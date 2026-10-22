<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('company_name');
            $table->string('slug')->unique();
            $table->string('status', 32)->default('active')->index();
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('brand_name')->nullable();
            $table->string('timezone', 64)->default('UTC');
            $table->string('country_code', 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->string('language', 8)->default('en');

            // Limits (PART 5)
            $table->unsignedInteger('user_limit')->nullable();
            $table->unsignedInteger('admin_limit')->nullable();
            $table->unsignedInteger('course_limit')->nullable();
            $table->unsignedBigInteger('storage_limit_bytes')->nullable();
            $table->unsignedInteger('branch_limit')->nullable();
            $table->unsignedInteger('live_session_limit')->nullable();
            $table->unsignedInteger('certificate_limit')->nullable();
            $table->unsignedBigInteger('email_limit_monthly')->nullable();

            // Aggregated usage
            $table->unsignedBigInteger('storage_used_bytes')->default(0);
            $table->json('metadata')->nullable();

            $table->timestamp('suspended_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'slug']);
        });

        Schema::create('tenant_settings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('group', 64)->index(); // brand, contact, analytics, smtp, storage, social, theme
            $table->string('key', 128);
            $table->text('value')->nullable();
            $table->string('type', 32)->default('string'); // string|boolean|integer|json
            $table->timestamps();

            $table->unique(['tenant_id', 'group', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_settings');
        Schema::dropIfExists('tenants');
    }
};