<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\Banner;
use App\Models\FaqCategory;
use App\Models\Menu;
use App\Models\Page;
use App\Models\Testimonial;

class CmsService
{
    public function __construct(
        protected SettingsService $settings,
        protected SeoService $seo,
    ) {
    }

    /**
     * Published page by slug, or null.
     */
    public function publishedPage(string $slug): ?Page
    {
        return Page::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->where('published_at', '<=', now())
            ->first();
    }

    /**
     * Header menu with nested items (2 levels).
     */
    public function headerMenu(): ?Menu
    {
        return Menu::query()
            ->where('location', 'header')
            ->with(['items' => fn ($q) => $q->whereNull('parent_id')->orderBy('sort_order')->with('children')])
            ->first();
    }

    /**
     * Footer menu with nested items (2 levels).
     */
    public function footerMenu(): ?Menu
    {
        return Menu::query()
            ->where('location', 'footer')
            ->with(['items' => fn ($q) => $q->whereNull('parent_id')->orderBy('sort_order')->with('children')])
            ->first();
    }

    /**
     * Banners of a given placement that are currently scheduled.
     *
     * @return \Illuminate\Support\Collection<int, Banner>
     */
    public function activeBanners(string $placement)
    {
        return Banner::query()
            ->where('placement', $placement)
            ->active()
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * @return \Illuminate\Support\Collection<int, Testimonial>
     */
    public function publishedTestimonials(bool $featuredOnly = false, int $limit = 12)
    {
        return Testimonial::query()
            ->published()
            ->when($featuredOnly, fn ($q) => $q->where('is_featured', true))
            ->orderBy('sort_order')
            ->limit($limit)
            ->get();
    }

    /**
     * Visible FAQ categories with their visible items.
     *
     * @return \Illuminate\Support\Collection<int, FaqCategory>
     */
    public function visibleFaqCategories()
    {
        return FaqCategory::query()
            ->orderBy('sort_order')
            ->with(['items' => fn ($q) => $q->where('is_visible', true)->orderBy('sort_order')])
            ->get()
            ->filter(fn (FaqCategory $category) => $category->items->isNotEmpty())
            ->values();
    }

    /**
     * Global announcements currently in schedule.
     *
     * @return \Illuminate\Support\Collection<int, Announcement>
     */
    public function globalAnnouncements()
    {
        return Announcement::query()->global()->active()->get();
    }

    /**
     * Site settings grouped for layout rendering.
     *
     * @return array<string, mixed>
     */
    public function siteSettings(): array
    {
        return [
            'brand' => $this->settings->group('brand'),
            'social' => $this->settings->group('social'),
            'footer' => $this->settings->group('footer'),
            'contact' => $this->settings->group('contact'),
            'analytics' => $this->settings->group('analytics'),
        ];
    }
}