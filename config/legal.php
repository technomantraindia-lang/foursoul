<?php

declare(strict_types=1);

/**
 * Prompt 2A: legal document versions captured at consent time.
 *
 * When the terms or privacy policy materially change, bump the version.
 * Users who accepted an older version can be re-prompted in a later phase.
 */
return [

    'terms' => [
        'version' => '1.0',
        'title' => 'Terms of Service',
        'updated_at' => '2026-08-22',
    ],

    'privacy' => [
        'version' => '1.0',
        'title' => 'Privacy Policy',
        'updated_at' => '2026-08-22',
    ],

    /**
     * Public support contact shown on account-status and legal screens.
     * Replace with the production support address before launch.
     */
    'support_email' => env('SUPPORT_EMAIL', 'support@foursouls.in'),
];