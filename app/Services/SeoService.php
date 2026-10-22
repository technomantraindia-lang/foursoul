<?php

namespace App\Services;

use App\Models\Page;
use Illuminate\Support\Facades\URL;

class SeoService
{
    /**
     * Build a normalized SEO payload for a page or arbitrary entity.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public function forPage(Page $page, array $overrides = []): array
    {
        return $this->build(
            title: $overrides['title'] ?? $page->seo_title ?? $page->title,
            description: $overrides['description'] ?? $page->seo_description,
            keywords: $overrides['keywords'] ?? data_get($page->metadata, 'seo.keywords'),
            canonical: $overrides['canonical'] ?? $page->canonical_url ?? route('pages.show', $page->slug),
            ogImage: $overrides['ogImage'] ?? $page->og_image_path,
            type: 'website',
        );
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public function build(
        ?string $title = null,
        ?string $description = null,
        ?string $keywords = null,
        ?string $canonical = null,
        ?string $ogImage = null,
        string $type = 'website',
        array $overrides = []
    ): array {
        return array_merge([
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            'canonical' => $canonical ?: URL::current(),
            'og' => [
                'title' => $title,
                'description' => $description,
                'image' => $ogImage,
                'url' => $canonical ?: URL::current(),
                'type' => $type,
            ],
            'twitter' => [
                'card' => $ogImage ? 'summary_large_image' : 'summary',
                'title' => $title,
                'description' => $description,
                'image' => $ogImage,
            ],
            'schema' => null,
        ], $overrides);
    }

    /**
     * JSON-LD schema foundation (e.g. Organization / WebSite / Article).
     *
     * @param  array<string, mixed>  $data
     */
    public function schema(string $type, array $data): array
    {
        return array_merge([
            '@context' => 'https://schema.org',
            '@type' => $type,
        ], $data);
    }
}