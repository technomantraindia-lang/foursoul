<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Subscription plans (Phase 3 — Plans/Subscriptions).
 *
 * Multi-currency prices are independently entered commercial prices
 * (never FX conversions). Sale prices are validated <= regular price at
 * the model/service layer.
 *
 * Additive only — nothing pre-existing is modified or destroyed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();

            // Identity / content
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('short_description')->nullable();
            $table->text('description')->nullable();

            // Lifecycle
            $table->string('status', 20)->default('draft')->index();

            // Billing
            $table->string('billing_type', 30)->index();
            $table->unsignedInteger('duration_days')->nullable();

            // Trial
            $table->boolean('trial_enabled')->default(false);
            $table->unsignedInteger('trial_days')->nullable();

            // Course access
            $table->string('course_access_type', 20)->default('selected_courses')->index();

            // Independent regional pricing (never converted)
            $table->decimal('price_aed', 12, 2)->nullable();
            $table->decimal('sale_price_aed', 12, 2)->nullable();
            $table->decimal('price_usd', 12, 2)->nullable();
            $table->decimal('sale_price_usd', 12, 2)->nullable();
            $table->decimal('price_inr', 12, 2)->nullable();
            $table->decimal('sale_price_inr', 12, 2)->nullable();
            $table->timestamp('sale_start_at')->nullable();
            $table->timestamp('sale_end_at')->nullable();

            // Merchandising
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0)->index();

            // Limits (device/live/community enforced in later phases)
            $table->unsignedInteger('max_active_enrollments')->nullable();
            $table->unsignedInteger('max_devices')->nullable();
            $table->boolean('live_sessions_enabled')->default(true);
            $table->boolean('community_access_enabled')->default(false);

            $table->json('metadata')->nullable();

            // Publishing
            $table->timestamp('published_at')->nullable();

            // Authorship
            $table->foreignId('created_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('admins')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};