<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CourseStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\EntitlementStatus;
use App\Enums\PlanCourseAccessType;
use App\Enums\PurchaseMode;
use App\Enums\SubscriptionStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Entitlement;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\DB;

/**
 * Access-control layer between PLAN → SUBSCRIPTION → ENTITLEMENT →
 * ENROLLMENT → COURSE ACCESS.
 *
 * The payment phase will call activate() after a verified payment and
 * renew() on renewal. Nothing here touches payment gateways.
 */
class SubscriptionService
{
    /**
     * Activate a subscription: stamp period/expiry, generate entitlements
     * for every eligible course the plan grants, and create/restore
     * enrollments (never resetting existing progress).
     */
    public function activate(UserSubscription $subscription): UserSubscription
    {
        return DB::transaction(function () use ($subscription) {
            $plan = $subscription->plan;

            $now = now();

            $subscription->forceFill([
                'status' => SubscriptionStatus::Active,
                'started_at' => $subscription->started_at ?? $now,
                'current_period_start' => $now,
                'current_period_end' => $this->periodEnd($plan, $now),
                'expires_at' => $this->expiryFor($plan, $now),
            ])->save();

            foreach ($this->eligibleCourses($plan) as $course) {
                $entitlement = Entitlement::updateOrCreate(
                    [
                        'user_id' => $subscription->user_id,
                        'course_id' => $course->id,
                        'subscription_id' => $subscription->id,
                    ],
                    [
                        'status' => EntitlementStatus::Active,
                        'granted_at' => $now,
                        'revoked_at' => null,
                        'expires_at' => $subscription->expires_at,
                        'source' => 'subscription_activation',
                        'source_reference' => (string) $subscription->id,
                    ],
                );

                $this->grantEnrollment($subscription->user, $course, $subscription, $entitlement);
            }

            return $subscription->refresh();
        });
    }

    /**
     * Renewal restores entitlements + enrollments without resetting progress.
     */
    public function renew(UserSubscription $subscription): UserSubscription
    {
        return DB::transaction(function () use ($subscription) {
            $now = now();
            $base = $subscription->expires_at && $subscription->expires_at->isFuture()
                ? $subscription->expires_at
                : $now;

            $subscription->forceFill([
                'status' => SubscriptionStatus::Active,
                'cancelled_at' => null,
                'ended_at' => null,
                'current_period_start' => $now,
                'current_period_end' => $this->periodEnd($subscription->plan, $now),
                'expires_at' => $this->expiryFor($subscription->plan, $base),
            ])->save();

            foreach ($this->eligibleCourses($subscription->plan) as $course) {
                Entitlement::where([
                    'user_id' => $subscription->user_id,
                    'course_id' => $course->id,
                    'subscription_id' => $subscription->id,
                ])->update([
                    'status' => EntitlementStatus::Active,
                    'revoked_at' => null,
                    'expires_at' => $subscription->expires_at,
                ]);

                Enrollment::query()
                    ->where('user_id', $subscription->user_id)
                    ->where('course_id', $course->id)
                    ->where('status', EnrollmentStatus::Locked)
                    ->update(['status' => EnrollmentStatus::Active]);
            }

            return $subscription->refresh();
        });
    }

    /**
     * Expiry locks paid course access. History and progress are preserved —
     * enrollments become Locked, never deleted.
     */
    public function expire(UserSubscription $subscription): void
    {
        DB::transaction(function () use ($subscription) {
            $subscription->forceFill([
                'status' => SubscriptionStatus::Expired,
                'ended_at' => now(),
            ])->save();

            Entitlement::where('subscription_id', $subscription->id)
                ->where('status', EntitlementStatus::Active)
                ->update(['status' => EntitlementStatus::Expired]);

            $courseIds = Entitlement::where('subscription_id', $subscription->id)
                ->pluck('course_id');

            Enrollment::query()
                ->where('user_id', $subscription->user_id)
                ->whereIn('course_id', $courseIds)
                ->where('status', EnrollmentStatus::Active)
                ->update(['status' => EnrollmentStatus::Locked]);
        });
    }

    /**
     * Backend source of truth: does this user currently have valid access
     * to this course? Never trusts frontend state or request values.
     */
    public function userHasCourseAccess(User $user, Course $course): bool
    {
        $status = $course->status ?? null;
        if (! $status instanceof CourseStatus || ! $status->isPubliclyVisible()) {
            return false;
        }

        // Free courses are open; subscription/standalone require proof below.
        if ($course->purchase_mode === PurchaseMode::Free) {
            return true;
        }

        return Entitlement::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->where('status', EntitlementStatus::Active)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->exists();
    }

    /* -----------------------------------------------------------------
     | Internals
     * ----------------------------------------------------------------- */

    private function eligibleCourses(SubscriptionPlan $plan): iterable
    {
        if ($plan->course_access_type === PlanCourseAccessType::AllCourses) {
            return Course::published()
                ->whereIn('purchase_mode', [PurchaseMode::SubscriptionOnly, PurchaseMode::Both])
                ->get();
        }

        return $plan->courses()
            ->published()
            ->whereIn('purchase_mode', [PurchaseMode::SubscriptionOnly, PurchaseMode::Both])
            ->get();
    }

    private function grantEnrollment(
        User $user,
        Course $course,
        UserSubscription $subscription,
        Entitlement $entitlement,
    ): void {
        Enrollment::updateOrCreate(
            [
                'user_id' => $user->id,
                'course_id' => $course->id,
            ],
            [
                'entitlement_id' => $entitlement->id,
                'status' => EnrollmentStatus::Active,
                'source' => 'subscription',
                'enrolled_at' => now(),
            ],
        );
    }

    private function periodEnd(SubscriptionPlan $plan, $from)
    {
        return $plan->billing_type->isLifetime() ? null : $this->expiryFor($plan, $from);
    }

    private function expiryFor(SubscriptionPlan $plan, $from)
    {
        if ($plan->billing_type->isLifetime()) {
            return null;
        }

        return (clone $from)->addDays(max(1, (int) $plan->duration_days));
    }
}