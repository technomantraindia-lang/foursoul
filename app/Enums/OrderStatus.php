<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Phase 5 (§5): Order lifecycle.
 *
 * Lifecycle must never skip states: pending → awaiting_payment → paid,
 * with cancelled/failed/expired as terminal branches and refund_requested
 * / refunded handled after payment success.
 */
enum OrderStatus: string
{
    case Pending = 'pending';
    case AwaitingPayment = 'awaiting_payment';
    case Paid = 'paid';
    case Cancelled = 'cancelled';
    case Failed = 'failed';
    case Expired = 'expired';
    case RefundRequested = 'refund_requested';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::AwaitingPayment => 'Awaiting Payment',
            self::Paid => 'Paid',
            self::Cancelled => 'Cancelled',
            self::Failed => 'Failed',
            self::Expired => 'Expired',
            self::RefundRequested => 'Refund Requested',
            self::Refunded => 'Refunded',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending, self::AwaitingPayment => 'warning',
            self::Paid => 'success',
            self::Cancelled, self::Failed, self::Expired => 'danger',
            self::RefundRequested => 'info',
            self::Refunded => 'gray',
        };
    }

    public function isPaid(): bool
    {
        return $this === self::Paid;
    }

    public function isTerminal(): bool
    {
        return match ($this) {
            self::Cancelled, self::Failed, self::Expired, self::Refunded => true,
            default => false,
        };
    }

    /**
     * Allowed transitions from the current state.
     *
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending => [self::AwaitingPayment, self::Cancelled],
            self::AwaitingPayment => [self::Paid, self::Failed, self::Expired, self::Cancelled],
            self::Paid => [self::RefundRequested, self::Refunded],
            self::RefundRequested => [self::Refunded],
            default => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }
}