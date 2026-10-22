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
    }
}