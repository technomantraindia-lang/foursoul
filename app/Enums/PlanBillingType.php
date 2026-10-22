<?php

declare(strict_types=1);

namespace App\Enums;

enum PlanBillingType: string
{
    case FixedDuration = 'fixed_duration';
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case HalfYearly = 'half_yearly';
    case Yearly = 'yearly';
    case Lifetime = 'lifetime';

    /**
     * Access duration in days for this billing type.
     * Lifetime returns null (no expiry).
     */
    public function durationDays(?int $planDurationDays = null): ?int
    {
        return match ($this) {
            self::FixedDuration => $planDurationDays,
            self::Monthly => 30,
            self::Quarterly => 91,
            self::HalfYearly => 182,
            self::Yearly => 365,
            self::Lifetime => null,
        };
    }

    /**
     * Whether subscriptions of this billing type ever expire.
     */
    public function expires(): bool
    {
        return $this !== self::Lifetime;
    }

    public function isLifetime(): bool
    {
        return $this === self::Lifetime;
    }

    public function label(): string
    {
        return match ($this) {
            self::FixedDuration => 'Fixed Duration',
            self::Monthly => 'Monthly',
            self::Quarterly => 'Quarterly',
            self::HalfYearly => 'Half Yearly',
            self::Yearly => 'Yearly',
            self::Lifetime => 'Lifetime',
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