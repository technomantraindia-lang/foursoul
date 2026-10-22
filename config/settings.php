<?php

declare(strict_types=1);

/**
 * Prompt 1 (Step 8): settings defaults.
 *
 * These values are used when a setting has not yet been stored in the
 * `settings` table. The seeder persists them so admins can edit them later.
 *
 * Groups supported by this foundation:
 * General, Branding, Locale, Currency, LMS, Subscription, Payment,
 * Notifications, Analytics, Integrations, Security.
 */
return [

    'defaults' => [
        // General
        'site_name' => 'Foursouls',
        'business_timezone' => 'Asia/Dubai',

        // Currency (Prompt 1 business rules)
        'base_currency' => 'AED',
        'uae_currency' => 'AED',
        'india_currency' => 'INR',
        'other_currency' => 'USD',
        'allow_manual_currency_switch' => true,
        'geoip_enabled' => true,

        // Locale
        'default_language' => 'en',

        // LMS
        'lms_enabled' => false,

        // Subscription
        'subscriptions_enabled' => false,

        // Payment
        'payment_gateway' => env('PAYMENT_GATEWAY', ''),

        // Notifications
        'notifications_enabled' => true,

        // Analytics
        'analytics_enabled' => false,

        // Integrations
        'wati_enabled' => false,

        // Security
        'force_https' => true,

        // Branding (Phase 9 — CMS Engine, PART 11)
        'brand_logo' => '',
        'brand_favicon' => '',
        'brand_primary_color' => '#ff6b00',
        'brand_font_family' => 'Inter',
        'brand_social_links' => [],
        'footer_about_text' => '',
        'contact_email' => '',
        'contact_phone' => '',
        'copyright_text' => '',

        // Analytics (Phase 9 — CMS Engine, PART 11)
        'analytics_ga_id' => '',
        'analytics_meta_pixel_id' => '',
    ],

    /**
     * Which settings keys are exposed for admin editing per group.
     * Kept minimal in Prompt 1; expanded in later phases.
     */

    /**
     * Which settings keys are exposed for admin editing per group.
     * Kept minimal in Prompt 1; expanded in later phases.
     */
    'groups' => [
        'general' => ['site_name', 'business_timezone'],
        'currency' => [
            'base_currency', 'uae_currency', 'india_currency', 'other_currency',
            'allow_manual_currency_switch', 'geoip_enabled',
        ],
        'branding' => [
            'brand_logo', 'brand_favicon', 'brand_primary_color', 'brand_font_family',
            'brand_social_links', 'footer_about_text', 'contact_email', 'contact_phone',
            'copyright_text',
        ],
        'analytics' => ['analytics_enabled', 'analytics_ga_id', 'analytics_meta_pixel_id'],
    ],
];