<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\LessonType;
use App\Models\CourseLesson;
use App\Models\CourseSection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CourseLesson>
 */
class CourseLessonFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'course_section_id' => CourseSection::factory(),
            'title' => fake()->sentence(4),
            'type' => LessonType::Video,
            'content' => fake()->paragraph(),
            'video_url' => fake()->optional()->url(),
            'duration_minutes' => fake()->numberBetween(5, 60),
            'position' => 0,
            'is_free_preview' => false,
        ];
    }

    public function text(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => LessonType::Text,
            'video_url' => null,
        ]);
    }

    public function quiz(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => LessonType::Quiz,
            'video_url' => null,
        ]);
    }

    public function freePreview(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_free_preview' => true,
        ]);
    }

    /**
     * Attach to an existing section without creating a new one.
     */
    public function forSection(CourseSection $section): static
    {
        return $this->state(fn (array $attributes): array => [
            'course_section_id' => $section->getKey(),
        ]);
    }
}