<?php

declare(strict_types=1);

namespace App\Services\Commerce;

use Illuminate\Support\Str;

/**
 * Phase 5 (§4): Unique human-readable order numbers.
 * Format: FS-YYYYMMDD-XXXXXX (6 random uppercase alphanumerics).
 */
class OrderNumberGenerator
{
    public function generate(): string
    {
        do {
            $number = sprintf(
                'FS-%s-%s',
                now()->format('Ymd'),
                strtoupper(Str::random(6)),
            );
        } while (\App\Models\Order::where('order_number', $number)->exists());

        return $number;
    }
}