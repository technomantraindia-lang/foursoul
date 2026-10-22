<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Admin;
use App\Models\Quiz;
use App\Models\User;

class QuizPolicy
{
    public function viewAny($user): bool
    {
        return $user instanceof Admin;
    }

    public function view(Admin $admin, Quiz $quiz): bool
    {
        return true;
    }

    public function create(Admin $admin): bool
    {
        return true;
    }

    public function update(Admin $admin, Quiz $quiz): bool
    {
        return true;
    }

    public function delete(Admin $admin, Quiz $quiz): bool
    {
        return true;
    }

    /**
     * A user may take a quiz only when it is published and they have course access.
     */
    public function take(User $user, Quiz $quiz): bool
    {
        if ($quiz->status->value !== 'published') {
            return false;
        }

        return app(\App\Services\SubscriptionService::class)->userHasCourseAccess($user, $quiz->course);
    }
}