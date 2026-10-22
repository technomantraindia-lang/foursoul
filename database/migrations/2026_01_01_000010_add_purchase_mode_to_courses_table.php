<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds commercial purchase mode to courses (Phase 3 — Plans/Subscriptions).
 *
 * - subscription_only: grantable through Plans only.
 * - standalone_only:   purchasable individually only; never auto-granted by a Plan.
 * - both:              grantable either way.
 *
 * Additive only — nothing pre-existing is modified or destroyed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table): void {
            $table->string('purchase_mode', 30)->default('both')->after('status')->index();
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table): void {
            $table->dropColumn('purchase_mode');
        });
    }
};