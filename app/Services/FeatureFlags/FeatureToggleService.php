<?php

namespace App\Services\FeatureFlags;

use App\Models\Tenant;
use App\Models\TenantFeature;
use Illuminate\Support\Facades\Cache;

/**
 * Phase 12 — Part 12: Feature Toggles.
 *
 * Layered resolution: kill switch (config/env) > tenant override
 * (tenant_features table) > global flag (settings/config) > default.
 *
 * Flag types: development, beta, hidden, standard. Hidden flags are
 * resolvable programmatically but excluded from public listings.
 */
class FeatureToggleService
{
    protected const CACHE_KEY = 'feature-flags:global';

    /** Resolve a feature for an optional tenant context. */
    public function enabled(string $key, ?Tenant $tenant = null): bool
    {
        // Kill switches always win (env-driven, e.g. FEATURE_<KEY>_DISABLED).
        if ($this->isKilled($key)) {
            return false;
        }

        if ($tenant !== null) {
            $tenantFlag = TenantFeature::query()
                ->where('tenant_id', $tenant->id)
                ->where('key', $key)
                ->first();

            if ($tenantFlag !== null) {
                return (bool) $tenantFlag->enabled;
            }
        }

        return (bool) ($this->globalFlags()[$key]['enabled'] ?? false);
    }

    /** Assert a feature is on, otherwise throw. */
    public function assertEnabled(string $key, ?Tenant $tenant = null): void
    {
        if (! $this->enabled($key, $tenant)) {
            throw new \RuntimeException("Feature [{$key}] is not enabled.");
        }
    }

    /** Set a global flag. */
    public function setGlobal(string $key, bool $enabled, string $type = 'standard'): void
    {
        $flags = $this->globalFlags();
        $flags[$key] = ['enabled' => $enabled, 'type' => $type];
        Cache::forever(self::CACHE_KEY, $flags);
    }

    /** Remove a global flag. */
    public function forget(string $key): void
    {
        $flags = $this->globalFlags();
        unset($flags[$key]);
        Cache::forever(self::CACHE_KEY, $flags);
    }

    /** Public listing excludes hidden and development-only flags outside local env. */
    public function listVisible(?Tenant $tenant = null): array
    {
        $hiddenTypes = app()->environment('local', 'staging') ? ['hidden'] : ['hidden', 'development'];

        return collect($this->globalFlags())
            ->reject(fn (array $flag) => in_array($flag['type'], $hiddenTypes, true))
            ->map(fn (array $flag, string $key) => [
                'key' => $key,
                'enabled' => $this->enabled($key, $tenant),
                'type' => $flag['type'],
            ])
            ->values()
            ->all();
    }

    /** Environment-level kill switch: FEATURE_{SNAKE}_DISABLED=true. */
    protected function isKilled(string $key): bool
    {
        $envKey = 'FEATURE_'.strtoupper(str_replace(['.', '-'], '_', $key)).'_DISABLED';

        return filter_var(env($envKey, false), FILTER_VALIDATE_BOOL);
    }

    /** Global flags from cache, seeded from config/feature-flags.php defaults. */
    protected function globalFlags(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return collect(config('feature-flags.flags', []))
                ->map(fn (array $flag) => [
                    'enabled' => (bool) ($flag['enabled'] ?? false),
                    'type' => $flag['type'] ?? 'standard',
                ])
                ->all();
        });
    }
}