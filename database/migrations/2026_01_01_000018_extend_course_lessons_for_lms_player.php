<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extends course_lessons for LMS player (Phase 3).
 *
 * Adds lesson release scheduling, sequential unlocking,
 * completion requirements and precise video duration tracking.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_lessons', function (Blueprint $table): void {
            // immediate | sequential | scheduled | plan_restricted
            $table->string('release_type', 20)->default('immediate')->after('is_free_preview');

            $table->timestamp('release_at')->nullable()->after('release_type');

            $table->boolean('requires_previous_completion')
                ->default(false)
                ->after('release_at');

            // manual | watch_percentage | watch_seconds
            $table->string('completion_requirement', 20)
                ->default('manual')
                ->after('requires_previous_completion');

            // Threshold for watch_percentage completion (default 90%)
            $table->unsignedTinyInteger('completion_threshold')
                ->default(90)
                ->after('completion_requirement');

            // Required watch seconds for watch_seconds completion
            $table->unsignedInteger('required_watch_seconds')
                ->nullable()
                ->after('completion_threshold');

            // Precise video duration in seconds (frontend-reported / canonical)
            $table->unsignedInteger('duration_seconds')->nullable()->after('duration_minutes');

            $table->boolean('is_published')->default(true)->after('duration_seconds');

            $table->index(['release_type', 'release_at']);
        });
    }

    public function down(): void
    {
        Schema::table('course_lessons', function (Blueprint $table): void {
            $table->dropIndex(['release_type', 'release_at']);
            $table->dropColumn([
                'release_type',
                'release_at',
                'requires_previous_completion',
                'completion_requirement',
                'completion_threshold',
                'required_watch_seconds',
                'duration_seconds',
                'is_published',
            ]);
        });
    }
};