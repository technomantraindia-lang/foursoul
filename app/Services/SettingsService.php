<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Prompt 1 (Step 8): simple, maintainable settings service.
 *
 * - Reads are cached (Laravel cache; falls back to the configured store,
 *   e.g. file/database locally, Redis in production).
 * - Writes update the DB and flush the cache.
 * - Defaults come from config/settings.php so the app works even before
 *   the seeder runs.
 *
 * Intentionally NOT an overengineered framework: one service, one table.
 */
class SettingsService
{
    public const CACHE_KEY = 'settings.all';

    /** @var array<string, mixed>|null */
    private ?array $cache = null;

    /**
     * Get a setting value by key ("group.key" or bare key), falling back to
     * config/settings.php defaults, then to the given default.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $all = $this->all();

        if (array_key_exists($key, $all)) {
            return $all[$key];
        }

        return config("settings.defaults.$key", $default);
    }

    /**
     * Persist a setting value (upsert) and refresh the cache.
     */
    public function set(string $key, mixed $value, string $group = 'general'): void
    {
        [$group, $key] = $this->splitKey($key, $group);

        $encoded = Setting::encodeValue($value);

        DB::table('settings')->updateOrInsert(
            ['key' => $key],
            [
                'group' => $group,
                'value' => $encoded['value'],
                'type' => $encoded['type'],
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );

        $this->flush();
    }

    /**
     * All stored settings as key => decoded value, merged over nothing
     * (defaults remain in config and are applied at read time).
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        if ($this->cache !== null) {
            return $this->cache;
        }

        $this->cache = Cache::rememberForever(self::CACHE_KEY, function () {
            return Setting::query()
                ->get()
                ->mapWithKeys(fn (Setting $setting) => [$setting->key => $setting->decodedValue()])
                ->all();
        });

        return $this->cache;
    }

    public function forget(string $key): void
    {
        Setting::where('key', $key)->delete();

        $this->flush();
    }

    public function flush(): void
    {
        $this->cache = null;

        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array{0: string, 1: string} [group, bareKey]
     */
    private function splitKey(string $key, string $fallbackGroup): array
    {
        if (str_contains($key, '.')) {
            [$group, $bare] = explode('.', $key, 2);

            return [$group, $bare];
        }

        return [$fallbackGroup, $key];
    }
}