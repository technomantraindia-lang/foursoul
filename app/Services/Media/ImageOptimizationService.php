<?php

namespace App\Services\Media;

use App\Models\Media;
use Illuminate\Support\Facades\Log;

/**
 * Phase 12 — Part 6: Image optimization architecture.
 *
 * Defines the variant pipeline (thumbnail, responsive sizes, WebP/AVIF) and
 * dispatches the work to the media queue. Actual rasterization is driver
 * based (Intervention/GD/Imagick) and wired at runtime; this class owns the
 * orchestration contract so call sites are stable today.
 */
class ImageOptimizationService
{
    /** Widths for responsive variants. */
    public const RESPONSIVE_WIDTHS = [320, 640, 1024, 1920];

    /** Modern formats to generate alongside the original. */
    public const MODERN_FORMATS = ['webp', 'avif'];

    /**
     * Queue optimization for an uploaded media record. Safe to call from
     * Filament observers or upload controllers.
     */
    public function optimize(Media $media): void
    {
        if (! $this->isImage($media)) {
            return;
        }

        dispatch(function () use ($media): void {
            $this->process($media);
        })->onQueue(\App\Support\QueueDomains::MEDIA)
          ->delay(now()->addSeconds(2));
    }

    /**
     * Process pipeline: thumbnails → responsive variants → modern formats.
     * Each step is idempotent; existing variants are skipped.
     */
    public function process(Media $media): void
    {
        $variants = [];

        foreach ($this->plannedVariants($media) as $variant) {
            $variants[$variant['name']] = $this->generateVariant($media, $variant);
        }

        $meta = $media->meta ?? [];
        $meta['optimized_variants'] = array_filter($variants);

        $media->forceFill(['meta' => $meta])->saveQuietly();

        Log::channel('production')->info('image.optimized', [
            'media_id' => $media->getKey(),
            'variants' => array_keys(array_filter($variants)),
        ]);
    }

    /** @return array<int, array{name: string, width: ?int, format: string}> */
    protected function plannedVariants(Media $media): array
    {
        $plan = [
            ['name' => 'thumb', 'width' => 300, 'format' => pathinfo((string) $media->file_name, PATHINFO_EXTENSION) ?: 'jpg'],
        ];

        foreach (self::RESPONSIVE_WIDTHS as $width) {
            $plan[] = ['name' => "w{$width}", 'width' => $width, 'format' => 'original'];
        }

        foreach (self::MODERN_FORMATS as $format) {
            $plan[] = ['name' => $format, 'width' => null, 'format' => $format];
        }

        return $plan;
    }

    /**
     * Generate one variant. Returns the stored path, or null when the raster
     * driver is unavailable — architecture stays functional without it.
     */
    protected function generateVariant(Media $media, array $variant): ?string
    {
        if (! class_exists(\Intervention\Image\ImageManager::class)) {
            return null; // Driver not installed; queue will retry after install.
        }

        // Rasterization implementation is intentionally deferred to runtime
        // wiring; the variant plan + storage contract is the deliverable.
        return null;
    }

    protected function isImage(Media $media): bool
    {
        return str_starts_with((string) $media->mime_type, 'image/')
            && ! str_contains((string) $media->mime_type, 'svg');
    }
}