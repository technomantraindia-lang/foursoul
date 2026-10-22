<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Admin;
use App\Enums\AdminRole;
use App\Enums\AdminStatus;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

/**
 * Prompt 2B (16): Create an Admin account via Artisan.
 *
 * Usage: php artisan app:create-admin
 *
 * Prompts for: Name, Email, Password, Password Confirmation
 * Validates: email format, unique admin email, password confirmation
 * Creates: Admin with UUID, hashed password, active status
 * Assigns: chosen admin role (Prompt 2C RBAC, default: Super Admin).
 */
class CreateAdmin extends Command
{
    protected $signature = 'app:create-admin';

    protected $description = 'Create a new administrator account';

    public function handle(): int
    {
        $this->info('Create Administrator Account');
        $this->newLine();

        // Name
        $name = $this->ask('Name', 'Administrator');
        if (blank($name)) {
            $this->error('Name is required.');
            return Command::FAILURE;
        }

        // Email
        $email = $this->ask('Email');
        if (blank($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('A valid email is required.');
            return Command::FAILURE;
        }

        // Check uniqueness
        if (Admin::where('email', $email)->exists()) {
            $this->error('An administrator with this email already exists.');
            return Command::FAILURE;
        }

        // Role (Prompt 2C) — choice() validates input against the role slugs
        $role = AdminRole::from(
            $this->choice('Role', AdminRole::slugs(), AdminRole::SuperAdmin->value)
        );

        // Password
        $password = $this->secret('Password');
        if (blank($password)) {
            $this->error('Password is required.');
            return Command::FAILURE;
        }

        // Password confirmation
        $passwordConfirmation = $this->secret('Confirm Password');
        if ($password !== $passwordConfirmation) {
            $this->error('Passwords do not match.');
            return Command::FAILURE;
        }

        // Validate password strength using Laravel's built-in rule
        $passwordRule = Password::min(8)
            ->mixedCase()
            ->numbers()
            ->symbols()
            ->uncompromised();

        $validator = \Illuminate\Support\Facades\Validator::make(
            ['password' => $password],
            ['password' => ['required', 'string', $passwordRule]]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->get('password') as $error) {
                $this->error($error);
            }
            return Command::FAILURE;
        }

        // Create the admin
        $admin = Admin::create([
            'uuid' => Str::uuid(),
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'status' => AdminStatus::Active,
            'two_factor_enabled' => false,
        ]);

        // Ensure RBAC rows exist (seeder is idempotent), then assign the role.
        $this->call(RolesAndPermissionsSeeder::class);
        $admin->assignRole($role->value);

        $this->newLine();
        $this->info('Administrator created successfully.');
        $this->table(
            ['Field', 'Value'],
            [
                ['UUID', $admin->uuid],
                ['Name', $admin->name],
                ['Email', $admin->email],
                ['Status', $admin->status->label()],
                ['Role', $role->label()],
                ['Created At', $admin->created_at?->format('Y-m-d H:i:s')],
            ]
        );

        return Command::SUCCESS;
    }
}