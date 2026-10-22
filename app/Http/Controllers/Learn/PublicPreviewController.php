<?php

declare(strict_types=1);

namespace App\Http\Controllers\Learn;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Services\LessonReleaseService;
use Illuminate\Contracts\View\View;

/**
 * Public free-preview player — separate from the authenticated LMS player.
 * Only free_preview lessons of published courses are ever rendered here.
 */
class PublicPreviewController extends Controller
{
    public function __construct(private readonly LessonReleaseService $releases) {}

    public function show(Course $course, CourseLesson $lesson): View
    {
        abort_unless($this->releases->canPreviewLesson($course, $lesson), 404);

        return view('learn.preview', [
            'course' => $course,
            'lesson' => $lesson,
        ]);
    }
}