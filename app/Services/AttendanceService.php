<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Enrollment;
use App\Models\LiveSession;
use App\Models\SessionAttendance;
use App\Models\User;

class AttendanceService
{
    /**
     * Record a join. One attendance row per user per session —
     * re-joins update joined_at only on first entry of the session.
     */
    public function join(User $user, LiveSession $session): SessionAttendance
    {
        $attendance = SessionAttendance::firstOrCreate(
            [
                'live_session_id' => $session->id,
                'user_id' => $user->id,
            ],
            [
                'enrollment_id' => Enrollment::query()
                    ->where('user_id', $user->id)
                    ->where('course_id', $session->course_id)
                    ->latest('id')
                    ->value('id'),
                'joined_at' => now(),
            ],
        );

        return $attendance;
    }

    /**
     * Record a leave and compute duration + attendance percentage.
     */
    public function leave(User $user, LiveSession $session): ?SessionAttendance
    {
        $attendance = SessionAttendance::query()
            ->where('live_session_id', $session->id)
            ->where('user_id', $user->id)
            ->first();

        if (! $attendance || $attendance->left_at !== null) {
            return $attendance; // already left or never joined — no duplicate writes
        }

        $end = $session->actual_end_at ?? $session->scheduled_end_at ?? now();
        $start = $attendance->joined_at ?? now();

        $durationMinutes = max(0, (int) $start->diffInMinutes(now()));
        $sessionMinutes = max(1, (int) $start->diffInMinutes($end));

        $attendance->update([
            'left_at' => now(),
            'duration_minutes' => $durationMinutes,
            'attendance_percentage' => round(min(100, ($durationMinutes / $sessionMinutes) * 100), 2),
            'present' => true,
        ]);

        return $attendance;
    }

    /**
     * Explicit present marking (e.g. manual attendance by instructor).
     */
    public function markPresent(User $user, LiveSession $session, bool $present = true): SessionAttendance
    {
        $attendance = $this->join($user, $session);

        $attendance->update(['present' => $present]);

        return $attendance;
    }
}