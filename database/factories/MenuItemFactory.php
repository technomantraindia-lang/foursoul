<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Menu;

class MenuItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'menu_id' => Menu::factory(),
            'parent_id' => null,
            'label' => $this->faker->words(2, true),
            'url' => '/'.$this->faker->slug(),
            'link_type' => 'custom',
            'opens_in_new_tab' => false,
            'is_visible' => true,
            'permission' => null,
            'sort_order' => 0,
        ];
    }
}