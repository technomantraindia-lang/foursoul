<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CourseProgressStatus;
use App\Enums\LessonProgressStatus;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseProgress;
use App\Models\Enrollment;
use App\Models\LessonProgress;
use App\Models\User;

/**
 * Centralized lesson + course progress business rules.
 *
 * - Never trusts request data for ownership; callers pass the authenticated user.
 * - Completion is never reset by subscription expiry.
 * - Progress rows are unique per user/lesson and user/course (enrollment-agnostic
 *   so renewal resumes existing progress).
 */
class ProgressService
{
    /** Throttle window for watch-position writes (seconds). */
    private const POSITION_WRITE_THROTTLE = 10;

    /* -----------------------------------------------------------------
     | Lesson progress
     * ----------------------------------------------------------------- */

    public function getOrCreateLessonProgress(User $user, Course $course, CourseLesson $lesson, ?int $enrollmentId = null): LessonProgress
    {
        return LessonProgress::firstOrCreate(
            [
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
            ],
            [
                'course_id' => $course->id,
                'enrollment_id' => $enrollmentId,
                'status' => LessonProgressStatus::NotStarted,
            ]
        );
    }

    /**
     * Update the watch position for a video lesson.
     *
     * @param array{position?:int, duration?:int, watched?:int} $data
     */
    public function updateWatchPosition(User $user, Course $course, CourseLesson $lesson, array $data): LessonProgress
    {
        $progress = $this->getOrCreateLessonProgress($user, $course, $lesson);

        $duration = isset($data['duration']) ? max(0, (int) $data['duration']) : ($progress->duration_seconds ?? ($lesson->duration_seconds ?? null));
        $position = isset($data['position']) ? max(0, (int) $data['position']) : $progress->last_position_seconds;
        $watched = isset($data['watched']) ? max(0, (int) $data['watched']) : 0;

        // Ignore impossible positions beyond a sane duration ceiling.
        if ($duration !== null && $duration > 0 && $position > $duration) {
            $position = $duration;
        }

        // Throttle DB writes: skip if last write was recent and position barely moved.
        if ($progress->last_accessed_at !== null
            && $progress->last_accessed_at->gt(now()->subSeconds(self::POSITION_WRITE_THROTTLE))
            && abs($position - $progress->last_position_seconds) < 5
        ) {
            return $progress;
        }

        $percentage = ($duration && $duration > 0)
            ? min(100.0, round(($position / $duration) * 100, 2))
            : 0.0;

        $progress->fill([
            'last_position_seconds' => $position,
            'duration_seconds' => $duration,
            'watch_seconds' => min($progress->watch_seconds + $watched, $duration ?? PHP_INT_MAX),
            'progress_percentage' => $percentage,
            'last_accessed_at' => now(),
        ]);

        if ($progress->status === LessonProgressStatus::NotStarted) {
            $progress->status = LessonProgressStatus::InProgress;
            $progress->started_at ??= now();
        }

        $progress->save();

        $this->touchCourseProgress($user, $course);

        // Auto-complete when requirement is met via watching.
        if ($lesson->completion_requirement === 'watch_percentage'
            && $percentage >= (float) $lesson->completion_threshold
        ) {
            $this->markLessonComplete($user, $course, $lesson, source: 'auto_watch');
        }

        return $progress->refresh();
    }

    public function markLessonComplete(User $user, Course $course, CourseLesson $lesson, string $source = 'manual'): LessonProgress
    {
        $progress = $this->getOrCreateLessonProgress($user, $course, $lesson);

        if ($progress->isCompleted()) {
            return $progress; // Completion is idempotent and never reset.
        }

        $progress->fill([
            'status' => LessonProgressStatus::Completed,
            'completed_at' => now(),
            'completion_source' => $source,
            'last_accessed_at' => now(),
        ]);

        if ($lesson->duration_seconds) {
            $progress->progress_percentage = 100.0;
        }

        $progress->save();

        $this->recalculateCourseProgress($user, $course);

        return $progress;
    }

    /* -----------------------------------------------------------------
     | Course progress
     * ----------------------------------------------------------------- */

    public function getOrCreateCourseProgress(User $user, Course $course): CourseProgress
    {
        return CourseProgress::firstOrCreate(
            [
                'user_id' => $user->id,
                'course_id' => $course->id,
            ],
            ['status' => CourseProgressStatus::NotStarted]
        );
    }

