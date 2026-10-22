<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Dialing codes for the registration/profile country-code selector.
 *
 * A curated, commonly used list (not exhaustive) — ordered with the
 * primary markets first (UAE, India) then alphabetical. Values are
 * stored WITHOUT the leading "+" in the database; the UI renders it.
 */
final class CountryCodes
{
    /**
     * @return array<string, string> Map of ISO 3166-1 alpha-2 code => dialing code (e.g. 'AE' => '+971').
     */
    public static function all(): array
    {
        return [
            'AE' => '+971',
            'IN' => '+91',
            'US' => '+1',
            'GB' => '+44',
            'AU' => '+61',
            'CA' => '+1',
            'SA' => '+966',
            'QA' => '+974',
            'KW' => '+965',
            'BH' => '+973',
            'OM' => '+968',
            'EG' => '+20',
            'PK' => '+92',
            'BD' => '+880',
            'LK' => '+94',
            'NP' => '+977',
            'SG' => '+65',
            'MY' => '+60',
            'PH' => '+63',
            'ID' => '+62',
            'DE' => '+49',
            'FR' => '+33',
            'IT' => '+39',
            'ES' => '+34',
            'NL' => '+31',
            'TR' => '+90',
            'ZA' => '+27',
            'NG' => '+234',
            'KE' => '+254',
            'BR' => '+55',
        ];
    }

    /**
     * Options for the country-code <select>: ISO code, dialing code,
     * human-readable name and flag emoji. Primary markets first.
     *
     * @return array<int, array{code: string, dial: string, name: string, flag: string}>
     */
    public static function dialingOptions(): array
    {
        $meta = [
            'AE' => ['name' => 'United Arab Emirates', 'flag' => '🇦🇪'],
            'IN' => ['name' => 'India', 'flag' => '🇮🇳'],
            'US' => ['name' => 'United States', 'flag' => '🇺🇸'],
            'GB' => ['name' => 'United Kingdom', 'flag' => '🇬🇧'],
            'AU' => ['name' => 'Australia', 'flag' => '🇦🇺'],
            'CA' => ['name' => 'Canada', 'flag' => '🇨🇦'],
            'SA' => ['name' => 'Saudi Arabia', 'flag' => '🇸🇦'],
            'QA' => ['name' => 'Qatar', 'flag' => '🇶🇦'],
            'KW' => ['name' => 'Kuwait', 'flag' => '🇰🇼'],
            'BH' => ['name' => 'Bahrain', 'flag' => '🇧🇭'],
            'OM' => ['name' => 'Oman', 'flag' => '🇴🇲'],
            'EG' => ['name' => 'Egypt', 'flag' => '🇪🇬'],
            'PK' => ['name' => 'Pakistan', 'flag' => '🇵🇰'],
            'BD' => ['name' => 'Bangladesh', 'flag' => '🇧🇩'],
            'LK' => ['name' => 'Sri Lanka', 'flag' => '🇱🇰'],
            'NP' => ['name' => 'Nepal', 'flag' => '🇳🇵'],
            'SG' => ['name' => 'Singapore', 'flag' => '🇸🇬'],
            'MY' => ['name' => 'Malaysia', 'flag' => '🇲🇾'],
            'PH' => ['name' => 'Philippines', 'flag' => '🇵🇭'],
            'ID' => ['name' => 'Indonesia', 'flag' => '🇮🇩'],
            'DE' => ['name' => 'Germany', 'flag' => '🇩🇪'],
            'FR' => ['name' => 'France', 'flag' => '🇫🇷'],
            'IT' => ['name' => 'Italy', 'flag' => '🇮🇹'],
            'ES' => ['name' => 'Spain', 'flag' => '🇪🇸'],
            'NL' => ['name' => 'Netherlands', 'flag' => '🇳🇱'],
            'TR' => ['name' => 'Türkiye', 'flag' => '🇹🇷'],
            'ZA' => ['name' => 'South Africa', 'flag' => '🇿🇦'],
            'NG' => ['name' => 'Nigeria', 'flag' => '🇳🇬'],
            'KE' => ['name' => 'Kenya', 'flag' => '🇰🇪'],
            'BR' => ['name' => 'Brazil', 'flag' => '🇧🇷'],
        ];

        $options = [];
        foreach (self::all() as $code => $dial) {
            $options[] = [
                'code' => $code,
                'dial' => $dial,
                'name' => $meta[$code]['name'] ?? $code,
                'flag' => $meta[$code]['flag'] ?? '🏳️',
            ];
        }

        return $options;
    }

    /**
     * Validate a dialing code against the supported list.
     *
     * Accepts input with or without a leading "+", normalizes to "+XXX".
     */
    public static function normalize(?string $code): ?string
    {
        if ($code === null) {
            return null;
        }

        $trimmed = trim($code);
        if ($trimmed === '') {
            return null;
        }

        $withPlus = str_starts_with($trimmed, '+') ? $trimmed : '+'.$trimmed;

        return in_array($withPlus, self::all(), true) ? $withPlus : null;
    }
}