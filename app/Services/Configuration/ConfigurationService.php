<?php

namespace App\Services\Configuration;

use Illuminate\Support\Facades\Config;

/**
 * Phase 12 — Part 13: Central Configuration Service.
 *
 * Environment abstraction and secrets access with no hardcoded
 * configuration in application code. All runtime configuration flows
 * through this service so drivers, credentials and feature wiring can
 * be swapped per environment without code changes.
 */
class ConfigurationService
{
    /**
     * Get a configuration value with environment override support.
     *
     * Resolution order: env var (CONFIG_{SNAKE_KEY}) > config() value > default.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $envKey = 'CONFIG_'.strtoupper(str_replace(['.', '-'], '_', $key));

        if (($env = env($envKey)) !== null) {
            return $env;
        }

        return Config::get($key, $default);
    }

    /** Boolean-typed config read. */
    public function bool(string $key, bool $default = false): bool
    {
        return filter_var($this->get($key, $default), FILTER_VALIDATE_BOOL);
    }

    /** Integer-typed config read. */
    public function int(string $key, int $default = 0): int
    {
        return (int) $this->get($key, $default);
    }

    /** String-typed config read. */
    public function string(string $key, string $default = ''): string
    {
        return (string) $this->get($key, $default);
    }

    /**
     * Secrets abstraction. Secrets are never cached or logged; they are
     * resolved from the environment (or a future vault driver) at call time.
     */
    public function secret(string $name, ?string $default = null): ?string
    {
        return env($name, $default);
    }

    /** Require a secret to be present, otherwise fail fast with a clear error. */
    public function requireSecret(string $name): string
    {
        $value = $this->secret($name);

        if ($value === null || $value === '') {
            throw new \RuntimeException("Required secret [{$name}] is not configured.");
        }

        return $value;
    }

    /** Current environment name. */
    public function environment(): string
    {
        return app()->environment();
    }

    /** True when running in a production-like environment. */
    public function isProduction(): bool
    {
        return app()->environment('production');
    }

    /** Runtime diagnostics-safe view of configuration (no secrets). */
    public function snapshot(array $keys): array
    {
        return collect($keys)
            ->mapWithKeys(fn (string $key) => [$key => $this->get($key)])
            ->all();
    }
}