<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Media;
use Illuminate\Auth\Access\HandlesAuthorization;

class MediaPolicy
{
    use HandlesAuthorization;

    public function viewAny($user): bool
    {
        return $user instanceof Admin;
    }

    public function view(Admin $admin, Media $media): bool
    {
        return true;
    }

    public function create(Admin $admin): bool
    {
        return true;
    }

    public function update(Admin $admin, Media $media): bool
    {
        return true;
    }

    public function delete(Admin $admin, Media $media): bool
    {
        return true;
    }
}