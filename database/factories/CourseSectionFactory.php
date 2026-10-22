<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Course;
use App\Models\CourseSection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CourseSection>
 */
class CourseSectionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'course_id' => Course::factory(),
            'title' => 'Module '.fake()->unique()->numberBetween(1, 20).': '.fake()->words(2, true),
            'description' => fake()->sentence(),
            'position' => 0,
        ];
    }

    /**
     * Attach to an existing course without creating a new one.
     */
    public function forCourse(Course $course): static
    {
        return $this->state(fn (array $attributes): array => [
            'course_id' => $course->getKey(),
        ]);
    }
}