<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Prompt 2B (§2): Admin accounts — fully separated from customer `users`.
 *
 * Additive migration; never touches Prompt 2A tables.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->string('name');

            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();

            // Never store plaintext passwords — model casts this to 'hashed'.
            $table->string('password');

            $table->string('avatar_path')->nullable();

            $table->string('status')->default('active')->index();

            $table->timestamp('last_login_at')->nullable()->index();
            $table->string('last_login_ip', 45)->nullable();

            // 2FA prepared but disabled by default; secret stored encrypted later.
            $table->boolean('two_factor_enabled')->default(false);
            $table->text('two_factor_secret')->nullable();

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};