<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Admin;
use App\Models\Course;
use Illuminate\Contracts\Auth\Authenticatable;

class CoursePolicy
{
    public function viewAny(Authenticatable $user): bool
    {
        return $user instanceof Admin && $user->can('courses.view');
    }

    public function view(Authenticatable $user, Course $course): bool
    {
        return $user instanceof Admin && $user->can('courses.view');
    }

    public function create(Authenticatable $user): bool
    {
        return $user instanceof Admin && $user->can('courses.manage');
    }

    public function update(Authenticatable $user, Course $course): bool
    {
        return $user instanceof Admin && $user->can('courses.manage');
    }

    public function delete(Authenticatable $user, Course $course): bool
    {
        return $user instanceof Admin && $user->can('courses.manage');
    }

    public function restore(Authenticatable $user, Course $course): bool
    {
        return $user instanceof Admin && $user->can('courses.manage');
    }

    public function forceDelete(Authenticatable $user, Course $course): bool
    {
        return $user instanceof Admin && $user->can('courses.manage');
    }
}