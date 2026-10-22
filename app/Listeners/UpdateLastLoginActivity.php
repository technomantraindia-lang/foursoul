<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Prompt 2A §14: on successful login, record last_login_at / last_login_ip
 * and seed last_activity_at. Runs on the queue when available so the login
 * response is never delayed by the write.
 *
 * Passwords and tokens are never logged or stored here.
 */
class UpdateLastLoginActivity implements ShouldQueue
{
    /**
     * Best-effort listener; a failed tracking write must not fail the login.
     */
    public $afterCommit = true;

    public function handle(Login $event): void
    {
        $user = $event->user;

        if (! $user instanceof User) {
            return;
        }

        $ip = request()?->ip();

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $ip,
            'last_activity_at' => now(),
        ])->save();
    }
}