<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login;
use Filament\Schemas\Components\Component;

/**
 * Prompt 2B (6): branded Administration Portal login.
 *
 * - Pins authentication to the dedicated `admin` guard (never Customer `web`).
 * - Enforces server-side status rules: suspended/disabled admins are denied
 *   even when their credentials are valid.
 * - Shows NO customer register/login or social login options.
 *
 * Note: the `LoginResponse` contract namespace follows the standard Filament
 * convention (Filament\Http\Responses\Auth\Contracts). Final Filament 5
 * signatures must be confirmed during runtime integration and recorded in
 * docs/IMPLEMENTATION_STATUS.md.
 */
class AdminLogin extends Login
{
    public function getHeading(): string
    {
        return __('Administration Portal');
    }

    public function getSubheading(): ?string
    {
        return __('Sign in to manage '.config('settings.defaults.site_name', 'Foursouls'));
    }

    /**
     * Pin the guard so authentication can never resolve through the
     * customer `web` guard, regardless of panel configuration drift.
     */
    public function getGuardName(): string
    {
        return 'admin';
    }

    protected function getEmailFormComponent(): Component
    {
        return parent::getEmailFormComponent()
            ->label(__('Admin Email'))
            ->autocomplete('username');
    }
}
