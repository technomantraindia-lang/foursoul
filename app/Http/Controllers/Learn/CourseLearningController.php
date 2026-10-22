<?php

declare(strict_types=1);

namespace App\Http\Controllers\Learn;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\LessonReleaseService;
use App\Services\ProgressService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CourseLearningController extends Controller
{
    public function __construct(
        private readonly ProgressService $progress,
        private readonly LessonReleaseService $releases,
    ) {}

    public function show(Request $request, Course $course): View
    {
        $user = $request->user();

        $hasAccess = $this->releases->canUserAccessCourse($user, $course);
        $courseProgress = $this->progress->getOrCreateCourseProgress($user, $course);

        // Build curriculum with per-lesson access state.
        $sections = $course->sections()
            ->with(['lessons' => fn ($q) => $q->orderBy('position')])
            ->orderBy('position')
            ->get()
            ->map(function ($section) use ($user, $course) {
                $lessons = $section->lessons->map(function ($lesson) use ($user, $course) {
                    [$allowed, $reason] = $this->releases->canUserAccessLesson($user, $course, $lesson);
                    $lp = $user->lessonProgress()->where('lesson_id', $lesson->id)->first();

                    return [
                        'lesson' => $lesson,
                        'allowed' => $allowed,
                        'locked_reason' => $reason,
                        'status' => $lp?->status?->value ?? 'not_started',
                        'percentage' => (float) ($lp?->progress_percentage ?? 0),
                    ];
                });

                return ['section' => $section, 'lessons' => $lessons];
            });

        // Assessments belong to the course experience as well as the admin
        // resource. Only published items are visible to enrolled learners.
        $assignments = $course->assignments()
            ->where('status', 'published')
            ->with('lesson')
            ->orderBy('due_at')
            ->get();

        $quizzes = $course->quizzes()
            ->where('status', 'published')
            ->withCount('questions')
            ->orderBy('published_at')
            ->get();

        return view('learn.course', [
            'course' => $course,
            'hasAccess' => $hasAccess,
            'courseProgress' => $courseProgress,
            'sections' => $sections,
            'assignments' => $assignments,
            'quizzes' => $quizzes,
        ]);
    }
}
