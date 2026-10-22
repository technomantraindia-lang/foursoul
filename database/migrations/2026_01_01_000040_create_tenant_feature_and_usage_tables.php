<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // PART 4 — Feature flags per tenant
        Schema::create('tenant_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('feature', 64); // courses, commerce, live_sessions, certificates, cms, blog, analytics, reports, api, community, forum, assignments, quizzes, notifications, email, whatsapp, push
            $table->boolean('enabled')->default(false);
            $table->json('config')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'feature']);
        });

        // PART 6 — Storage usage tracking per tenant
        Schema::create('tenant_storage_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('category', 32)->index(); // images|videos|documents|certificates|reports|media|backups
            $table->unsignedBigInteger('bytes_used')->default(0);
            $table->unsignedBigInteger('file_count')->default(0);
            $table->timestamp('last_calculated_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'category']);
        });

        // PART 18 — Tenant audit log
        Schema::create('tenant_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->string('actor_type', 32)->nullable(); // admin|user|system
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('action', 64)->index(); // tenant_created, tenant_suspended, plan_changed, domain_verified, feature_changed...
            $table->string('subject_type', 64)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_audit_logs');
        Schema::dropIfExists('tenant_storage_usage');
        Schema::dropIfExists('tenant_features');
    }
};