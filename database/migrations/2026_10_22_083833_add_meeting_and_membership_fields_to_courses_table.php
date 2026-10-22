<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('meeting_platform')->nullable()->after('description');
            $table->text('meeting_link')->nullable()->after('meeting_platform');
            $table->string('meeting_passcode')->nullable()->after('meeting_link');
            $table->string('meeting_schedule')->nullable()->after('meeting_passcode');
            $table->boolean('requires_membership')->default(true)->after('meeting_schedule');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn([
                'meeting_platform',
                'meeting_link',
                'meeting_passcode',
                'meeting_schedule',
                'requires_membership',
            ]);
        });
    }
};
