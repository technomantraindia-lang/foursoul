<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Phase 5 (§10): Refund lifecycle.
 */
enum RefundStatus: string
{
    case Requested = 'requested';
    case Approved = 'approved';
    case Processing = 'processing';
    case Completed = 'completed';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Requested => 'Requested',
            self::Approved => 'Approved',
            self::Processing => 'Processing',
            self::Completed => 'Completed',
            self::Rejected => 'Rejected',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Requested => 'warning',
            self::Approved, self::Processing => 'info',
            self::Completed => 'success',
            self::Rejected => 'danger',
        };
    }
}