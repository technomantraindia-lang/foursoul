<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Course;
use App\Models\User;
use App\Services\SubscriptionService;

/**
 * Customer-facing course access authorization.
 *
 * The backend is the single source of truth: a User may view a paid
 * course only when a valid entitlement/enrollment/subscription proves it.
 * Frontend state, URLs and request values are never trusted.
 */
class CourseAccessPolicy
{
    public function __construct(
        protected SubscriptionService $subscriptions,
    ) {}

    /**
     * Whether the user may view this course's content.
     */
    public function view(User $user, Course $course): bool
    {
        // Free courses are open to any authenticated user.
        if ($course->purchase_mode === \App\Enums\PurchaseMode::Free) {
            return true;
        }

        return $this->subscriptions->userHasCourseAccess($user, $course);
    }
}