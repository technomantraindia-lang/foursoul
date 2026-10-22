<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\AnnouncementType;

class AnnouncementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'body' => $this->faker->paragraph(),
            'type' => AnnouncementType::Global,
            'course_id' => null,
            'starts_at' => null,
            'ends_at' => null,
        ];
    }
}