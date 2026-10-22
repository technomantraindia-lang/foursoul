<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\LessonType;
use App\Enums\LiveSessionStatus;
use App\Models\CourseLesson;
use App\Models\CourseSection;
use App\Models\LiveSession;
use Illuminate\Validation\ValidationException;

final class ReplayConversionService
{
    public function convertReplayToLesson(LiveSession $session, CourseSection $section, string $title, ?int $position = null): CourseLesson
    {
        if ($session->status !== LiveSessionStatus::Completed || ! $session->isReplayAvailable()) {
            throw ValidationException::withMessages(['session' => 'Only completed sessions with a replay can be converted.']);
        }
        if ((int) $section->course_id !== (int) $session->course_id) {
            throw ValidationException::withMessages(['section' => 'The destination section must belong to the session course.']);
        }

        $url = (string) ($session->recording_url ?? $session->replay_url);
        $existing = CourseLesson::query()->where('course_section_id', $section->id)->where('video_url', $url)->first();
        if ($existing) {
            return $existing;
        }

        return CourseLesson::create([
            'course_section_id' => $section->id,
            'title' => $title,
            'type' => LessonType::Video,
            'video_url' => $url,
            'position' => $position,
            'is_published' => false,
            'is_free_preview' => false,
        ]);
    }
}
