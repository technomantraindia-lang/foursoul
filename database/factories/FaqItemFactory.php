<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\FaqCategory;

class FaqItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'faq_category_id' => FaqCategory::factory(),
            'question' => $this->faker->sentence().'?',
            'answer' => $this->faker->paragraph(),
            'is_visible' => true,
            'sort_order' => 0,
        ];
    }
}