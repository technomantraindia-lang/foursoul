<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CourseStatus;
use App\Models\Admin;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return [
            'uuid' => (string) Str::uuid(),
            'title' => $title,
            'description' => fake()->paragraph(),
            'status' => CourseStatus::Draft,
            'published_at' => null,
            'created_by' => Admin::factory(),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => CourseStatus::Published,
            'published_at' => now(),
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => CourseStatus::Archived,
            'published_at' => null,
        ]);
    }

    public function unauthored(): static
    {
        return $this->state(fn (array $attributes): array => [
            'created_by' => null,
        ]);
    }
}