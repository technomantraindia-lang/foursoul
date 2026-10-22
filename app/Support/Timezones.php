<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Curated timezone list for the preferences/profile selectors.
 *
 * Grouped by region for a clean <optgroup> UI. PHP DateTimeZone
 * identifiers are used so they are directly usable with
 * date_default_timezone_set() / Carbon::setUserTimezone().
 */
final class Timezones
{
    /**
     * @return array<string, array<string, string>> Region => identifier => label.
     */
    public static function grouped(): array
    {
        return [
            'Middle East' => [
                'Asia/Dubai' => 'Dubai (GST, UTC+4)',
                'Asia/Riyadh' => 'Riyadh (AST, UTC+3)',
                'Asia/Qatar' => 'Doha (AST, UTC+3)',
                'Asia/Kuwait' => 'Kuwait (AST, UTC+3)',
                'Asia/Bahrain' => 'Manama (AST, UTC+3)',
                'Asia/Muscat' => 'Muscat (GST, UTC+4)',
            ],
            'Asia' => [
                'Asia/Kolkata' => 'India (IST, UTC+5:30)',
                'Asia/Singapore' => 'Singapore (SGT, UTC+8)',
                'Asia/Kuala_Lumpur' => 'Kuala Lumpur (MYT, UTC+8)',
                'Asia/Manila' => 'Manila (PHT, UTC+8)',
                'Asia/Jakarta' => 'Jakarta (WIB, UTC+7)',
                'Asia/Karachi' => 'Karachi (PKT, UTC+5)',
                'Asia/Dhaka' => 'Dhaka (BST, UTC+6)',
                'Asia/Kathmandu' => 'Kathmandu (NPT, UTC+5:45)',
            ],
            'Europe' => [
                'Europe/London' => 'London (GMT/BST)',
                'Europe/Dublin' => 'Dublin (GMT/IST)',
                'Europe/Paris' => 'Paris (CET/CEST)',
                'Europe/Berlin' => 'Berlin (CET/CEST)',
                'Europe/Madrid' => 'Madrid (CET/CEST)',
                'Europe/Rome' => 'Rome (CET/CEST)',
                'Europe/Amsterdam' => 'Amsterdam (CET/CEST)',
                'Europe/Istanbul' => 'Istanbul (TRT, UTC+3)',
            ],
            'Americas' => [
                'America/New_York' => 'New York (ET)',
                'America/Chicago' => 'Chicago (CT)',
                'America/Denver' => 'Denver (MT)',
                'America/Los_Angeles' => 'Los Angeles (PT)',
                'America/Toronto' => 'Toronto (ET)',
                'America/Vancouver' => 'Vancouver (PT)',
                'America/Sao_Paulo' => 'São Paulo (BRT)',
            ],
            'Africa & Oceania' => [
                'Africa/Cairo' => 'Cairo (EET)',
                'Africa/Lagos' => 'Lagos (WAT)',
                'Africa/Nairobi' => 'Nairobi (EAT)',
                'Africa/Johannesburg' => 'Johannesburg (SAST)',
                'Australia/Sydney' => 'Sydney (AEST/AEDT)',
                'Pacific/Auckland' => 'Auckland (NZST/NZDT)',
            ],
            'UTC' => [
                'UTC' => 'UTC',
            ],
        ];
    }

    /**
     * Flat list of all supported identifiers.
     *
     * @return list<string>
     */
    public static function all(): array
    {
        $all = [];
        foreach (self::grouped() as $zones) {
            foreach ($zones as $identifier => $label) {
                $all[] = $identifier;
            }
        }

        return $all;
    }

    public static function isValid(?string $timezone): bool
    {
        return $timezone !== null && in_array($timezone, self::all(), true);
    }
}