<?php

declare(strict_types=1);

namespace App\Enums;

enum EntitlementStatus: string
{
    case Active = 'active';
    case Expired = 'expired';
    case Revoked = 'revoked';

    public function grantsAccess(): bool
    {
        return $this === self::Active;
    }

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Expired => 'Expired',
            self::Revoked => 'Revoked',
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