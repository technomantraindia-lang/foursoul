<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MediaFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->word();

        return [
            'folder_id' => null,
            'uploaded_by_admin_id' => null,
            'disk' => 'public',
            'path' => 'media/'.$this->faker->uuid().'.jpg',
            'file_name' => $name.'.jpg',
            'mime_type' => 'image/jpeg',
            'size_bytes' => $this->faker->numberBetween(10_000, 5_000_000),
            'width' => 1200,
            'height' => 800,
            'title' => ucfirst($name),
            'alt_text' => $this->faker->sentence(6),
            'caption' => null,
            'tags' => null,
            'variants' => null,
            'usage' => null,
        ];
    }
}