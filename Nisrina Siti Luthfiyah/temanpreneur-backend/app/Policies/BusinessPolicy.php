<?php
namespace App\Policies;

use App\Models\User;
use App\Models\Business;

class BusinessPolicy
{
    public function viewAny(User $user)
    {
        // ✅ Use hasRole() for safety
        return $user->hasRole('admin');
    }

    public function view(User $user, Business $business)
    {
        return $user->id === $business->user_id || $user->hasRole('admin');
    }

    public function create(User $user)
    {
        return true; // Checked in controller
    }

    public function update(User $user, Business $business)
    {
        return $user->id === $business->user_id;
    }

    public function delete(User $user, Business $business)
    {
        return $user->hasRole('admin');
    }

    public function verify(User $user, ?Business $business = null)
    {
        return $user->hasRole('admin');
    }

    public function upgrade(User $user, ?Business $business = null)
    {
        return $user->hasRole('admin');
    }

    public function approve(User $user, ?Business $business = null)
    {
        return $user->hasRole('admin');
    }

    public function reject(User $user, ?Business $business = null)
    {
        return $user->hasRole('admin');
    }
}