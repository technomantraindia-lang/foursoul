<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AssignmentStatus;
use App\Enums\CouponType;
use App\Enums\CourseStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\EntitlementStatus;
use App\Enums\LessonType;
use App\Enums\LiveSessionStatus;
use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use App\Enums\PageStatus;
use App\Enums\PlanBillingType;
use App\Enums\PlanCourseAccessType;
use App\Enums\PlanStatus;
use App\Enums\PurchaseMode;
use App\Enums\QuestionDifficulty;
use App\Enums\QuestionStatus;
use App\Enums\QuestionType;
use App\Enums\QuizStatus;
use App\Enums\SessionProvider;
use App\Enums\SessionType;
use App\Enums\SubscriptionStatus;
use App\Enums\TenantStatus;
use App\Enums\UserStatus;
use App\Models\Admin;
use App\Models\Assignment;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Branch;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseSection;
use App\Models\Enrollment;
use App\Models\Entitlement;
use App\Models\LiveSession;
use App\Models\Notification;
use App\Models\Page;
use App\Models\Question;
use App\Models\QuestionCategory;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\TenantDomain;
use App\Models\TenantFeature;
use App\Models\TenantSubscription;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Minimal local-only demo data for functional QA.
 * Never runs outside the local environment.
 */
class LocalDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command?->warn('LocalDemoSeeder skipped: not a local environment.');

            return;
        }

        $this->call(LocalAdminSeeder::class);

        $admin = Admin::query()->where('email', 'admin@foursouls.test')->firstOrFail();
        $student = $this->student('student@foursouls.test', 'Demo Student');
        $locked = $this->student('locked@foursouls.test', 'Locked Student');

        $course = Course::query()->firstOrCreate(
            ['slug' => 'local-demo-course'],
            [
                'uuid' => (string) Str::uuid(),
                'title' => 'Local Demo Course',
                'description' => 'Minimal published course for local QA.',
                'status' => CourseStatus::Published,
                'purchase_mode' => PurchaseMode::Both,
                'published_at' => now()->subDay(),
                'created_by' => $admin->id,
            ],
        );

        $section = CourseSection::query()->firstOrCreate(
            ['course_id' => $course->id, 'title' => 'Getting Started'],
            [
                'uuid' => (string) Str::uuid(),
                'description' => 'Intro section',
                'position' => 1,
            ],
        );

        $preview = CourseLesson::query()->firstOrCreate(
            ['course_section_id' => $section->id, 'title' => 'Free Preview Lesson'],
            [
                'uuid' => (string) Str::uuid(),
                'type' => LessonType::Text,
                'content' => 'This preview is visible without purchase.',
                'position' => 1,
                'is_free_preview' => true,
            ],
        );

        $paid = CourseLesson::query()->firstOrCreate(
            ['course_section_id' => $section->id, 'title' => 'Paid Lesson'],
            [
                'uuid' => (string) Str::uuid(),
                'type' => LessonType::Text,
                'content' => 'This lesson requires an active entitlement.',
                'position' => 2,
                'is_free_preview' => false,
            ],
        );

        $plan = SubscriptionPlan::query()->firstOrCreate(
            ['slug' => 'local-demo-plan'],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Local Demo Plan',
                'short_description' => 'QA monthly plan',
                'description' => 'Local-only subscription used to exercise checkout and access.',
                'status' => PlanStatus::Active,
                'billing_type' => PlanBillingType::Monthly,
                'duration_days' => 30,
                'trial_enabled' => false,
                'course_access_type' => PlanCourseAccessType::SelectedCourses,
                'price_aed' => 99,
                'price_usd' => 29,
                'price_inr' => 1999,
                'is_featured' => true,
                'sort_order' => 1,
                'live_sessions_enabled' => true,
                'published_at' => now()->subDay(),
                'created_by' => $admin->id,
            ],
        );

        $plan->courses()->syncWithoutDetaching([$course->id]);

        $subscription = UserSubscription::query()->firstOrCreate(
            [
                'user_id' => $student->id,
                'subscription_plan_id' => $plan->id,
                'activation_source' => 'local_demo',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'status' => SubscriptionStatus::Active,
                'currency' => 'AED',
                'unit_price' => 99,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => 99,
                'started_at' => now()->subDay(),
                'current_period_start' => now()->subDay(),
                'current_period_end' => now()->addDays(29),
                'expires_at' => now()->addDays(29),
            ],
        );

        $entitlement = Entitlement::query()->firstOrCreate(
            [
                'user_id' => $student->id,
                'course_id' => $course->id,
                'subscription_id' => $subscription->id,
            ],
            [
                'uuid' => (string) Str::uuid(),
                'status' => EntitlementStatus::Active,
                'granted_at' => now()->subDay(),
                'expires_at' => $subscription->expires_at,
                'source' => 'local_demo',
                'source_reference' => (string) $subscription->id,
            ],
        );

        Enrollment::query()->firstOrCreate(
            [
                'user_id' => $student->id,
                'course_id' => $course->id,
            ],
            [
                'uuid' => (string) Str::uuid(),
                'entitlement_id' => $entitlement->id,
                'status' => EnrollmentStatus::Active,
                'source' => 'subscription',
                'enrolled_at' => now()->subDay(),
            ],
        );

        Coupon::query()->firstOrCreate(
            ['code' => 'DEMO10'],
            [
                'uuid' => (string) Str::uuid(),
                'type' => CouponType::Percentage,
                'value' => 10,
                'usage_limit' => 50,
                'usage_limit_per_user' => 3,
                'used_count' => 0,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonth(),
                'status' => 'active',
            ],
        );

        Page::query()->firstOrCreate(
            ['slug' => 'home'],
            [
                'uuid' => (string) Str::uuid(),
                'title' => 'Home',
                'seo_title' => 'FourSouls Local Home',
                'seo_description' => 'Local CMS homepage used for QA.',
                'layout' => 'default',
                'visibility' => 'public',
                'status' => PageStatus::Published,
                'published_at' => now()->subDay(),
                'content' => [
                    'sections' => [[
                        'title' => 'Welcome',
                        'blocks' => [[
                            'type' => 'hero',
                            'data' => [
                                'heading' => 'FourSouls Local QA',
                                'subheading' => 'Published CMS homepage for local testing.',
                            ],
                        ]],
                    ]],
                ],
                'created_by_admin_id' => $admin->id,
            ],
        );

        $blogCategory = BlogCategory::query()->firstOrCreate(
            ['slug' => 'local-updates'],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Local Updates',
                'description' => 'QA blog category',
                'sort_order' => 1,
            ],
        );

        BlogPost::query()->firstOrCreate(
            ['slug' => 'local-welcome'],
            [
                'uuid' => (string) Str::uuid(),
                'blog_category_id' => $blogCategory->id,
                'author_admin_id' => $admin->id,
                'title' => 'Local Welcome Post',
                'excerpt' => 'A published local blog post for QA.',
                'content' => '<p>This post exists so the public blog index and show pages have content.</p>',
                'status' => 'published',
                'published_at' => now()->subDay(),
                'reading_time_minutes' => 1,
                'seo_title' => 'Local Welcome Post',
                'seo_description' => 'Local QA blog post',
            ],
        );

        $questionCategory = QuestionCategory::query()->firstOrCreate(
            ['slug' => 'local-demo'],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Local Demo',
                'description' => 'QA question category',
            ],
        );

        $question = Question::query()->firstOrCreate(
            ['question_text' => 'What is 2 + 2?'],
            [
                'uuid' => (string) Str::uuid(),
                'question_category_id' => $questionCategory->id,
                'type' => QuestionType::SingleChoice,
                'explanation' => 'Basic arithmetic.',
                'marks' => 10,
                'negative_marks' => 0,
                'difficulty' => QuestionDifficulty::Easy,
                'status' => QuestionStatus::Published,
            ],
        );

        if ($question->options()->count() === 0) {
            QuestionOption::query()->create([
                'uuid' => (string) Str::uuid(),
                'question_id' => $question->id,
                'option_text' => '3',
                'is_correct' => false,
                'sort_order' => 1,
            ]);
            QuestionOption::query()->create([
                'uuid' => (string) Str::uuid(),
                'question_id' => $question->id,
                'option_text' => '4',
                'is_correct' => true,
                'sort_order' => 2,
            ]);
        }

        $quiz = Quiz::query()->firstOrCreate(
            ['course_id' => $course->id, 'title' => 'Local Demo Quiz'],
            [
                'uuid' => (string) Str::uuid(),
                'course_lesson_id' => $paid->id,
                'description' => 'One-question quiz for local QA.',
                'passing_marks' => 10,
                'total_marks' => 10,
                'max_attempts' => 5,
                'duration_minutes' => 15,
                'status' => QuizStatus::Published,
                'published_at' => now()->subDay(),
            ],
        );

        $quiz->questions()->syncWithoutDetaching([
            $question->id => ['sort_order' => 1, 'marks_override' => 10],
        ]);

        Assignment::query()->firstOrCreate(
            ['course_id' => $course->id, 'title' => 'Local Demo Assignment'],
            [
                'uuid' => (string) Str::uuid(),
                'course_lesson_id' => $paid->id,
                'description' => 'Write a short reflection.',
                'instructions' => 'Submit a few sentences of text.',
                'due_at' => now()->addWeek(),
                'max_marks' => 100,
                'allow_resubmission' => true,
                'max_attempts' => 5,
                'accepted_file_types' => ['pdf', 'txt'],
                'max_file_size_kb' => 2048,
                'status' => AssignmentStatus::Published,
            ],
        );

        LiveSession::query()->firstOrCreate(
            ['course_id' => $course->id, 'title' => 'Local Demo Live Session'],
            [
                'uuid' => (string) Str::uuid(),
                'lesson_id' => $paid->id,
                'instructor_id' => $admin->id,
                'description' => 'Scheduled session for local join/attendance QA.',
                'session_type' => SessionType::LiveClass,
                'provider' => SessionProvider::Custom,
                'meeting_url' => 'https://example.test/local-live',
                'timezone' => 'Asia/Dubai',
                'scheduled_start_at' => now()->addHour(),
                'scheduled_end_at' => now()->addHours(2),
                'waiting_room_enabled' => false,
                'replay_enabled' => true,
                'status' => LiveSessionStatus::Scheduled,
            ],
        );

        Notification::query()->firstOrCreate(
            [
                'user_id' => $student->id,
                'event_key' => 'local_demo_welcome',
                'channel' => NotificationChannel::InApp,
            ],
            [
                'uuid' => (string) Str::uuid(),
                'title' => 'Welcome to local QA',
                'subject' => 'Welcome',
                'body' => 'This in-app notification exists so the notification center has a row.',
                'status' => NotificationStatus::Sent,
                'sent_at' => now()->subHour(),
                'idempotency_key' => 'local-demo-welcome-'.$student->id,
            ],
        );

        $tenant = Tenant::query()->firstOrCreate(
            ['slug' => 'foursouls-local'],
            [
                'uuid' => (string) Str::uuid(),
                'company_name' => 'FourSouls Local Tenant',
                'status' => TenantStatus::Active,
                'owner_user_id' => $student->id,
                'brand_name' => 'FourSouls Local',
                'timezone' => 'Asia/Dubai',
                'country_code' => 'AE',
                'currency' => 'AED',
                'language' => 'en',
                'user_limit' => 50,
                'branch_limit' => 5,
                'storage_limit_bytes' => 1073741824,
            ],
        );

        TenantFeature::query()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'feature' => 'courses'],
            ['enabled' => true],
        );

        TenantDomain::query()->firstOrCreate(
            ['domain' => 'local.foursouls.test'],
            [
                'tenant_id' => $tenant->id,
                'is_primary' => true,
                'ssl_status' => 'pending',
                'dns_status' => 'pending',
            ],
        );

        Branch::query()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'HQ'],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Headquarters',
                'address' => 'Local QA branch',
                'user_limit' => 25,
            ],
        );

        TenantSubscription::query()->firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'subscription_plan_id' => $plan->id,
            ],
            [
                'uuid' => (string) Str::uuid(),
                'status' => 'trialing',
                'currency' => 'AED',
                'unit_price' => 99,
                'is_trial' => true,
                'trial_ends_at' => now()->addDays(14),
                'current_period_start' => now(),
                'current_period_end' => now()->addDays(14),
                'expires_at' => now()->addDays(14),
            ],
        );

        $this->command?->info('Local demo data ready.');
        $this->command?->info('Student: student@foursouls.test / password (verified).');
        $this->command?->info('Locked (no entitlement): locked@foursouls.test / password.');
        $this->command?->info('Preview lesson UUID: '.$preview->uuid);
        $this->command?->info('Paid lesson UUID: '.$paid->uuid);
        $this->command?->info('Unused locked user id: '.$locked->id);
    }

    private function student(string $email, string $name): User
    {
        $user = User::query()->firstOrNew(['email' => $email]);

        if (! $user->exists) {
            $user->uuid = (string) Str::uuid();
            $user->referral_code = User::generateReferralCode();
        }

        $user->full_name = $name;
        $user->syncNameParts();
        $user->password = 'password';
        $user->preferred_language = 'en';
        $user->preferred_currency = 'AED';
        $user->timezone = 'Asia/Dubai';
        $user->save();

        $user->forceFill([
            'email_verified_at' => now(),
            'status' => UserStatus::Active,
            'terms_accepted_at' => now(),
            'privacy_accepted_at' => now(),
        ])->save();

        return $user->fresh();
    }
}
