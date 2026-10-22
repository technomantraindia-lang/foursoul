<?php

declare(strict_types=1);

namespace App\Enums;

enum EnrollmentStatus: string
{
    case Active = 'active';
    case Locked = 'locked';
    case Completed = 'completed';

    /**
     * Only active enrollments grant course access.
     * Locked preserves history + progress without access.
     */
    public function grantsAccess(): bool
    {
        return $this === self::Active;
    }

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Locked => 'Locked',
            self::Completed => 'Completed',
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