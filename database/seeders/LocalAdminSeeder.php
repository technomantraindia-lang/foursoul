<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AdminRole;
use App\Enums\AdminStatus;
use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LocalAdminSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            return;
        }

        $this->call(RolesAndPermissionsSeeder::class);

        $admin = Admin::firstOrNew(['email' => 'admin@foursouls.test']);

        if (! $admin->exists) {
            $admin->uuid = (string) Str::uuid();
        }

        $admin->fill([
            'name' => 'Super Admin',
            'password' => 'password',
            'status' => AdminStatus::Active,
            'two_factor_enabled' => false,
        ]);

        $admin->save();

        $admin->assignRole(AdminRole::SuperAdmin->value);
    }
}
