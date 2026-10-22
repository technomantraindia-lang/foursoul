<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Application entry-point seeder.
 *
 * Order matters:
 *  1. SettingsSeeder  — platform settings consumed by services.
 *  2. RolesAndPermissionsSeeder — Spatie roles/permissions on the `admin`
 *     guard (idempotent; safe to re-run).
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SettingsSeeder::class,
            RolesAndPermissionsSeeder::class,
        ]);

        $admin = \App\Models\Admin::firstOrNew(['email' => 'admin@foursouls.test']);
        if (! $admin->exists) {
            $admin->uuid = (string) \Illuminate\Support\Str::uuid();
        }
        $admin->fill([
            'name' => 'Super Admin',
            'password' => 'password',
            'status' => \App\Enums\AdminStatus::Active,
            'two_factor_enabled' => false,
        ]);
        $admin->save();
        $admin->assignRole(\App\Enums\AdminRole::SuperAdmin->value);

        $this->call([
            FaqAndFrontendPlanSeeder::class,
        ]);
    }
}