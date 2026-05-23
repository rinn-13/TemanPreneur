<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'rating',
        'comment',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    /**
     * Relasi: Review milik satu order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relasi: Review untuk satu produk (melalui OrderItem)
     * Order bisa memiliki multiple items, kita ambil yang pertama
     */
    public function product()
    {
        return $this->hasOneThrough(
            Product::class,
            OrderItem::class,
            'order_id', // Foreign key on OrderItem table
            'id',       // Foreign key on Product table
            'order_id', // Local key on Review table
            'product_id' // Local key on OrderItem table
        );
    }

    /**
     * Relasi: Review untuk seller (business)
     * Ambil seller dari produk order
     */
    public function seller()
    {
        return $this->hasManyThrough(
            User::class,
            Product::class,
            'id',
            'id',
            function ($query) {
                $query->select('product_id')
                    ->from('order_items')
                    ->whereColumn('order_items.order_id', 'reviews.order_id')
                    ->first();
            },
            'user_id'
        );
    }

    /**
     * Get seller business through the order's products
     */
    public function business()
    {
        return $this->hasOneThrough(
            Business::class,
            Product::class,
            'id',
            'user_id',
            function ($query) {
                // Get first product from order
                return $query->selectRaw('products.user_id')
                    ->join('order_items', 'products.id', '=', 'order_items.product_id')
                    ->where('order_items.order_id', $this->order_id)
                    ->first();
            },
            'id'
        );
    }

    /**
     * Get the buyer of this review
     */
    public function buyer()
    {
        return $this->hasOneThrough(
            User::class,
            Order::class,
            'id',
            'id',
            'order_id',
            'buyer_id'
        );
    }
}