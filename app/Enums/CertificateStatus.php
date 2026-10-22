<?php

declare(strict_types=1);

namespace App\Enums;

enum CertificateStatus: string
{
    case Issued = 'issued';
    case Revoked = 'revoked';

    public function label(): string
    {
        return match ($this) {
            self::Issued => 'Issued',
            self::Revoked => 'Revoked',
        };
    }
}
