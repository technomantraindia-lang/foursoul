<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PageStatus;
use App\Models\Admin;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BlogPostFactory extends Factory
{
    protected $model = BlogPost::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return [
            'uuid' => Str::uuid(),
            'blog_category_id' => BlogCategory::factory(),
            'author_admin_id' => Admin::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'excerpt' => fake()->paragraph(),
            'content' => implode("

", fake()->paragraphs(5)),
            'featured_image_path' => null,
            'status' => PageStatus::Published,
            'published_at' => now()->subDay(),
            'reading_time_minutes' => fake()->numberBetween(1, 12),
            'seo_title' => null,
            'seo_description' => null,
            'metadata' => null,
            'view_count' => 0,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => PageStatus::Published,
            'published_at' => now()->subDay(),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => PageStatus::Draft,
            'published_at' => null,
        ]);
    }
}