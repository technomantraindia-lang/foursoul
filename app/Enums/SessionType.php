<?php

declare(strict_types=1);

namespace App\Enums;

enum SessionType: string
{
    case LiveClass = 'live_class';
    case Webinar = 'webinar';
    case QaSession = 'qa_session';
    case Workshop = 'workshop';

    public function label(): string
    {
        return match ($this) {
            self::LiveClass => 'Live Class',
            self::Webinar => 'Webinar',
            self::QaSession => 'Q&A Session',
            self::Workshop => 'Workshop',
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