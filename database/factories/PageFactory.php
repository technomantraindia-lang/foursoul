<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PageStatus;
use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'uuid' => Str::uuid(),
            'title' => $title,
            'slug' => Str::slug($title),
            'seo_title' => null,
            'seo_description' => null,
            'og_image_path' => null,
            'canonical_url' => null,
            'layout' => 'default',
            'visibility' => 'public',
            'status' => PageStatus::Published,
            'published_at' => now()->subDay(),
            'content' => ['sections' => []],
            'version' => 1,
            'metadata' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => PageStatus::Draft,
            'published_at' => null,
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn () => [
            'status' => PageStatus::Scheduled,
        ]);
    }
}