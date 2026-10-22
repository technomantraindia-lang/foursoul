<?php

declare(strict_types=1);

namespace App\Http\Controllers\Learn;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Services\LessonReleaseService;
use App\Services\ProgressService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class LessonPlayerController extends Controller
{
    public function __construct(
        private readonly ProgressService $progress,
        private readonly LessonReleaseService $releases,
    ) {}

    public function show(Request $request, Course $course, CourseLesson $lesson): View
    {
        $user = $request->user();

        // Lesson must belong to the course in the URL.
        abort_unless($lesson->section && (int) $lesson->section->course_id === (int) $course->id, 404);

        [$allowed, $reason] = $this->releases->canUserAccessLesson($user, $course, $lesson);

        if (! $allowed) {
            // Expired access -> friendly redirect to the curriculum page.
            if ($reason === 'access_expired') {
                throw new HttpException(302, route('learn.course', $course));
            }

            abort(403, __('You cannot access this lesson yet.'));
        }

        $lessonProgress = $this->progress->getOrCreateLessonProgress($user, $course, $lesson);
        $this->progress->touchLastAccessed($lessonProgress);

        [$previous, $next] = $this->progress->getNeighbourLessons($user, $course, $lesson);
        $continue = $this->progress->determineContinueLesson($user, $course);

        return view('learn.lesson-player', [
            'course' => $course,
            'lesson' => $lesson,
            'lessonProgress' => $lessonProgress,
            'previous' => $previous,
            'next' => $next,
            'continueLesson' => $continue,
        ]);
    }
}