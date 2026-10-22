<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Enrollments: per-user course access records. History is preserved on
 * expiry (status flips, rows never deleted) and progress is never reset
 * on renewal.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->restrictOnDelete();

            // Source of this enrollment (subscription | standalone_purchase | admin_grant)
            $table->string('source', 30)->default('subscription');
            $table->foreignId('entitlement_id')->nullable()->constrained('entitlements')->nullOnDelete();

            // active | expired | revoked | completed
            $table->string('status', 20)->default('active')->index();

            $table->timestamp('enrolled_at');
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('revoked_at')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'course_id']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};