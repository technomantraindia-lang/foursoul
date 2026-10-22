<?php

declare(strict_types=1);

namespace App\Enums;

enum SubscriptionStatus: string
{
    case Pending = 'pending';
    case Trialing = 'trialing';
    case Active = 'active';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
    case Ended = 'ended';

    /**
     * Only these statuses grant course access.
     */
    public function grantsAccess(): bool
    {
        return in_array($this, [self::Active, self::Trialing], true);
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Trialing => 'Trialing',
            self::Active => 'Active',
            self::Expired => 'Expired',
            self::Cancelled => 'Cancelled',
            self::Ended => 'Ended',
        };
    }

    public function getLabel(): string
    {
        return $this->label();
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}