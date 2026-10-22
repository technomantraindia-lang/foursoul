<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Admin;
use App\Models\CourseLesson;

class CourseLessonPolicy
{
    public function viewAny($user): bool
    {
        return $user instanceof Admin && $user->can('courses.view');
    }

    public function view(Admin $admin, CourseLesson $lesson): bool
    {
        return $admin->can('courses.view');
    }

    public function create(Admin $admin): bool
    {
        return $admin->can('courses.manage');
    }

    public function update(Admin $admin, CourseLesson $lesson): bool
    {
        return $admin->can('courses.manage');
    }

    public function delete(Admin $admin, CourseLesson $lesson): bool
    {
        return $admin->can('courses.manage');
    }

    public function reorder(Admin $admin): bool
    {
        return $admin->can('courses.manage');
    }

    public function restore(Admin $admin, CourseLesson $lesson): bool
    {
        return $admin->can('courses.manage');
    }

    public function forceDelete(Admin $admin, CourseLesson $lesson): bool
    {
        return $admin->can('courses.manage');
    }
}