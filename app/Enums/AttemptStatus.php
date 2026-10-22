<?php

declare(strict_types=1);

namespace App\Enums;

enum AttemptStatus: string
{
    case InProgress = 'in_progress';
    case Submitted = 'submitted';
    case Completed = 'completed';
    case Expired = 'expired';
    case Abandoned = 'abandoned';

    public function label(): string
    {
        return match ($this) {
            self::InProgress => 'In Progress',
            self::Submitted => 'Submitted',
            self::Completed => 'Completed',
            self::Expired => 'Expired',
            self::Abandoned => 'Abandoned',
        };
    }

    public function isFinished(): bool
    {
        return in_array($this, [self::Submitted, self::Completed, self::Expired, self::Abandoned], true);
    }

    public function isAnswerable(): bool
    {
        return $this === self::InProgress;
    }

    public static function values(): array
    {
        return array_map(static fn (self $s): string => $s->value, self::cases());
    }
}