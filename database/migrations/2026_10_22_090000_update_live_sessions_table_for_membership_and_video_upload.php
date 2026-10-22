<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Make course_id nullable so sessions can exist without courses
        DB::statement('ALTER TABLE `live_sessions` MODIFY `course_id` BIGINT UNSIGNED NULL');

        Schema::table('live_sessions', function (Blueprint $table) {
            $table->string('video_type')->default('live_meeting')->after('session_type');
            $table->text('video_url')->nullable()->after('meeting_url');
            $table->string('video_file')->nullable()->after('video_url');
            $table->json('allowed_plans')->nullable()->after('video_file');
            $table->string('min_plan_tier')->default('all')->after('allowed_plans');
            $table->boolean('is_locked_for_non_members')->default(true)->after('min_plan_tier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('live_sessions', function (Blueprint $table) {
            $table->dropColumn([
                'video_type',
                'video_url',
                'video_file',
                'allowed_plans',
                'min_plan_tier',
                'is_locked_for_non_members',
            ]);
        });
    }
};
