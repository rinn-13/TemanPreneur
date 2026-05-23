<?php
namespace App\Policies;

use App\Models\User;
use App\Models\Product;

class ProductPolicy
{
    public function create(User $user)
    {
        $business = $user->business;
        if (!$business || !$business->is_verified || !in_array($business->status, ['approved', 'active'], true)) {
            return false;
        }
        if (!$business->is_premium && $business->products()->count() >= 2) {
            return false; // batasi 2 produk untuk seller reguler
        }
        return true;
    }

    public function update(User $user, Product $product)
    {
        return $user->id === $product->business->user_id;
    }

    public function delete(User $user, Product $product)
    {
        return $user->id === $product->business->user_id;
    }
}