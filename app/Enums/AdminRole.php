<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Prompt 2C (§1): Admin role definitions — RBAC foundation.
 *
 * Roles are persisted through Spatie Laravel Permission on the dedicated
 * `admin` guard (never the customer `web` guard). `Super Admin` bypasses
 * every ability check via Gate::before (registered in AppServiceProvider)
 * AND holds every defined permission explicitly, so both `$admin->can()`
 * and `$admin->hasPermissionTo()` behave consistently.
 *
 * Permission naming convention: `{domain}.{action}` where action is
 * `view` (read-only) or a specific mutating action (`manage`, `refund`).
 */
enum AdminRole: string
{
    case SuperAdmin = 'super_admin';
    case SupportAdmin = 'support_admin';
    case SessionManager = 'session_manager';
    case FinanceAdmin = 'finance_admin';
    case MarketingAdmin = 'marketing_admin';
    case ContentAdmin = 'content_admin';

    /**
     * Human-readable label for display in the Filament panel.
     */
    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::SupportAdmin => 'Support Admin',
            self::SessionManager => 'Session Manager',
            self::FinanceAdmin => 'Finance Admin',
            self::MarketingAdmin => 'Marketing Admin',
            self::ContentAdmin => 'Content Admin',
        };
    }

    /**
     * Permission names assigned to this role (guard: `admin`).
     *
     * @return list<string>
     */
    public function permissions(): array
    {
        return match ($this) {
            self::SuperAdmin => self::allPermissions(),

            self::SupportAdmin => [
                'customers.view',
                'customers.manage',
                'subscriptions.view',
                'enrollments.view',
                'enrollments.manage',
                'sessions.view',
                'reports.view',
            ],

            self::SessionManager => [
                'sessions.view',
                'sessions.manage',
                'enrollments.view',
                'reports.view',
            ],

            self::FinanceAdmin => [
                'payments.view',
                'payments.refund',
                'subscriptions.view',
                'subscriptions.manage',
                'plans.view',
                'settings.view',
                'reports.view',
            ],

            self::MarketingAdmin => [
                'cms.view',
                'cms.manage',
                'customers.view',
                'reports.view',
            ],

            self::ContentAdmin => [
                'courses.view',
                'courses.manage',
                'sections.view',
                'sections.manage',
                'lessons.view',
                'lessons.manage',
                'cms.view',
                'reports.view',
            ],
        };
    }

    /**
     * Complete permission catalogue for the admin panel.
     *
     * Single source of truth used by the seeder and the Super Admin role.
     * New domains must be added here first, then mapped onto roles.
     *
     * @return list<string>
     */
    public static function allPermissions(): array
    {
        return [
            'customers.view',
            'customers.manage',

            'admins.view',
            'admins.manage',

            'courses.view',
            'courses.manage',

            'sections.view',
            'sections.manage',

            'lessons.view',
            'lessons.manage',

            'plans.view',
            'plans.manage',

            'subscriptions.view',
            'subscriptions.manage',

            'enrollments.view',
            'enrollments.manage',

            'payments.view',
            'payments.refund',

            'sessions.view',
            'sessions.manage',

            'cms.view',
            'cms.manage',

            'reports.view',

            'settings.view',
            'settings.manage',
        ];
    }

    /**
     * All role slugs — used for CLI/validation error messages.
     *
     * @return list<string>
     */
    public static function slugs(): array
    {
        return array_map(static fn (self $role): string => $role->value, self::cases());
    }
}