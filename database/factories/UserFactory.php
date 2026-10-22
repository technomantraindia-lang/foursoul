<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
final class UserFactory extends Factory
{
    protected static ?string $password = null;

    /**
     * The current expiration of the model's password (Laravel 11+ factory contract).
     */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'full_name' => fake()->name(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'mobile_country_code' => '+971',
            'mobile_number' => fake()->numerify('5########'),
            'mobile_verified_at' => now(),
            'password' => static::$password ??= 'Password123!',
            'avatar_path' => null,
            'preferred_language' => 'en',
            'preferred_currency' => 'AED',
            'detected_country_code' => 'AE',
            'detected_currency' => 'AED',
            'last_detected_ip' => fake()->ipv4(),
            'timezone' => 'Asia/Dubai',
            'marketing_consent' => false,
            'marketing_consent_at' => null,
            'terms_accepted_at' => now(),
            'terms_version' => config('legal.terms.version', '1.0'),
            'privacy_accepted_at' => now(),
            'privacy_version' => config('legal.privacy.version', '1.0'),
            'status' => UserStatus::Active->value,
            'registration_source' => 'web',
            'referral_code' => strtoupper(Str::random(8)),
            'referred_by_user_id' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'last_activity_at' => null,
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => UserStatus::Suspended->value,
        ]);
    }

    public function blocked(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => UserStatus::Blocked->value,
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => UserStatus::Closed->value,
        ]);
    }
}