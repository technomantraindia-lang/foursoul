<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CourseStatus;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\User;

/**
 * Central authority for lesson publication / release / unlock state.
 *
 * Locked reasons are user-friendly and consumed by controllers/views:
 * subscription_required, access_expired, previous_lesson_required,
 * scheduled_release, lesson_unpublished, course_unpublished, not_enrolled.
 */
class LessonReleaseService
{
    public function __construct(
        private readonly SubscriptionService $subscriptions,
    ) {}

    public function isLessonPublished(CourseLesson $lesson): bool
    {
        return (bool) $lesson->is_published;
    }

    public function isLessonReleased(CourseLesson $lesson): bool
    {
        return match ($lesson->release_type) {
            'scheduled' => $lesson->release_at === null || $lesson->release_at->lte(now()),
            default => true,
        };
    }

    /**
     * Sequential unlocking: when requires_previous_completion is set,
     * every required published lesson ordered before this one must be completed.
     */
    public function isSequentiallyUnlocked(User $user, CourseLesson $lesson): bool
    {
        if (! $lesson->requires_previous_completion) {
            return true;
        }

        $previousIds = CourseLesson::query()
            ->where('course_section_id', $lesson->course_section_id)
            ->where('position', '<', $lesson->position)
            ->where('is_published', true)
            ->pluck('id');

        if ($previousIds->isEmpty()) {
            return true;
        }

        $completedCount = $user->lessonProgress()
            ->whereIn('lesson_id', $previousIds)
            ->where('status', 'completed')
            ->count();

        return $completedCount >= $previousIds->count();
    }

    /**
     * Full access check for an authenticated user on a paid lesson.
     * Returns [bool $allowed, ?string $lockedReason].
     */
    public function canUserAccessLesson(User $user, Course $course, CourseLesson $lesson): array
    {
        if ($course->status !== CourseStatus::Published) {
            return [false, 'course_unpublished'];
        }

        if (! $this->isLessonPublished($lesson)) {
            return [false, 'lesson_unpublished'];
        }

        if (! $this->isLessonReleased($lesson)) {
            return [false, 'scheduled_release'];
        }

        // Free preview lessons bypass enrollment/subscription checks.
        if ($lesson->is_free_preview) {
            return [$this->isSequentiallyUnlocked($user, $lesson), null];
        }

        if (! $this->subscriptions->userHasCourseAccess($user, $course)) {
            return [false, 'not_enrolled'];
        }

        if (! $this->isSequentiallyUnlocked($user, $lesson)) {
            return [false, 'previous_lesson_required'];
        }

        return [true, null];
    }

    /**
     * Course-level access (published + user has valid subscription/entitlement/enrollment).
     */
    public function canUserAccessCourse(User $user, Course $course): bool
    {
        if ($course->status !== CourseStatus::Published) {
            return false;
        }

        return $this->subscriptions->userHasCourseAccess($user, $course);
    }

    public function canPreviewLesson(Course $course, CourseLesson $lesson): bool
    {
        return $course->status === CourseStatus::Published
            && $this->isLessonPublished($lesson)
            && $lesson->is_free_preview;
    }

    /**
     * Build a locked-state descriptor for a lesson in a curriculum listing.
     */
    public function getLockedReason(User $user, Course $course, CourseLesson $lesson): ?string
    {
        [, $reason] = $this->canUserAccessLesson($user, $course, $lesson);

        return $reason;
    }
}