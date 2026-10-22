<?php

declare(strict_types=1);

namespace App\Enums;

enum LiveSessionStatus: string
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Live = 'live';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Scheduled => 'Scheduled',
            self::Live => 'Live',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
            self::Expired => 'Expired',
        };
    }

    /**
     * Statuses a learner may see on their calendar / session list.
     */
    public function isPubliclyVisible(): bool
    {
        return in_array($this, [self::Scheduled, self::Live, self::Completed], true);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}