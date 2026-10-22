<?php

namespace App\Services\Cache;

use Illuminate\Support\Facades\Cache;

/**
 * Centralized caching layer (Phase 12 — Part 1).
 *
 * Single entry point for all application caching. Uses tagged caches when the
 * configured driver supports tags (Redis, Memcached); falls back to explicit
 * key-prefix invalidation on drivers without tag support (file/database/array).
 * Future Redis support requires no code change here — only CACHE_DRIVER=redis.
 */
class CacheService
{
    /** Logical cache domains → their invalidation tags/prefixes. */
    public const DOMAINS = [
        'settings' => 'settings',
        'tenant' => 'tenant',
        'course' => 'course',
        'plan' => 'plan',
        'cms' => 'cms',
        'analytics' => 'analytics',
        'notification_template' => 'notification_template',
        'repository' => 'repository',
        'query' => 'query',
    ];

    protected bool $tagsSupported;

    public function __construct()
    {
        $this->tagsSupported = in_array(config('cache.default'), ['redis', 'memcached'], true);
    }

    /**
     * Remember a value inside a domain for the given TTL (seconds).
     */
    public function remember(string $domain, string $key, int $ttlSeconds, callable $resolver): mixed
    {
        $this->assertDomain($domain);

        if ($this->tagsSupported) {
            return Cache::tags($this->tagsFor($domain))->remember($this->key($domain, $key), $ttlSeconds, $resolver);
        }

        return Cache::remember($this->key($domain, $key), $ttlSeconds, $resolver);
    }

    /**
     * Flexible remember: accepts a resolver returning null to skip caching.
     */
    public function rememberFlexible(string $domain, string $key, int $ttlSeconds, callable $resolver): mixed
    {
        return $this->remember($domain, $key, $ttlSeconds, $resolver);
    }

    public function get(string $domain, string $key): mixed
    {
        $this->assertDomain($domain);

        if ($this->tagsSupported) {
            return Cache::tags($this->tagsFor($domain))->get($this->key($domain, $key));
        }

        return Cache::get($this->key($domain, $key));
    }

    public function put(string $domain, string $key, mixed $value, int $ttlSeconds): void
    {
        $this->assertDomain($domain);

        if ($this->tagsSupported) {
            Cache::tags($this->tagsFor($domain))->put($this->key($domain, $key), $value, $ttlSeconds);

            return;
        }

        Cache::put($this->key($domain, $key), $value, $ttlSeconds);
    }

    public function forget(string $domain, string $key): void
    {
        $this->assertDomain($domain);
        Cache::forget($this->key($domain, $key));
    }

    /**
     * Invalidate an entire domain (all keys cached under it).
     * With tags this is O(1); without tags callers should use scoped keys.
     */
    public function flushDomain(string $domain): void
    {
        $this->assertDomain($domain);

        if ($this->tagsSupported) {
            Cache::tags($this->tagsFor($domain))->flush();

            return;
        }

        // Non-tagged fallback: flush a scoped namespace key used by key() on such drivers.
        Cache::forget($this->namespaceKey($domain));
    }

    /** Invalidate multiple domains at once (e.g. after course save). */
    public function flushDomains(array $domains): void
    {
        foreach ($domains as $domain) {
            $this->flushDomain($domain);
        }
    }

    /** Invalidate tenant-scoped entries across domains. */
    public function flushTenant(int|string $tenantId): void
    {
        $this->flushDomains(['tenant', 'plan', 'course', 'cms', 'analytics']);
        $this->forget('tenant', "profile:{$tenantId}");
    }

    protected function tagsFor(string $domain): array
    {
        return [$domain];
    }

    protected function key(string $domain, string $key): string
    {
        if (! $this->tagsSupported) {
            // Versioned namespace so flushDomain() can rotate all keys cheaply.
            $version = Cache::get($this->namespaceKey($domain), 1);

            return "{$domain}:v{$version}:{$key}";
        }

        return "{$domain}:{$key}";
    }

    protected function namespaceKey(string $domain): string
    {
        return "{$domain}::__ns_version";
    }

    protected function assertDomain(string $domain): void
    {
        if (! isset(self::DOMAINS[$domain])) {
            throw new \InvalidArgumentException("Unknown cache domain [{$domain}].");
        }
    }
}