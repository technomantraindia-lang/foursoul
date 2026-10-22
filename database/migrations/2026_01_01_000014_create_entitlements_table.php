<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Entitlements: per-course access rights generated from an active
 * subscription. Revoked on expiry, restored on renewal — rows are never
 * deleted so history and progress survive.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entitlements', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subscription_id')->constrained('subscriptions')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->restrictOnDelete();

            // active | expired | revoked
            $table->string('status', 20)->default('active')->index();

            $table->timestamp('granted_at');
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('revoked_at')->nullable();

            $table->string('source')->nullable();          // e.g. subscription_activation
            $table->string('source_reference')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'course_id', 'subscription_id']);
            $table->index(['user_id', 'course_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entitlements');
    }
};