<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_attendance', static function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignId('live_session_id')->constrained('live_sessions')->cascadeOnDelete();
            $table->foreignId('enrollment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->timestamp('joined_at')->nullable();
            $table->timestamp('left_at')->nullable();

            $table->unsignedInteger('duration_minutes')->default(0);
            $table->decimal('attendance_percentage', 5, 2)->default(0);
            $table->boolean('present')->default(false);

            $table->json('metadata')->nullable();

            $table->timestamps();

            // One attendance row per user per session (join/leave updates it).
            $table->unique(['live_session_id', 'user_id'], 'session_attendance_unique');
            $table->index(['user_id', 'joined_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_attendance');
    }
};