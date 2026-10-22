<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('notification_template_id')->nullable()->constrained('notification_templates')->nullOnDelete();
            $table->string('channel'); // NotificationChannel enum value
            $table->string('event_key')->nullable(); // event that produced this notification
            $table->string('subject')->nullable(); // email subject
            $table->string('title')->nullable(); // push/in-app title
            $table->longText('body');
            $table->json('variables')->nullable();
            $table->string('status')->default('queued'); // NotificationStatus
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->timestamp('last_error_at')->nullable();
            $table->text('last_error')->nullable();
            $table->string('idempotency_key')->unique()->nullable(); // duplicate-send prevention
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at']);
            $table->index(['status', 'scheduled_at']);
            $table->index(['channel', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};