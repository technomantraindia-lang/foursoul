<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CourseStatus;
use App\Enums\PurchaseMode;
use App\Models\Admin;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseSection;
use App\Models\LiveSession;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PlatformFullDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Admin::where('email', 'admin@foursouls.test')->first() ?? Admin::first();
        $adminId = $admin ? $admin->id : 1;

        // 1. Blog Categories & Blog Posts
        $wellnessCat = BlogCategory::firstOrCreate(
            ['slug' => 'wellness'],
            ['uuid' => (string) Str::uuid(), 'name' => 'Wellness & Yoga', 'description' => 'Mindfulness and yoga articles', 'sort_order' => 1]
        );

        $updatesCat = BlogCategory::firstOrCreate(
            ['slug' => 'updates'],
            ['uuid' => (string) Str::uuid(), 'name' => 'Community Updates', 'description' => 'FourSouls announcements', 'sort_order' => 2]
        );

        $blogs = [
            [
                'title' => '5 Simple Yoga Practices for a Peaceful Morning',
                'slug' => '5-simple-yoga-practices-for-a-peaceful-morning',
                'blog_category_id' => $wellnessCat->id,
                'author_admin_id' => $adminId,
                'excerpt' => 'Start your day with simple yoga practices that help awaken your body, calm your mind, and bring positive energy into your morning.',
                'content' => '<p>Cultivating a gentle morning routine grounds the nervous system and creates mental clarity for the day ahead. Incorporate mindful diaphragmatic breath, gentle spinal twists, and restorative postures.</p>',
                'featured_image_path' => 'https://www.shutterstock.com/image-vector/toogle-slider-260nw-1110907754.jpg',
                'status' => 'published',
                'published_at' => now()->subDays(2),
                'reading_time_minutes' => 4,
            ],
            [
                'title' => 'The Power of Conscious Breathwork (Pranayama)',
                'slug' => 'the-power-of-conscious-breathwork',
                'blog_category_id' => $wellnessCat->id,
                'author_admin_id' => $adminId,
                'excerpt' => 'How intentional rhythmic breathwork rebalances the sympathetic nervous system and enhances deep restorative sleep.',
                'content' => '<p>Conscious breathing connects the physical body with emotional equanimity. Practicing Anulom Vilom and Bhramari for 10 minutes every evening restores nervous system balance.</p>',
                'featured_image_path' => 'https://images.unsplash.com/photo-1506126613408-eca07ce68773?auto=format&fit=crop&w=1200&q=80',
                'status' => 'published',
                'published_at' => now()->subDay(),
                'reading_time_minutes' => 5,
            ],
            [
                'title' => 'tessssssed',
                'slug' => 'tessssssedtest',
                'blog_category_id' => $updatesCat->id,
                'author_admin_id' => $adminId,
                'excerpt' => 'Reflections on daily practice, inner alignment, and mindful living at FourSouls.',
                'content' => '<p>Dedicated practice transforms physical vitality into conscious presence. Embrace each step of your inward journey.</p>',
                'featured_image_path' => 'blog-images/01M5HFN5CD5SXJBV18TZESHMAV.jpg',
                'status' => 'published',
                'published_at' => now(),
                'reading_time_minutes' => 1,
            ],
            [
                'title' => 'Local Welcome Post',
                'slug' => 'local-welcome',
                'blog_category_id' => $updatesCat->id,
                'author_admin_id' => $adminId,
                'excerpt' => 'Welcome to FourSouls. Explore our sanctuary for holistic movement, mindful breath, and inner peace.',
                'content' => '<p>FourSouls is designed to offer accessible, transformative practices that meet you wherever you are on your path.</p>',
                'featured_image_path' => 'https://images.unsplash.com/photo-1545205597-3d9d02c29597?auto=format&fit=crop&w=1200&q=80',
                'status' => 'published',
                'published_at' => now()->subDays(5),
                'reading_time_minutes' => 2,
            ],
        ];

        foreach ($blogs as $b) {
            BlogPost::firstOrCreate(
                ['slug' => $b['slug']],
                array_merge($b, ['uuid' => (string) Str::uuid()])
            );
        }

        // 2. Courses
        $courses = [
            [
                'title' => 'Yoga for Beginners',
                'slug' => 'yoga-for-beginners-21-day-foundation',
                'description' => 'Start your yoga journey with this beginner-friendly 21-day program. Learn basic yoga poses, breathing techniques, flexibility exercises, balance, and relaxation practices.',
                'status' => CourseStatus::Published,
                'purchase_mode' => PurchaseMode::Both,
                'requires_membership' => true,
                'created_by' => $adminId,
                'published_at' => now()->subDays(10),
            ],
            [
                'title' => 'Beginner Yoga & Mindfulness',
                'slug' => 'beginner-yoga-mindfulness',
                'description' => 'A beginner-friendly course designed to improve flexibility, balance, breathing, and mental calm through simple yoga postures and mindfulness practices.',
                'status' => CourseStatus::Published,
                'purchase_mode' => PurchaseMode::Both,
                'requires_membership' => true,
                'created_by' => $adminId,
                'published_at' => now()->subDays(8),
            ],
            [
                'title' => 'Meditation for Inner Peace',
                'slug' => 'meditation-for-inner-peace',
                'description' => 'Learn practical meditation and breathing techniques to reduce daily stress, improve focus, calm the mind, and develop a peaceful daily routine.',
                'status' => CourseStatus::Published,
                'purchase_mode' => PurchaseMode::Both,
                'requires_membership' => true,
                'created_by' => $adminId,
                'published_at' => now()->subDays(7),
            ],
            [
                'title' => 'Pranayama & Breathwork',
                'slug' => 'pranayama-breathwork',
                'description' => 'Explore traditional pranayama and guided breathing exercises designed to improve breath awareness, relaxation, concentration, and overall well-being.',
                'status' => CourseStatus::Published,
                'purchase_mode' => PurchaseMode::Both,
                'requires_membership' => true,
                'created_by' => $adminId,
                'published_at' => now()->subDays(6),
            ],
            [
                'title' => 'Holistic Wellness & Healthy Living',
                'slug' => 'holistic-wellness-healthy-living',
                'description' => 'A practical wellness course covering mindful habits, daily routines, relaxation, movement, and balanced lifestyle practices for healthier everyday living.',
                'status' => CourseStatus::Published,
                'purchase_mode' => PurchaseMode::Both,
                'requires_membership' => true,
                'created_by' => $adminId,
                'published_at' => now()->subDays(5),
            ],
        ];

        foreach ($courses as $c) {
            Course::firstOrCreate(
                ['slug' => $c['slug']],
                array_merge($c, ['uuid' => (string) Str::uuid()])
            );
        }

        // 3. Live Sessions
        $sessions = [
            [
                'title' => 'Sunrise Surya & Daily Pranayama Live Class',
                'description' => 'Morning energizing breathwork and Surya Namaskar variations for all skill levels.',
                'session_type' => 'live_class',
                'video_type' => 'live_meeting',
                'provider' => 'zoom',
                'status' => 'scheduled',
                'meeting_url' => 'https://zoom.us/j/1234567890',
                'min_plan_tier' => 'all',
                'is_locked_for_non_members' => true,
                'scheduled_start_at' => now()->addDays(1)->setTime(7, 0),
                'scheduled_end_at' => now()->addDays(1)->setTime(8, 0),
            ],
            [
                'title' => 'Therapeutic Spine & Joint Masterclass (YouTube HD)',
                'description' => 'Specialized mobility flow to relieve lower-back tension and strengthen core stabilizers.',
                'session_type' => 'live_class',
                'video_type' => 'youtube',
                'provider' => 'youtube',
                'status' => 'scheduled',
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'min_plan_tier' => 'transform',
                'is_locked_for_non_members' => true,
                'scheduled_start_at' => now()->addDays(2)->setTime(18, 0),
                'scheduled_end_at' => now()->addDays(2)->setTime(19, 0),
            ],
            [
                'title' => 'VIP Sacred Sound Bath & Guided Yoga Nidra',
                'description' => 'Exclusive restorative session with Tibetan singing bowls and deep subconscious relaxation.',
                'session_type' => 'live_class',
                'video_type' => 'live_meeting',
                'provider' => 'google_meet',
                'status' => 'scheduled',
                'meeting_url' => 'https://meet.google.com/abc-defg-hij',
                'min_plan_tier' => 'sadhana',
                'is_locked_for_non_members' => true,
                'scheduled_start_at' => now()->addDays(3)->setTime(20, 0),
                'scheduled_end_at' => now()->addDays(3)->setTime(21, 0),
            ],
        ];

        foreach ($sessions as $s) {
            LiveSession::firstOrCreate(
                ['title' => $s['title']],
                array_merge($s, ['uuid' => (string) Str::uuid(), 'timezone' => 'UTC'])
            );
        }
    }
}
