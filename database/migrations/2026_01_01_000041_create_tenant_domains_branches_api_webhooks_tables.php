<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // PART 7 — Custom domains
        Schema::create('tenant_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('domain')->unique();
            $table->boolean('is_primary')->default(false);
            $table->string('ssl_status', 24)->default('pending'); // pending|issued|failed|expired
            $table->string('dns_status', 24)->default('pending'); // pending|verified|failed
            $table->timestamp('verified_at')->nullable();
            $table->string('verification_token', 128)->nullable();
            $table->boolean('redirect_to_primary')->default(false);
            $table->timestamps();

            $table->index(['tenant_id', 'is_primary']);
        });

        // PART 9 — Branches / departments
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('code', 32)->nullable();
            $table->text('address')->nullable();
            $table->unsignedBigInteger('user_limit')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'name']);
        });

        Schema::create('branch_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_manager')->default(false);
            $table->timestamps();

            $table->unique(['branch_id', 'user_id']);
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('name');
            $table->timestamps();

            $table->index(['tenant_id', 'name']);
        });

        // PART 10 — API keys
        Schema::create('tenant_api_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('key_prefix', 16);
            $table->string('key_hash');
            $table->json('scopes')->nullable(); // e.g. ["courses.read","users.write"]
            $table->unsignedInteger('rate_limit_per_minute')->default(60);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'revoked_at']);
        });

        // PART 11 — Webhooks
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('url', 2048);
            $table->json('events'); // subscribed event names
            $table->string('secret');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('webhook_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('webhook_id')->constrained('webhooks')->cascadeOnDelete();
            $table->string('event', 64);
            $table->json('payload')->nullable();
            $table->unsignedSmallInteger('response_status')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->string('status', 16)->default('pending'); // pending|delivered|failed
            $table->timestamp('next_retry_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['webhook_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_deliveries');
        Schema::dropIfExists('webhooks');
        Schema::dropIfExists('tenant_api_keys');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('branch_users');
        Schema::dropIfExists('branches');
        Schema::dropIfExists('tenant_domains');
    }
};