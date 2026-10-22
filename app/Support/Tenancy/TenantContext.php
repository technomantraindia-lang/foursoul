<?php

declare(strict_types=1);

namespace App\Support\Tenancy;

/**
 * Current tenant for the request/job. Never populated from request input.
 */
final class TenantContext
{
    private static ?int $tenantId = null;

    public static function set(?int $tenantId): void
    {
        if ($tenantId !== null && $tenantId < 1) {
            throw new \InvalidArgumentException('Tenant id must be a positive integer.');
        }

        self::$tenantId = $tenantId;
    }

    public static function id(): ?int
    {
        return self::$tenantId;
    }

    public static function forget(): void
    {
        self::$tenantId = null;
    }
}
