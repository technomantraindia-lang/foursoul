<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Prompt 6 (§1): Supported question types for the question bank.
 *
 * `file_upload` is reserved for a future phase and always requires
 * manual review when it eventually becomes active.
 */
enum QuestionType: string
{
    case SingleChoice = 'single_choice';
    case MultipleChoice = 'multiple_choice';
    case TrueFalse = 'true_false';
    case Numeric = 'numeric';
    case ShortAnswer = 'short_answer';
    case Paragraph = 'paragraph';
    case FileUpload = 'file_upload';

    public function label(): string
    {
        return match ($this) {
            self::SingleChoice => 'Single Choice',
            self::MultipleChoice => 'Multiple Choice',
            self::TrueFalse => 'True / False',
            self::Numeric => 'Numeric',
            self::ShortAnswer => 'Short Answer',
            self::Paragraph => 'Paragraph',
            self::FileUpload => 'File Upload',
        };
    }

    /**
     * Types the evaluation engine can grade automatically.
     */
    public function isAutoGradable(): bool
    {
        return match ($this) {
            self::SingleChoice,
            self::MultipleChoice,
            self::TrueFalse,
            self::Numeric => true,
            self::ShortAnswer => false, // exact-match capable later; manual by default
            self::Paragraph,
            self::FileUpload => false,
        };
    }

    public static function values(): array
    {
        return array_map(static fn (self $t): string => $t->value, self::cases());
    }
}