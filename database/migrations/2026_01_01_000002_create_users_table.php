<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Users table (Prompt 2A — User Authentication).
 *
 * Additive migration on top of the Prompt 1 `settings` table.
 * Nothing pre-existing is modified or destroyed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();

            // Identity
            $table->uuid('uuid')->unique();

            // Name
            $table->string('full_name');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();

            // Email
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();

            // Mobile (stored separately: e.g. "+971" | "501234567")
            $table->string('mobile_country_code', 8)->nullable();
            $table->string('mobile_number', 20)->nullable();
            $table->timestamp('mobile_verified_at')->nullable();

            // Credentials & avatar
            $table->string('password');
            $table->string('avatar_path')->nullable();

            // Preferences
            $table->string('preferred_language', 10)->nullable();
            $table->string('preferred_currency', 3)->nullable();

            // Geo detection snapshot (trusted sources only)
            $table->string('detected_country_code', 2)->nullable();
            $table->string('detected_currency', 3)->nullable();
            $table->string('last_detected_ip', 45)->nullable();

            $table->string('timezone', 64)->nullable();

            // Consent (terms and privacy tracked separately)
            $table->boolean('marketing_consent')->default(false);
            $table->timestamp('marketing_consent_at')->nullable();
            $table->timestamp('terms_accepted_at')->nullable();
            $table->string('terms_version', 20)->nullable();
            $table->timestamp('privacy_accepted_at')->nullable();
            $table->string('privacy_version', 20)->nullable();

            // Lifecycle
            $table->string('status', 20)->default('active')->index();
            $table->string('registration_source', 40)->nullable();

            // Referrals
            $table->string('referral_code', 20)->nullable()->unique();
            $table->foreignId('referred_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Activity tracking
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->timestamp('last_activity_at')->nullable();

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index('mobile_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};