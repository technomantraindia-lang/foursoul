<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\Admin;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Request;

/**
 * Prompt 2B (9): Admin-specific login listener.
 *
 * Updates last_login_at and last_login_ip on successful Admin authentication.
 * Keeps Admin login tracking completely separate from Customer (User) login tracking.
 */
class UpdateAdminLastLoginActivity
{
    public function handle(Login $event): void
    {
        // Only act on Admin guard logins
        if ($event->guard !== 'admin') {
            return;
        }

        /** @var Admin|null $admin */
        $admin = $event->user;

        if ($admin instanceof Admin) {
            $admin->forceFill([
                'last_login_at' => now(),
                'last_login_ip' => Request::ip(),
            ])->saveQuietly();
        }
    }
}