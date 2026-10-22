<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_progress', static function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained('course_lessons')->cascadeOnDelete();
            $table->foreignId('enrollment_id')->nullable()->constrained()->nullOnDelete();

            // string status backed by App\Enums\LessonProgressStatus
            $table->string('status', 32)->default('not_started')->index();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('last_accessed_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->unsignedInteger('watch_seconds')->default(0);
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->decimal('progress_percentage', 5, 2)->default(0);
            $table->unsignedInteger('last_position_seconds')->default(0);

            $table->string('completion_source')->nullable(); // manual | watch_percentage | watch_seconds
            $table->json('metadata')->nullable();

            $table->timestamps();

            // One progress row per user + lesson + enrollment context.
            $table->unique(['user_id', 'lesson_id', 'enrollment_id'], 'lesson_progress_unique');
            $table->index(['user_id', 'course_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_progress');
    }
};