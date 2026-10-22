<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Phase 2: Course lifecycle states.
 *
 * - Draft:   visible only in the admin panel, never enrollable.
 * - Published: live in the catalogue; publishing stamps published_at.
 * - Archived: retired from the catalogue but retained for history.
 */
enum CourseStatus: string implements HasLabel
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Published => 'Published',
            self::Archived => 'Archived',
        };
    }

    public function getLabel(): string
    {
        return $this->label();
    }

    /**
     * Whether courses in this state appear in the customer-facing catalogue.
     */
    public function isPubliclyVisible(): bool
    {
        return $this === self::Published;
    }
}