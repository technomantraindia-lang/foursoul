<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Admin;
use Illuminate\Support\Str;

/**
 * Prompt 2B (§3): Generates a UUID automatically for every admin
 * when one has not been supplied explicitly.
 */
class AdminObserver
{
    public function creating(Admin $admin): void
    {
        if (empty($admin->uuid)) {
            $admin->uuid = (string) Str::uuid();
        }
    }
}