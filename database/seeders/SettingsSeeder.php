<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Prompt 1 (Step 8): persists the currency/general settings defaults so
 * admins can edit them later without code changes.
 */
class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = config('settings.defaults', []);

        foreach ($defaults as $key => $value) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                ['group' => 'general', ...Setting::encodeValue($value)],
            );
        }
    }
}