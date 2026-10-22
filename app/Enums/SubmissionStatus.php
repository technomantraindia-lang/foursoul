<?php

declare(strict_types=1);

namespace App\Enums;

enum SubmissionStatus: string
{
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case Graded = 'graded';
    case Returned = 'returned';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Submitted',
            self::UnderReview => 'Under Review',
            self::Graded => 'Graded',
            self::Returned => 'Returned for Revision',
            self::Rejected => 'Rejected',
        };
    }

    public function getLabel(): string
    {
        return $this->label();
    }

    public function isGraded(): bool
    {
        return $this === self::Graded;
    }

    public static function values(): array
    {
        return array_map(static fn (self $s): string => $s->value, self::cases());
    }
}