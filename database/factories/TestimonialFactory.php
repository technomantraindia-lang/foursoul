<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TestimonialFactory extends Factory
{
    public function definition(): array
    {
        return [
            'author_name' => $this->faker->name(),
            'author_title' => $this->faker->jobTitle(),
            'author_avatar_path' => null,
            'quote' => $this->faker->paragraph(),
            'rating' => $this->faker->numberBetween(4, 5),
            'is_featured' => false,
            'status' => 'published',
            'sort_order' => 0,
        ];
    }

    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true]);
    }
}