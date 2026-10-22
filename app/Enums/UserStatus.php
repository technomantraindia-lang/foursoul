<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * User account lifecycle status (Prompt 2A).
 *
 * Rules:
 * - active:    Normal account access.
 * - suspended: Account/history remains, protected functionality restricted.
 *              User may still sign in and view the status page, but cannot
 *              use protected application features until reinstated.
 * - blocked:   Authentication/access denied entirely.
 * - closed:    Account preserved historically but normal access disabled.
 *
 * User history is never hard-deleted; closure is a soft state.
 */
enum UserStatus: string
{
    case Active = 'active';
    case Suspended = 'suspended';
    case Blocked = 'blocked';
    case Closed = 'closed';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Suspended => 'Suspended',
            self::Blocked => 'Blocked',
            self::Closed => 'Closed',
        };
    }

    /** Can this user authenticate at all? */
    public function canAuthenticate(): bool
    {
        return match ($this) {
            self::Active, self::Suspended => true,
            self::Blocked, self::Closed => false,
        };
    }

    /** Can this user access protected application functionality? */
    public function canUseProtectedFeatures(): bool
    {
        return $this === self::Active;
    }
}