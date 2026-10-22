<?php

declare(strict_types=1);

namespace Tests\Feature\Cms;

use App\Models\Admin;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Page;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CmsEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_page_is_publicly_rendered(): void
    {
        $page = Page::factory()->create([
            'slug' => 'about-us',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get('/about-us');

        $response->assertOk();
        $response->assertSee($page->title);
    }

    public function test_draft_page_is_not_found(): void
    {
        Page::factory()->create(['slug' => 'draft-page', 'status' => 'draft']);

        $this->get('/draft-page')->assertNotFound();
    }

    public function test_scheduled_page_is_hidden_until_publish_time(): void
    {
        Page::factory()->create([
            'slug' => 'future-page',
            'status' => 'scheduled',
            'published_at' => now()->addWeek(),
        ]);

        $this->get('/future-page')->assertNotFound();
    }

    public function test_blog_index_lists_only_published_posts(): void
    {
        $category = BlogCategory::factory()->create();

        $published = BlogPost::factory()->published()->create([
            'blog_category_id' => $category->id,
            'title' => 'Visible post',
        ]);
        BlogPost::factory()->draft()->create(['title' => 'Hidden post']);

        $response = $this->get('/blog');

        $response->assertOk();
        $response->assertSee('Visible post');
        $response->assertDontSee('Hidden post');
    }

    public function test_blog_post_show_renders_content_and_related_posts(): void
    {
        $post = BlogPost::factory()->published()->create([
            'title' => 'Main post',
            'content' => '<p>Hello world</p>',
        ]);
        BlogPost::factory()->count(2)->published()->create();

        $response = $this->get('/blog/'.$post->slug);

        $response->assertOk();
        $response->assertSee('Main post');
        $response->assertSee('Hello world', false);
    }

    public function test_draft_blog_post_is_not_found(): void
    {
        $post = BlogPost::factory()->draft()->create();

        $this->get('/blog/'.$post->slug)->assertNotFound();
    }

    public function test_admin_can_manage_pages_via_policy(): void
    {
        $admin = Admin::factory()->active()->create();
        $page = Page::factory()->create();

        $this->assertTrue($admin->can('view', $page));
        $this->assertTrue($admin->can('update', $page));
        $this->assertTrue($admin->can('create', Page::class));
    }
}