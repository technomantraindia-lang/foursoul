<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Phase 5 (§11): Coupon discount type.
 */
enum CouponType: string
{
    case Percentage = 'percentage';
    case Fixed = 'fixed';

    public function label(): string
    {
        return match ($this) {
            self::Percentage => 'Percentage',
            self::Fixed => 'Fixed Amount',
        };
    }
}