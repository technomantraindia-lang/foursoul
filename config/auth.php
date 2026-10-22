<?php

declare(strict_types=1);

/**
 * Authentication configuration (Prompt 2B).
 *
 * Two fully separated authentication domains:
 *
 * - Customers: `web` guard + `users` provider + `users` password broker
 *   (Prompt 2A behaviour preserved unchanged).
 * - Admins:    `admin` guard + `admins` provider + `admins` password broker
 *   backed by the dedicated `admin_password_reset_tokens` table.
 *
 * A Customer session must never grant access to the Filament admin panel,
 * and vice versa. Password reset tables/brokers are likewise isolated.
 */
return [

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    */
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'admin' => [
            'driver' => 'session',
            'provider' => 'admins',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    */
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', App\Models\User::class),
        ],

        'admins' => [
            'driver' => 'eloquent',
            'model' => App\Models\Admin::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | The `admins` broker deliberately uses its own token table so a Customer
    | password reset can never touch an Admin account, and vice versa.
    |
    */
    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],

        'admins' => [
            'provider' => 'admins',
            'table' => 'admin_password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];