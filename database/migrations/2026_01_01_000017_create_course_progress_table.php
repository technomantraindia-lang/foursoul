<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_progress', static function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('enrollment_id')->nullable()->constrained()->nullOnDelete();

            // string status backed by App\Enums\CourseProgressStatus
            $table->string('status', 32)->default('not_started')->index();

            $table->unsignedInteger('total_lessons')->default(0);
            $table->unsignedInteger('completed_lessons')->default(0);
            $table->decimal('progress_percentage', 5, 2)->default(0);

            $table->timestamp('started_at')->nullable();
            $table->timestamp('last_accessed_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();

            // One progress row per user + course + enrollment context.
            $table->unique(['user_id', 'course_id', 'enrollment_id'], 'course_progress_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_progress');
    }
};