<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\LiveSessionStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LiveSession;
use App\Models\User;

class LiveSessionService
{
    /** Default minutes a learner may join before scheduled start. */
    public const JOIN_BEFORE_MINUTES = 15;

    /** Default grace period (minutes) after scheduled end to still join. */
    public const JOIN_GRACE_MINUTES = 30;

    public function __construct(
        protected SubscriptionService $subscriptionService,
    ) {}

    /* -----------------------------------------------------------------
     | Lifecycle
     * ----------------------------------------------------------------- */

    public function schedule(array $data): LiveSession
    {
        return LiveSession::create($data + [
            'status' => LiveSessionStatus::Scheduled->value,
        ]);
    }

    public function reschedule(LiveSession $session, $start, $end): LiveSession
    {
        $session->update([
            'scheduled_start_at' => $start,
            'scheduled_end_at' => $end,
            'status' => LiveSessionStatus::Scheduled,
        ]);

        return $session;
    }

    public function start(LiveSession $session): LiveSession
    {
        if ($session->status === LiveSessionStatus::Scheduled) {
            $session->update([
                'status' => LiveSessionStatus::Live,
                'actual_start_at' => now(),
            ]);
        }

        return $session;
    }

    public function end(LiveSession $session): LiveSession
    {
        if ($session->status === LiveSessionStatus::Live) {
            $session->update([
                'status' => LiveSessionStatus::Completed,
                'actual_end_at' => now(),
            ]);
        }

        return $session;
    }

    public function cancel(LiveSession $session): LiveSession
    {
        if (in_array($session->status, [LiveSessionStatus::Draft, LiveSessionStatus::Scheduled], true)) {
            $session->update(['status' => LiveSessionStatus::Cancelled]);
        }

        return $session;
    }

    /**
     * Mark sessions whose window has fully passed as expired.
     */
    public function expireStaleSessions(): int
    {
        return LiveSession::query()
            ->whereIn('status', [LiveSessionStatus::Scheduled, LiveSessionStatus::Live])
            ->where('scheduled_end_at', '<', now()->subMinutes(self::JOIN_GRACE_MINUTES))
            ->update(['status' => LiveSessionStatus::Expired->value]);
    }

    /* -----------------------------------------------------------------
     | Access
     * ----------------------------------------------------------------- */

    /**
     * Server-side join validation. Never trusts request identifiers.
     *
     * @return array{allowed: bool, reason: string|null, waiting_room: bool}
     */
    public function validateJoin(User $user, LiveSession $session): array
    {
        LiveSession::clearExpiredMeetingLinks();
        $session->refresh();

        if (! in_array($session->status, [LiveSessionStatus::Scheduled, LiveSessionStatus::Live], true)) {
            return ['allowed' => false, 'reason' => 'session_not_available', 'waiting_room' => false];
        }

        // Course access via subscription/entitlement/enrollment — source of truth.
        if (! $this->subscriptionService->userHasCourseAccess($user, $session->course)) {
            return ['allowed' => false, 'reason' => 'course_access_required', 'waiting_room' => false];
        }

        $enrollment = Enrollment::query()
            ->where('user_id', $user->id)
            ->where('course_id', $session->course_id)
            ->active()
            ->first();

        if (! $enrollment) {
            return ['allowed' => false, 'reason' => 'not_enrolled', 'waiting_room' => false];
        }

        if (! $session->isJoinableNow(self::JOIN_BEFORE_MINUTES, self::JOIN_GRACE_MINUTES)) {
            return ['allowed' => false, 'reason' => 'outside_join_window', 'waiting_room' => false];
        }

        if (
            $session->max_participants !== null
            && $session->attendances()->whereNotNull('joined_at')->count() >= $session->max_participants
        ) {
            return ['allowed' => false, 'reason' => 'session_full', 'waiting_room' => false];
        }

        return [
            'allowed' => true,
            'reason' => null,
            'waiting_room' => $session->waiting_room_enabled,
        ];
    }

    /**
     * Join state for the student UI.
     *
     * @return array<string, mixed>
     */
    public function joinState(User $user, LiveSession $session): array
    {
        $validation = $this->validateJoin($user, $session);

        return [
            'can_join' => $validation['allowed'],
            'locked_reason' => $validation['reason'],
            'waiting_room' => $validation['waiting_room'],
            'meeting_url' => $validation['allowed'] ? $session->meeting_url : null,
            'starts_at' => $session->scheduled_start_at?->toIso8601String(),
            'ends_at' => $session->scheduled_end_at?->toIso8601String(),
            'timezone' => $session->timezone,
            'youtube_embed_url' => $validation['allowed'] ? $this->youtubeEmbedUrl($session->meeting_url) : null,
        ];
    }

    public function youtubeEmbedUrl(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        $parts = parse_url($url);
        $host = strtolower((string) ($parts['host'] ?? ''));
        $path = trim((string) ($parts['path'] ?? ''), '/');
        parse_str((string) ($parts['query'] ?? ''), $query);
        $id = $query['v'] ?? null;

        if ($host === 'youtu.be') {
            $id = explode('/', $path)[0] ?? null;
        } elseif (str_contains($path, 'live/')) {
            $id = explode('live/', $path)[1] ?? null;
        }

        return $id && preg_match('/^[A-Za-z0-9_-]{6,20}$/', (string) $id)
            ? 'https://www.youtube.com/embed/'.rawurlencode((string) $id)
            : null;
    }

    /* -----------------------------------------------------------------
     | Replay
     * ----------------------------------------------------------------- */

    /**
     * Replay URL is only revealed when every gate passes.
     * Never expose replay/recording URLs publicly.
     */
    public function replayFor(User $user, LiveSession $session): ?string
    {
        if (! $session->isReplayAvailable()) {
            return null;
        }

        if (! $this->subscriptionService->userHasCourseAccess($user, $session->course)) {
            return null;
        }

        return $session->recording_url ?? $session->replay_url;
    }

    /* -----------------------------------------------------------------
     | Calendar queries
     * ----------------------------------------------------------------- */

    /**
     * Student calendar: sessions for courses the user is enrolled in.
     */
    public function upcomingForUser(User $user, int $limit = 20)
    {
        return LiveSession::query()
            ->visibleToLearners()
            ->where(function ($query): void {
                $query->where(function ($scheduled): void {
                    $scheduled->where('status', LiveSessionStatus::Scheduled->value)
                        ->where('scheduled_start_at', '>=', now());
                })->orWhere('status', LiveSessionStatus::Live->value);
            })
            ->whereIn('course_id', $user->enrollments()->active()->pluck('course_id'))
            ->with(['course:id,title,slug', 'instructor:id,name'])
            ->orderBy('scheduled_start_at')
            ->limit($limit)
            ->get();
    }

    public function pastForUser(User $user, int $limit = 20)
    {
        return LiveSession::query()
            ->visibleToLearners()
            ->past()
            ->whereIn('course_id', $user->enrollments()->pluck('course_id'))
            ->with(['course:id,title,slug'])
            ->orderByDesc('scheduled_start_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Instructor calendar: sessions the admin/instructor hosts.
     */
    public function upcomingForInstructor(int $instructorId, int $limit = 50)
    {
        return LiveSession::query()
            ->upcoming()
            ->where('instructor_id', $instructorId)
            ->with(['course:id,title,slug'])
            ->orderBy('scheduled_start_at')
            ->limit($limit)
            ->get();
    }
}
