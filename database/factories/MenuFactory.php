<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MenuFactory extends Factory
{
    public function definition(): array
    {
        return [
            'location' => $this->faker->unique()->randomElement(['header', 'footer']),
            'name' => ucfirst($this->faker->word()).' Menu',
        ];
    }
}