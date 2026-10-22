<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_sessions', static function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained('course_lessons')->nullOnDelete();
            $table->foreignId('instructor_id')->nullable()->constrained('admins')->nullOnDelete();

            $table->string('title');
            $table->text('description')->nullable();

            // string enums backed by App\Enums\SessionType / SessionProvider / LiveSessionStatus
            $table->string('session_type', 32)->default('live_class');
            $table->string('provider', 32)->default('zoom');
            $table->string('status', 32)->default('draft')->index();

            $table->string('meeting_id')->nullable();
            $table->string('meeting_password')->nullable();
            $table->text('meeting_url')->nullable();
            $table->text('host_url')->nullable();

            // Replay metadata — never exposed publicly; gated server-side.
            $table->text('replay_url')->nullable();
            $table->text('recording_url')->nullable();

            $table->string('timezone', 64)->default('UTC');

            $table->timestamp('scheduled_start_at');
            $table->timestamp('scheduled_end_at')->useCurrent();
            $table->timestamp('actual_start_at')->nullable();
            $table->timestamp('actual_end_at')->nullable();

            $table->unsignedInteger('max_participants')->nullable();
            $table->boolean('waiting_room_enabled')->default(true);
            $table->boolean('recording_enabled')->default(false);
            $table->boolean('replay_enabled')->default(true);

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index(['course_id', 'status']);
            $table->index(['instructor_id', 'scheduled_start_at']);
            $table->index('scheduled_start_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_sessions');
    }
};
