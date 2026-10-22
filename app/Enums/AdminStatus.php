<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Prompt 2B (§1): Admin account lifecycle states.
 *
 * Only `active` admins may authenticate and access the Filament panel.
 * All status checks must go through this enum's helpers — never compare
 * raw strings in controllers/services.
 */
enum AdminStatus: string
{
    case Active = 'active';
    case Suspended = 'suspended';
    case Disabled = 'disabled';

    /**
     * Default status applied to newly created admins.
     */
    public static function default(): self
    {
        return self::Active;
    }

    /**
     * Whether an admin with this status may authenticate at all.
     */
    public function canAuthenticate(): bool
    {
        return $this === self::Active;
    }

    /**
     * Whether an admin with this status may access the Filament panel.
     *
     * Server-side gate: suspended and disabled are always denied,
     * regardless of what the UI shows.
     */
    public function canAccessPanel(): bool
    {
        return $this === self::Active;
    }

    /**
     * Human-readable label for display in the panel.
     */
    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Suspended => 'Suspended',
            self::Disabled => 'Disabled',
        };
    }
}