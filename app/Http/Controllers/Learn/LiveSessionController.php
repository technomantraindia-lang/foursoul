<?php

declare(strict_types=1);

namespace App\Http\Controllers\Learn;

use App\Http\Controllers\Controller;
use App\Models\LiveSession;
use App\Services\AttendanceService;
use App\Services\CalendarService;
use App\Services\LiveSessionService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LiveSessionController extends Controller
{
    public function __construct(
        protected LiveSessionService $liveSessionService,
        protected AttendanceService $attendanceService,
        protected CalendarService $calendarService,
    ) {}

    /**
     * Student calendar — upcoming sessions grouped by day.
     */
    public function index(Request $request): View
    {
        $timezone = $this->timezoneFrom($request);

        return view('learn.live-sessions', [
            'days' => $this->calendarService->studentCalendar($request->user(), $timezone),
            'pastSessions' => $this->liveSessionService->pastForUser($request->user()),
            'timezone' => $timezone,
        ]);
    }

    /**
     * Session detail + join state + replay availability.
     */
    public function show(Request $request, LiveSession $session): View
    {
        abort_unless(in_array($session->status->value, ['scheduled', 'live', 'completed'], true), 404);

        $user = $request->user();
        $joinState = $this->liveSessionService->joinState($user, $session);

        return view('learn.live-session', [
            'session' => $session->load(['course', 'instructor']),
            'joinState' => $joinState,
            'replayUrl' => $this->liveSessionService->replayFor($user, $session),
            'attendance' => $session->attendances()->where('user_id', $user->id)->first(),
            'timezone' => $this->timezoneFrom($request),
            'youtubeEmbedUrl' => $joinState['youtube_embed_url'] ?? null,
        ]);
    }

    /**
     * Record a join event (server-validated).
     */
    public function join(Request $request, LiveSession $session)
    {
        $validation = $this->liveSessionService->validateJoin($request->user(), $session);

        if (! $validation['allowed']) {
            return back()->with('flash.error', match ($validation['reason']) {
                'course_access_required' => 'You need valid course access to join this session.',
                'not_enrolled' => 'You are not enrolled in this course.',
                'outside_join_window' => 'This session is not open for joining yet.',
                'session_full' => 'This session has reached its participant limit.',
                default => 'This session is not available.',
            });
        }

        $attendance = $this->attendanceService->join($request->user(), $session);

        if ($validation['waiting_room']) {
            return redirect()
                ->route('learn.live-sessions.show', $session)
                ->with('flash.info', 'You are in the waiting room. The host will admit you when the session starts.');
        }

        return redirect()->away($session->meeting_url);
    }

    /**
     * Record a leave event for the current user's attendance row.
     */
    public function leave(Request $request, LiveSession $session)
    {
        $this->attendanceService->leave($request->user(), $session);

        return redirect()
            ->route('learn.live-sessions.show', $session)
            ->with('flash.success', 'Your attendance has been recorded.');
    }

    protected function timezoneFrom(Request $request): string
    {
        $tz = (string) $request->query('tz', $request->user()->timezone ?? 'UTC');

        return in_array($tz, \DateTimeZone::listIdentifiers(), true) ? $tz : 'UTC';
    }
}
