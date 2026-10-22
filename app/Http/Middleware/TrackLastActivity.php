<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * §14 — Throttled last_activity_at tracking.
 *
 * Writes to the database at most once per user per window (default 5
 * minutes). The pending timestamp is kept in the cache so the write is
 * not lost — it is flushed on the next eligible request after the
 * throttle window expires.
 *
 * No passwords or tokens are ever recorded.
 */
class TrackLastActivity
{
    /**
     * Minimum seconds between database writes per user.
     */
    public const THROTTLE_SECONDS = 300;

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $user = $request->user();

        if ($user === null || ! $user->exists) {
            return $response;
        }

        $cacheKey = "last-activity:{$user->getAuthIdentifier()}";

        $now = now();
        $lastWrite = Cache::get($cacheKey);

        if ($lastWrite !== null && $now->diffInSeconds($lastWrite) < self::THROTTLE_SECONDS) {
            return $response;
        }

        // Only touch the row when the value actually moves forward; this
        // avoids a pointless UPDATE when the timestamp is already current.
        if ($user->last_activity_at === null || $user->last_activity_at->lt($now->subSeconds(self::THROTTLE_SECONDS))) {
            $user->forceFill(['last_activity_at' => $now])->saveQuietly();
            Cache::put($cacheKey, $now, self::THROTTLE_SECONDS * 2);
        }

        return $response;
    }
}