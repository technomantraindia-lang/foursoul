<?php

declare(strict_types=1);

namespace App\Enums;

enum PurchaseMode: string
{
    case Free = 'free';
    case SubscriptionOnly = 'subscription_only';
    case StandaloneOnly = 'standalone_only';
    case Both = 'both';

    public function label(): string
    {
        return match ($this) {
            self::Free => 'Free',
            self::SubscriptionOnly => 'Subscription only',
            self::StandaloneOnly => 'Standalone purchase only',
            self::Both => 'Subscription or standalone purchase',
        };
    }

    /**
     * Courses in these modes may be granted through a subscription plan.
     * standalone_only must never be auto-entitled via a plan.
     */
    public function isPlanGrantable(): bool
    {
        return match ($this) {
            self::Free, self::SubscriptionOnly, self::Both => true,
            self::StandaloneOnly => false,
        };
    }
}