<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AdminStatus;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Prompt 2B (17): Admin factory for testing.
 *
 * Provides fake states: active, suspended, disabled.
 * No predictable production credentials.
 */
class AdminFactory extends Factory
{
    protected $model = Admin::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'), // Only for testing
            'avatar_path' => null,
            'status' => AdminStatus::Active,
            'last_login_at' => null,
            'last_login_ip' => null,
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'remember_token' => Str::random(60),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AdminStatus::Active,
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AdminStatus::Suspended,
        ]);
    }

    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AdminStatus::Disabled,
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_enabled' => true,
            'two_factor_secret' => Str::random(32), // Placeholder for encrypted storage
        ]);
    }

    public function withLastLogin(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_login_at' => now()->subDays(fake()->numberBetween(1, 30)),
            'last_login_ip' => fake()->ipv4(),
        ]);
    }
}