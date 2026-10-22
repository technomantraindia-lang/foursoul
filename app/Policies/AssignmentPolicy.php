<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Admin;
use App\Models\Assignment;
use App\Models\User;

class AssignmentPolicy
{
    public function viewAny($user): bool
    {
        return $user instanceof Admin;
    }

    public function view(Admin $admin, Assignment $assignment): bool
    {
        return true;
    }

    public function create(Admin $admin): bool
    {
        return true;
    }

    public function update(Admin $admin, Assignment $assignment): bool
    {
        return true;
    }

    public function delete(Admin $admin, Assignment $assignment): bool
    {
        return true;
    }

    /**
     * A user may submit an assignment only when it is published and they have course access.
     */
    public function submit(User $user, Assignment $assignment): bool
    {
        if ($assignment->status->value !== 'published') {
            return false;
        }

        return app(\App\Services\SubscriptionService::class)->userHasCourseAccess($user, $assignment->course);
    }
}