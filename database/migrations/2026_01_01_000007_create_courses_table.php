<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Courses table (Phase 2 — Course Structure).
 *
 * Root entity of the learning catalogue. Authored by an admin,
 * versioned through status (draft/published/archived) and soft-deleted
 * so historical enrollments keep their referential integrity.
 *
 * Nothing pre-existing is modified or destroyed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table): void {
            $table->id();

            // Identity
            $table->uuid('uuid')->unique();

            // Content
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            // Lifecycle
            $table->string('status', 20)->default('draft')->index();
            $table->timestamp('published_at')->nullable();

            // Authorship
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('admins')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};