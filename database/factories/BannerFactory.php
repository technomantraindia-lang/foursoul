<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BannerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement(['homepage', 'popup', 'announcement_bar', 'campaign']),
            'title' => $this->faker->sentence(4),
            'content' => $this->faker->paragraph(),
            'image_path' => null,
            'button_label' => null,
            'button_url' => null,
            'status' => 'published',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(30),
            'sort_order' => 0,
            'metadata' => null,
        ];
    }
}