<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Audit trail: dashboard views, report generations, export downloads.
        Schema::create('analytics_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            // auditable admin/user identity (polymorphic to support both guards)
            $table->nullableMorphs('actor');
            $table->string('action'); // dashboard_viewed | report_generated | export_downloaded
            $table->string('subject'); // e.g. revenue_report, super_admin_dashboard
            $table->json('parameters')->nullable(); // filters used
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['actor_type', 'actor_id', 'action']);
            $table->index(['action', 'created_at']);
        });

        // Scheduled reports architecture (daily/weekly/monthly, email delivery later).
        Schema::create('scheduled_reports', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('admin_id')->constrained('admins')->cascadeOnDelete();
            $table->string('name');
            $table->string('report_key'); // matches ReportDefinition key
            $table->string('format', 10)->default('csv'); // csv|excel|pdf
            $table->string('frequency', 20); // daily|weekly|monthly
            $table->json('filters')->nullable();
            $table->json('recipients')->nullable(); // email addresses (delivery implemented later)
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_generated_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'next_run_at']);
        });

        // Export history for abuse prevention + audit.
        Schema::create('report_exports', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('admin_id')->constrained('admins')->cascadeOnDelete();
            $table->string('report_key');
            $table->string('format', 10);
            $table->json('filters')->nullable();
            $table->unsignedInteger('row_count')->default(0);
            $table->timestamps();

            $table->index(['admin_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_exports');
        Schema::dropIfExists('scheduled_reports');
        Schema::dropIfExists('analytics_audit_logs');
    }
};