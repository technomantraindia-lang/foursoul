<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_comparison_features', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('begin')->default('—');
            $table->string('commit')->default('—');
            $table->string('transform')->default('—');
            $table->string('sadhana')->default('—');
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_published')->default(true)->index();
            $table->timestamps();
        });

        $now = now();
        DB::table('membership_comparison_features')->insert([
            ['name' => 'Unlimited Daily Live Classes', 'begin' => 'Yes', 'commit' => 'Yes', 'transform' => 'Yes', 'sadhana' => 'Yes', 'sort_order' => 10, 'is_published' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'On-Demand Yoga & Pranayama Vault', 'begin' => 'Yes', 'commit' => 'Yes', 'transform' => 'Yes', 'sadhana' => 'Yes', 'sort_order' => 20, 'is_published' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Never Miss a Live Masterclass', 'begin' => 'Yes', 'commit' => 'Yes', 'transform' => 'Yes', 'sadhana' => 'Yes', 'sort_order' => 30, 'is_published' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Private Community Hub Access', 'begin' => 'Yes', 'commit' => 'Yes', 'transform' => 'Yes', 'sadhana' => 'Yes', 'sort_order' => 40, 'is_published' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => '90-Day Habit & Consistency Tracker', 'begin' => '—', 'commit' => 'Yes', 'transform' => 'Yes', 'sadhana' => 'Yes', 'sort_order' => 50, 'is_published' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Therapeutic Spine & Joint Masterclass', 'begin' => '—', 'commit' => '—', 'transform' => 'Yes', 'sadhana' => 'Yes', 'sort_order' => 60, 'is_published' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Plan-Pause Flexibility (Freeze Days)', 'begin' => '0 Days', 'commit' => '7 Days', 'transform' => '14 Days', 'sadhana' => 'Up to 45 Days', 'sort_order' => 70, 'is_published' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Weekend Sound Baths & Masterclasses', 'begin' => '—', 'commit' => '—', 'transform' => 'Standard', 'sadhana' => 'VIP Pass Included', 'sort_order' => 80, 'is_published' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Direct WhatsApp Teacher Hotline', 'begin' => 'Standard', 'commit' => 'Priority', 'transform' => 'Priority', 'sadhana' => 'Dedicated Master Concierge', 'sort_order' => 90, 'is_published' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_comparison_features');
    }
};
