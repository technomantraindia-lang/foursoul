<?php

namespace App\Jobs\Concerns;

use App\Support\QueueDomains;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Phase 12 — Part 3: Background job foundation.
 *
 * Base class for all production jobs: pins a queue domain, standard retry/
 * backoff policy, structured logging with correlation IDs, and dead-letter
 * routing on final failure.
 */
abstract class ProductionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var array<int, int> Exponential backoff seconds between retries. */
    public array $backoff = [30, 120, 600];

    public int $timeout = 300;

    public function __construct()
    {
        $this->onQueue($this->queueDomain());
    }

    /** The QueueDomains constant this job runs on. */
    abstract protected function queueDomain(): string;

    public function displayName(): string
    {
        return static::class;
    }

    /**
     * Correlation ID propagated through logs so a request → job → audit trail
     * can be reconstructed (Part 11 observability).
     */
    protected function correlationId(): string
    {
        return (string) ($this->correlationId ??= (string) \Illuminate\Support\Str::ulid());
    }

    public function failed(Throwable $exception): void
    {
        Log::channel('production')->error('job.failed', [
            'job' => static::class,
            'queue' => $this->queue,
            'correlation_id' => $this->correlationId(),
            'attempts' => $this->attempts(),
            'error' => $exception->getMessage(),
        ]);

        // Final failure → dead-letter queue for inspection/replay.
        if (config('queue.dead_letter.enabled', true)) {
            dispatch(function () use ($exception): void {
                Log::channel('production')->warning('job.dead_letter', [
                    'job' => static::class,
                    'error' => $exception->getMessage(),
                ]);
            })->onQueue(QueueDomains::DEAD_LETTER);
        }
    }
}