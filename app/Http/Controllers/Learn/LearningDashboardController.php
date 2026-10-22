<?php

declare(strict_types=1);

namespace App\Http\Controllers\Learn;

use App\Enums\EnrollmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\LessonReleaseService;
use App\Services\ProgressService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class LearningDashboardController extends Controller
{
    public function __construct(
        private readonly ProgressService $progress,
        private readonly LessonReleaseService $releases,
    ) {}

    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $enrollments = $user->enrollments()
            ->with(['course.sections'])
            ->whereIn('status', [EnrollmentStatus::Active, EnrollmentStatus::Completed])
            ->latest('updated_at')
            ->get()
            ->map(function ($enrollment) use ($user) {
                $course = $enrollment->course;
                $courseProgress = $this->progress->getOrCreateCourseProgress($user, $course);
                $continueLesson = $courseProgress->isCompleted()
                    ? null
                    : $this->progress->continueLesson($user, $course, $this->releases);

                return [
                    'enrollment' => $enrollment,
                    'course' => $course,
                    'progress' => $courseProgress,
                    'continue_lesson' => $continueLesson,
                    'is_expired' => $enrollment->expires_at !== null && $enrollment->expires_at->lt(now()),
                ];
            });

        // Courses the user can access via subscription but has no enrollment row for.
        $subscribedCourses = Course::query()
            ->published()
            ->whereHas('subscriptionPlans.subscriptions', function ($q) use ($user) {
                $q->where('user_id', $user->id)->where('status', 'active')
                  ->where(fn ($qq) => $qq->whereNull('expires_at')->orWhere('expires_at', '>', now()));
            })
            ->whereDoesntHave('enrollments', fn ($q) => $q->where('user_id', $user->id))
            ->get()
            ->map(fn (Course $course) => [
                'enrollment' => null,
                'course' => $course,
                'progress' => $this->progress->getOrCreateCourseProgress($user, $course),
                'continue_lesson' => $this->progress->continueLesson($user, $course, $this->releases),
                'is_expired' => false,
            ]);

        $items = $enrollments->concat($subscribedCourses)->values();

        $continueItem = $items->first(function (array $item): bool {
            return ($item['continue_lesson'] ?? null) !== null && ! ($item['is_expired'] ?? false);
        });

        return view('learn.dashboard', [
            'enrollments' => $items->map(function (array $item): array {
                $item['access_expired'] = (bool) ($item['is_expired'] ?? false);

                return $item;
            }),
            'continueEnrollment' => $continueItem
                ? (object) ['course' => $continueItem['course']]
                : null,
            'continueLesson' => $continueItem['continue_lesson'] ?? null,
        ]);
    }
}