<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Supported UI languages (Prompt 2A).
 *
 * English is the primary language. Arabic and Hindi are offered as
 * secondary options for the UAE/India markets. Language packs are a
 * future concern; the preference is stored now so the account model
 * is complete.
 */
final class Locales
{
    /**
     * @return array<string, string> Map of code => human label.
     */
    public static function all(): array
    {
        return [
            'en' => 'English',
            'ar' => 'العربية (Arabic)',
            'hi' => 'हिन्दी (Hindi)',
        ];
    }

    /**
     * Alias used by auth/register Blade selectors.
     *
     * @return array<string, string> Map of code => human label.
     */
    public static function options(): array
    {
        return self::all();
    }

    public static function isValid(?string $code): bool
    {
        return $code !== null && array_key_exists($code, self::all());
    }
}