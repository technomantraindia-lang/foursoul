<?php

namespace App\Services\Observability;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Schedule;
use Throwable;

/**
 * Phase 12 — Part 11: Observability.
 *
 * Health checks, performance counters, structured logging with
 * correlation IDs, and application diagnostics. Prometheus-compatible
 * exposition format prepared for future scraping.
 */
class ObservabilityService
{
    protected array $counters = [];

    /** Run all health checks; returns per-check status. */
    public function health(): array
    {
        return [
            'database' => $this->check(fn () => DB::select('SELECT 1')),
            'cache' => $this->check(function (): void {
                cache()->put('health:probe', true, 10);
                cache()->get('health:probe');
            }),
            'queue' => $this->check(fn () => \App\Support\QueueDomains::all()),
            'storage' => $this->check(fn () => app(\App\Services\Storage\StorageManager::class)->disk()),
        ];
    }

    public function healthy(): bool
    {
        return ! in_array(false, $this->health(), true);
    }

    /** Increment a named counter (in-memory; flushed by metrics exporter). */
    public function increment(string $metric, int $by = 1, array $tags = []): void
    {
        $key = $this->key($metric, $tags);
        $this->counters[$key] = ($this->counters[$key] ?? 0) + $by;
    }

    /** Record a timing observation in milliseconds. */
    public function observeTiming(string $metric, float $ms, array $tags = []): void
    {
        $this->increment("{$metric}_ms_sum", (int) round($ms), $tags);
        $this->increment("{$metric}_ms_count", 1, $tags);
    }

    /** Render counters in Prometheus text exposition format. */
    public function renderPrometheus(): string
    {
        $lines = [];

        foreach ($this->counters as $key => $value) {
            [$name, $tagString] = $this->splitKey($key);
            $lines[] = "# TYPE {$name} counter";
            $lines[] = "{$name}{$tagString} {$value}";
        }

        return implode("
", $lines)."
";
    }

    /** Structured log line with correlation ID propagation. */
    public function log(string $level, string $message, array $context = []): void
    {
        Log::channel('production')->{$level}($message, $context + [
            'correlation_id' => request()?->header('X-Correlation-Id') ?? (string) \Illuminate\Support\Str::uuid(),
        ]);
    }

    /** Application diagnostics snapshot for support tooling. */
    public function diagnostics(): array
    {
        return [
            'php' => PHP_VERSION,
            'laravel' => app()->version(),
            'environment' => app()->environment(),
            'memory_usage_mb' => round(memory_get_usage(true) / 1048576, 2),
            'db_connection' => config('database.default'),
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
            'time' => now()->toIso8601String(),
        ];
    }

    protected function check(callable $fn): bool
    {
        try {
            $fn();

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    protected function key(string $metric, array $tags): string
    {
        ksort($tags);

        return $metric.'|'.json_encode($tags);
    }

    protected function splitKey(string $key): array
    {
        [$metric, $tagsJson] = explode('|', $key, 2);
        $tags = json_decode($tagsJson, true) ?: [];
        $tagString = $tags === [] ? '' : '{'.implode(',', array_map(
            fn ($k, $v) => "{$k}="{$v}"",
            array_keys($tags),
            $tags
        )).'}';

        return [$metric, $tagString];
    }
}