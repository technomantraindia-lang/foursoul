<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Course lessons table (Phase 2 — Course Structure).
 *
 * Leaf content unit inside a section. `type` discriminates between
 * video / text / quiz lessons; media and duration are optional so
 * text lessons stay lightweight.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_lessons', function (Blueprint $table): void {
            $table->id();

            $table->uuid('uuid')->unique();

            $table->foreignId('course_section_id')
                ->constrained('course_sections')
                ->cascadeOnDelete();

            $table->string('title');

            // Discriminates lesson rendering: video | text | quiz
            $table->string('type', 20)->default('video')->index();

            $table->text('content')->nullable();
            $table->string('video_url')->nullable();

            // Duration in minutes (null = not applicable)
            $table->unsignedInteger('duration_minutes')->nullable();

            // Manual ordering within the parent section
            $table->unsignedInteger('position')->default(0)->index();

            $table->boolean('is_free_preview')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_lessons');
    }
};