<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Page;
use Illuminate\Auth\Access\HandlesAuthorization;

class PagePolicy
{
    use HandlesAuthorization;

    public function viewAny($user): bool
    {
        return $user instanceof Admin;
    }

    public function view(Admin $admin, Page $page): bool
    {
        return true;
    }

    public function create(Admin $admin): bool
    {
        return true;
    }

    public function update(Admin $admin, Page $page): bool
    {
        return true;
    }

    public function delete(Admin $admin, Page $page): bool
    {
        return true;
    }

    public function restore(Admin $admin, Page $page): bool
    {
        return true;
    }

    public function forceDelete(Admin $admin, Page $page): bool
    {
        return true;
    }
}