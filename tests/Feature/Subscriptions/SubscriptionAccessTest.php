<?php

namespace Tests\Feature\Subscriptions;

use App\Enums\CourseStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\EntitlementStatus;
use App\Enums\PlanBillingType;
use App\Enums\PlanCourseAccessType;
use App\Enums\PlanStatus;
use App\Enums\PurchaseMode;
use App\Enums\SubscriptionStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Entitlement;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionAccessTest extends TestCase
{
    use RefreshDatabase;

    private function activePlan(array $attributes = []): SubscriptionPlan
    {
        return SubscriptionPlan::create(array_merge([
            'name' => 'Pro Plan',
            'status' => PlanStatus::Active,
            'billing_type' => PlanBillingType::Monthly,
            'course_access_type' => PlanCourseAccessType::AllCourses,
            'duration_days' => 30,
            'price_aed' => 999,
        ], $attributes));
    }

    private function subscriptionCourse(): Course
    {
        return Course::create([
            'title' => 'Subscription Course',
            'purchase_mode' => PurchaseMode::SubscriptionOnly,
            'status' => CourseStatus::Published,
            'published_at' => now(),
        ]);
    }

    public function test_user_without_subscription_cannot_access_paid_course(): void
    {
        $user = User::factory()->create();
        $course = $this->subscriptionCourse();

        $this->assertFalse(app(\App\Services\SubscriptionService::class)->userHasCourseAccess($user, $course));
    }

    public function test_active_subscription_grants_entitlement_and_enrollment_and_access(): void
    {
        $user = User::factory()->create();
        $plan = $this->activePlan();
        $course = $this->subscriptionCourse();

        $subscription = UserSubscription::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => SubscriptionStatus::Pending,
            'currency' => 'AED',
            'unit_price' => 999,
            'total_amount' => 999,
            'started_at' => now(),
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
            'expires_at' => now()->addMonth(),
        ]);

        app(\App\Services\SubscriptionService::class)->activate($subscription);

        $this->assertTrue($subscription->fresh()->status === SubscriptionStatus::Active);
        $this->assertSame(1, Entitlement::where('user_id', $user->id)->where('course_id', $course->id)->where('status', EntitlementStatus::Active)->count());
        $this->assertSame(1, Enrollment::where('user_id', $user->id)->where('course_id', $course->id)->where('status', EnrollmentStatus::Active)->count());
        $this->assertTrue(app(\App\Services\SubscriptionService::class)->userHasCourseAccess($user, $course));
    }

    public function test_expired_subscription_locks_access_but_preserves_history(): void
    {
        $user = User::factory()->create();
        $plan = $this->activePlan();
        $course = $this->subscriptionCourse();

        $subscription = UserSubscription::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => SubscriptionStatus::Active,
            'currency' => 'AED',
            'unit_price' => 999,
            'total_amount' => 999,
            'expires_at' => now()->subDay(),
        ]);
        app(\App\Services\SubscriptionService::class)->activate($subscription);

        app(\App\Services\SubscriptionService::class)->expire($subscription);

        $service = app(\App\Services\SubscriptionService::class);
        $this->assertFalse($service->userHasCourseAccess($user, $course));

        // History preserved, not deleted.
        $this->assertSame(1, Entitlement::where('user_id', $user->id)->where('course_id', $course->id)->where('status', EntitlementStatus::Expired)->count());
        $this->assertSame(1, Enrollment::where('user_id', $user->id)->where('course_id', $course->id)->where('status', EnrollmentStatus::Locked)->count());

        // Renewal restores access without resetting history rows.
        $renewal = UserSubscription::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => SubscriptionStatus::Pending,
            'currency' => 'AED',
            'unit_price' => 999,
            'total_amount' => 999,
            'expires_at' => now()->addMonth(),
        ]);
        app(\App\Services\SubscriptionService::class)->activate($renewal);

        $this->assertTrue($service->userHasCourseAccess($user, $course));
        $this->assertLessThanOrEqual(1, Enrollment::where('user_id', $user->id)->where('course_id', $course->id)->where('status', EnrollmentStatus::Active)->count());
    }

    public function test_standalone_only_course_is_not_granted_by_plan(): void
    {
        $user = User::factory()->create();
        $plan = $this->activePlan();
        $course = Course::create([
            'title' => 'Standalone Course',
            'purchase_mode' => PurchaseMode::StandaloneOnly,
            'status' => CourseStatus::Published,
            'published_at' => now(),
        ]);

        $subscription = UserSubscription::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => SubscriptionStatus::Pending,
            'currency' => 'AED',
            'unit_price' => 999,
            'total_amount' => 999,
            'expires_at' => now()->addMonth(),
        ]);
        app(\App\Services\SubscriptionService::class)->activate($subscription);

        $this->assertFalse(app(\App\Services\SubscriptionService::class)->userHasCourseAccess($user, $course));
    }
}