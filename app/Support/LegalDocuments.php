<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Prompt 2A: centralised access to legal document metadata.
 *
 * Versions are captured at consent time (see RegisterRequest) so the
 * platform can prove which terms/privacy text a user accepted.
 */
final class LegalDocuments
{
    public static function termsVersion(): string
    {
        return (string) config('legal.terms.version', '1.0');
    }

    public static function termsTitle(): string
    {
        return (string) config('legal.terms.title', 'Terms of Service');
    }

    public static function termsUpdatedAt(): string
    {
        return (string) config('legal.terms.updated_at', '');
    }

    public static function privacyVersion(): string
    {
        return (string) config('legal.privacy.version', '1.0');
    }

    public static function privacyTitle(): string
    {
        return (string) config('legal.privacy.title', 'Privacy Policy');
    }

    public static function privacyUpdatedAt(): string
    {
        return (string) config('legal.privacy.updated_at', '');
    }

    /**
     * Public support contact shown on account-status and legal screens.
     */
    public static function supportEmail(): string
    {
        return (string) config('legal.support_email', 'support@foursouls.in');
    }
}