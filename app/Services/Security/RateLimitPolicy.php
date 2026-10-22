<?php

namespace App\Services\Security;

use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;

/**
 * Phase 12 — Part 9: Centralized rate limiting policies.
 *
 * Distinct limits per audience (guests, students, admins, API keys,
 * webhooks) with a single resolution point so middleware and controllers
 * never hardcode limit values.
 */
class RateLimitPolicy
{
    /** @var array<string, array{max_attempts: int, decay_seconds: int}> */
    protected const POLICIES = [
        'guest' => ['max_attempts' => 60, 'decay_seconds' => 60],
        'student' => ['max_attempts' => 120, 'decay_seconds' => 60],
        'admin' => ['max_attempts' => 300, 'decay_seconds' => 60],
        'api_key' => ['max_attempts' => 1000, 'decay_seconds' => 60],
        'webhook_inbound' => ['max_attempts' => 240, 'decay_seconds' => 60],
        'auth' => ['max_attempts' => 5, 'decay_seconds' => 300],
        'export' => ['max_attempts' => 10, 'decay_seconds' => 3600],
    ];

    public function __construct(protected RateLimiter $limiter)
    {
    }

    /**
     * Resolve the policy key for a request based on audience.
     */
    public function resolve(Request $request): string
    {
        if ($request->routeIs('filament.*') || $request->user('admin')) {
            return 'admin';
        }

        if ($request->bearerToken() || $request->header('X-Api-Key')) {
            return 'api_key';
        }

        if ($request->is('webhooks/*') || $request->is('api/webhooks/*')) {
            return 'webhook_inbound';
        }

        return $request->user() ? 'student' : 'guest';
    }

    /**
     * Attempt an action under the resolved (or explicit) policy.
     *
     * @return array{allowed: bool, retry_after: int, remaining: int}
     */
    public function attempt(Request $request, ?string $policy = null, ?string $key = null): array
    {
        $policy ??= $this->resolve($request);
        $config = self::POLICIES[$policy] ?? self::POLICIES['guest'];
        $key = $policy.':'.($key ?? sha1($request->ip().'|'.($request->user()?->id ?? 'guest')));

        if ($this->limiter->tooManyAttempts($key, $config['max_attempts'])) {
            return [
                'allowed' => false,
                'retry_after' => $this->limiter->availableIn($key),
                'remaining' => 0,
            ];
        }

        $this->limiter->hit($key, $config['decay_seconds']);

        return [
            'allowed' => true,
            'retry_after' => 0,
            'remaining' => $config['max_attempts'] - $this->limiter->attempts($key),
        ];
    }

    /** Expose policy config for headers / diagnostics. */
    public function config(string $policy): array
    {
        return self::POLICIES[$policy] ?? self::POLICIES['guest'];
    }
}