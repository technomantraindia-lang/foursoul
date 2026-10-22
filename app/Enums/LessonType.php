<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Phase 2: How a lesson delivers its content.
 *
 * - Video:  hosted/streamed video (URL stored on the lesson).
 * - Text:   rich text / article content.
 * - Quiz:   assessment placeholder (content URL unused for now).
 * - Download: file attachment (URL points to a downloadable asset).
 */
enum LessonType: string implements HasLabel
{
    case Video = 'video';
    case Text = 'text';
    case Quiz = 'quiz';
    case Download = 'download';

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
            self::Video => 'Video',
            self::Text => 'Text',
            self::Quiz => 'Quiz',
            self::Download => 'Download',
        };
    }

    public function getLabel(): string
    {
        return $this->label();
    }
}
