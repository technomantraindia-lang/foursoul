<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\BlogPost;
use Illuminate\Auth\Access\HandlesAuthorization;

class BlogPostPolicy
{
    use HandlesAuthorization;

    public function viewAny($user): bool
    {
        return $user instanceof Admin;
    }

    public function view(Admin $admin, BlogPost $post): bool
    {
        return true;
    }

    public function create(Admin $admin): bool
    {
        return true;
    }

    public function update(Admin $admin, BlogPost $post): bool
    {
        return true;
    }

    public function delete(Admin $admin, BlogPost $post): bool
    {
        return true;
    }

    public function restore(Admin $admin, BlogPost $post): bool
    {
        return true;
    }

    public function forceDelete(Admin $admin, BlogPost $post): bool
    {
        return true;
    }
}