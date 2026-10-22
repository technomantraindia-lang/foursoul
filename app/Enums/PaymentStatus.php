<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Phase 5 (§8): Payment lifecycle, gateway-independent.
 */
enum PaymentStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Succeeded = 'succeeded';
    case Failed = 'failed';
    case Refunded = 'refunded';
    case PartiallyRefunded = 'partially_refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Processing => 'Processing',
            self::Succeeded => 'Succeeded',
            self::Failed => 'Failed',
            self::Refunded => 'Refunded',
            self::PartiallyRefunded => 'Partially Refunded',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending, self::Processing => 'warning',
            self::Succeeded => 'success',
            self::Failed => 'danger',
            self::Refunded, self::PartiallyRefunded => 'gray',
        };
    }

    public function isSuccessful(): bool
    {
        return $this === self::Succeeded;
    }
}