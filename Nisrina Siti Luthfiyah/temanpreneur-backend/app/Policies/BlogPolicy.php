<?php

namespace App\Policies;

use App\Models\Blog;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BlogPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        // Admin bypass semua permission
        if ($user->hasRole('admin')) {
            return true;
        }

        // WAJIB return null kalau bukan admin
        return null;
    }

    public function viewAny(User $user): bool
    {
        return true; // public akses
    }

    public function create(User $user): bool
    {
        return $user->hasRole('seller')
            && $user->business
            && $user->business->is_verified;
    }

    public function update(User $user, Blog $blog): bool
    {
        return $user->business?->id === $blog->business_id;
    }

    public function delete(User $user, Blog $blog): bool
    {
        return $this->update($user, $blog);
    }
}