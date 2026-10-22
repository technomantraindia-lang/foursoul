<?php

declare(strict_types=1);

namespace App\Http\Controllers\Learn;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Services\LessonReleaseService;
use App\Services\ProgressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LessonProgressController extends Controller
{
    public function __construct(
        private readonly ProgressService $progress,
        private readonly LessonReleaseService $releases,
    ) {}

    /**
     * Periodic watch-position updates from the player.
     */
    public function progress(Request $request, Course $course, CourseLesson $lesson): JsonResponse
    {
        $user = $request->user();

        abort_unless($lesson->section && (int) $lesson->section->course_id === (int) $course->id, 404);
        [$allowed] = $this->releases->canUserAccessLesson($user, $course, $lesson);
        abort_unless($allowed, 403);

        $data = $request->validate([
            'position_seconds' => ['required', 'integer', 'min:0'],
            'duration_seconds' => ['nullable', 'integer', 'min:0', 'max:86400'],
            'watched_delta_seconds' => ['nullable', 'integer', 'min:0', 'max:600'],
        ]);

        $lessonProgress = $this->progress->updateWatchPosition(
            $user,
            $course,
            $lesson,
            [
                'position' => (int) $data['position_seconds'],
                'duration' => isset($data['duration_seconds']) ? (int) $data['duration_seconds'] : null,
                'watched' => (int) ($data['watched_delta_seconds'] ?? 0),
            ],
        );

        return response()->json([
            'status' => $lessonProgress->status->value,
            'progress_percentage' => (float) $lessonProgress->progress_percentage,
            'last_position_seconds' => (int) $lessonProgress->last_position_seconds,
            'completed' => $lessonProgress->status === \App\Enums\LessonProgressStatus::Completed,
        ]);
    }

    /**
     * Manual "Mark Complete" (and auto-completion fallback).
     */
    public function complete(Request $request, Course $course, CourseLesson $lesson): JsonResponse
    {
        $user = $request->user();

        abort_unless($lesson->section && (int) $lesson->section->course_id === (int) $course->id, 404);
        [$allowed] = $this->releases->canUserAccessLesson($user, $course, $lesson);
        abort_unless($allowed, 403);

        $lessonProgress = $this->progress->markLessonComplete($user, $course, $lesson, 'manual');

        return response()->json([
            'status' => $lessonProgress->status->value,
            'completed_at' => optional($lessonProgress->completed_at)->toIso8601String(),
            'next_lesson_url' => $this->progress
                ->determineNextLessonUrl($user, $course, $lesson),
        ]);
    }
}
