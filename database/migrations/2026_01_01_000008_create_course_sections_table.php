<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Course sections table (Phase 2 — Course Structure).
 *
 * Ordered grouping of lessons inside a course (e.g. "Module 1:
 * Foundations"). Position is an integer sort order managed by the
 * Filament relation manager; reordering never touches other courses.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_sections', function (Blueprint $table): void {
            $table->id();

            $table->uuid('uuid')->unique();

            $table->foreignId('course_id')
                ->constrained('courses')
                ->cascadeOnDelete();

            $table->string('title');
            $table->text('description')->nullable();

            // Manual ordering within the parent course
            $table->unsignedInteger('position')->default(0)->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_sections');
    }
};