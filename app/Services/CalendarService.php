<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

/**
 * Timezone-aware calendar architecture for students and instructors.
 * Returns grouped day buckets so Blade views can render simple calendars.
 */
class CalendarService
{
    public function __construct(
        protected LiveSessionService $liveSessionService,
    ) {}

    /**
     * @return array<int, array{date: string, timezone: string, sessions: \Illuminate\Support\Collection}>
     */
    public function studentCalendar(User $user, string $timezone = 'UTC'): array
    {
        $sessions = $this->liveSessionService->upcomingForUser($user);

        return $this->groupByDay($sessions, $timezone);
    }

    /**
     * @return array<int, array{date: string, timezone: string, sessions: \Illuminate\Support\Collection}>
     */
    public function instructorCalendar(int $instructorId, string $timezone = 'UTC'): array
    {
        $sessions = $this->liveSessionService->upcomingForInstructor($instructorId);

        return $this->groupByDay($sessions, $timezone);
    }

    /**
     * @param iterable<\App\Models\LiveSession> $sessions
     * @return array<int, array{date: string, timezone: string, sessions: \Illuminate\Support\Collection}>
     */
    protected function groupByDay(iterable $sessions, string $timezone): array
    {
        $buckets = [];

        foreach ($sessions as $session) {
            $localStart = $session->scheduled_start_at->copy()->setTimezone($timezone);
            $key = $localStart->format('Y-m-d');

            $buckets[$key] ??= [
                'date' => $key,
                'timezone' => $timezone,
                'sessions' => collect(),
            ];

            $buckets[$key]['sessions']->push($session);
        }

        ksort($buckets);

        return array_values($buckets);
    }
}