    public function recalculateCourseProgress(User $user, Course $course): CourseProgress
    {
        $progress = $this->getOrCreateCourseProgress($user, $course);

        $totalLessons = CourseLesson::query()
            ->where('is_published', true)
            ->whereIn('course_section_id', $course->sections()->pluck('id'))
            ->count();

        $completedLessons = $user->lessonProgress()
            ->whereIn('lesson_id', CourseLesson::query()
                ->where('is_published', true)
                ->whereIn('course_section_id', $course->sections()->pluck('id'))
                ->select('id'))
            ->where('status', 'completed')
            ->count();

        $percentage = $totalLessons > 0
            ? round(min(100, ($completedLessons / $totalLessons) * 100), 2)
            : 0.0;

        $wasCompleted = $progress->isCompleted();

        $progress->fill([
            'total_lessons' => $totalLessons,
            'completed_lessons' => $completedLessons,
            'progress_percentage' => $percentage,
            'last_accessed_at' => now(),
        ]);

        if (! $wasCompleted && $totalLessons > 0 && $completedLessons >= $totalLessons) {
            $progress->status = CourseProgressStatus::Completed;
            $progress->completed_at ??= now();
        } elseif ($progress->status === CourseProgressStatus::NotStarted && $completedLessons > 0) {
            $progress->status = CourseProgressStatus::InProgress;
        } elseif ($progress->status === CourseProgressStatus::NotStarted && $completedLessons === 0 && $progress->last_accessed_at !== null) {
            $progress->status = CourseProgressStatus::InProgress;
        }

        $progress->save();

        return $progress;
    }

    protected function touchCourseProgress(User $user, Course $course): void
    {
        $progress = $this->getOrCreateCourseProgress($user, $course);
        $progress->last_accessed_at = now();
        if ($progress->status === CourseProgressStatus::NotStarted) {
            $progress->status = CourseProgressStatus::InProgress;
            $progress->started_at ??= now();
        }
        $progress->save();
    }

    public function touchLastAccessed(LessonProgress $progress): LessonProgress
    {
        $progress->last_accessed_at = now();
        if ($progress->status === LessonProgressStatus::NotStarted) {
            $progress->status = LessonProgressStatus::InProgress;
            $progress->started_at ??= now();
        }
        $progress->save();

        return $progress;
    }

    /* -----------------------------------------------------------------
     | Navigation helpers
     * ----------------------------------------------------------------- */

    /**
     * Ordered flat list of published lessons for a course.
     */
    public function orderedLessons(Course $course)
    {
        return CourseLesson::query()
            ->whereIn('course_section_id', $course->sections()->orderBy('position')->pluck('id'))
            ->where('is_published', true)
            ->orderBy('position')
            ->with('section')
            ->get();
    }

    public function nextLesson(Course $course, CourseLesson $current)
    {
        return $this->orderedLessons($course)
            ->filter(fn ($l) => $l->position > $current->position || ($l->position === $current->position && $l->section->position > $current->section->position))
            ->sortBy(fn ($l) => [$l->section->position, $l->position])
            ->first();
    }

    public function previousLesson(Course $course, CourseLesson $current)
    {
        return $this->orderedLessons($course)
            ->filter(fn ($l) => $l->position < $current->position || ($l->position === $current->position && $l->section->position < $current->section->position))
            ->sortByDesc(fn ($l) => [$l->section->position, $l->position])
            ->first();
    }

    /**
     * The lesson to resume: first incomplete unlocked lesson, else first lesson.
     */
    public function continueLesson(User $user, Course $course, LessonReleaseService $releases)
    {
        foreach ($this->orderedLessons($course) as $lesson) {
            [$allowed] = $releases->canUserAccessLesson($user, $course, $lesson);
            if (! $allowed) {
                continue;
            }
            $done = $user->lessonProgress()
                ->where('lesson_id', $lesson->id)
                ->where('status', 'completed')
                ->exists();
            if (! $done) {
                return $lesson;
            }
        }

        return $this->orderedLessons($course)->first();
    }

    /**
     * @return array{0:?CourseLesson,1:?CourseLesson}
     */
    public function getNeighbourLessons(User $user, Course $course, CourseLesson $lesson): array
    {
        return [$this->previousLesson($course, $lesson), $this->nextLesson($course, $lesson)];
    }

    public function determineContinueLesson(User $user, Course $course): ?CourseLesson
    {
        return $this->continueLesson($user, $course, app(LessonReleaseService::class));
    }

    public function determineNextLessonUrl(User $user, Course $course, CourseLesson $current): string
    {
        $next = $this->nextLesson($course, $current);
        if (! $next) {
            return route('learn.course', $course);
        }
        [$allowed] = app(LessonReleaseService::class)->canUserAccessLesson($user, $course, $next);
        return $allowed ? route('learn.lesson', [$course, $next]) : route('learn.course', $course);
    }
}
