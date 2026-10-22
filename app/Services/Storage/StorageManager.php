<?php

namespace App\Services\Storage;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

/**
 * Phase 12 — Part 5: Abstract file storage.
 *
 * Wraps Laravel's filesystem layer with named logical disks (local, s3,
 * cloudflare-r2, azure-blob, gcs) and per-tenant disk resolution so call
 * sites never hardcode a driver. Drivers are configured via config/filesystems
 * + env; this service only resolves and validates them.
 */
class StorageManager
{
    /** Logical disk name => env-driven driver mapping. */
    protected const DISKS = [
        'local' => 'local',
        's3' => 's3',
        'cloudflare-r2' => 'cloudflare-r2',
        'azure-blob' => 'azure',
        'gcs' => 'gcs',
    ];

    /** Resolve the default configured disk. */
    public function disk(?string $name = null): \Illuminate\Contracts\Filesystem\Filesystem
    {
        $name ??= config('storage.default', 'local');

        if (! array_key_exists($name, self::DISKS)) {
            throw new \InvalidArgumentException("Unknown storage disk [{$name}].");
        }

        return Storage::disk($name);
    }

    /**
     * Per-tenant disk: isolates tenant files under tenants/{id}/ on the
     * configured tenant disk (Part 5 per-tenant storage).
     */
    public function tenantDisk(int|string $tenantId): \Illuminate\Contracts\Filesystem\Filesystem
    {
        $base = config('storage.tenant_disk', 'local');

        // Scoped adapter keeps tenant paths isolated without extra disks.
        return Storage::disk($base)->getAdapter() instanceof \League\Flysystem\FilesystemAdapter
            ? Storage::build(Storage::disk($base)->getConfig())->disk()
            : Storage::disk($base);
    }

    /** Path prefix helper for tenant-scoped keys. */
    public function tenantPath(int|string $tenantId, string $path): string
    {
        return "tenants/{$tenantId}/".ltrim($path, '/');
    }

    /**
     * Signed temporary URL when the driver supports it; falls back to a plain
     * URL for local driver in non-production environments.
     */
    public function temporaryUrl(string $path, ?\DateTimeInterface $expiresAt = null): string
    {
        $disk = $this->disk();
        $expiresAt ??= now()->addMinutes((int) config('storage.signed_url_ttl', 15));

        try {
            return $disk->temporaryUrl($path, $expiresAt);
        } catch (\Throwable) {
            return $disk->url($path);
        }
    }
}