<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'category_id',
        'category',
        'name',
        'description',
        'price',
        'stock',
        'image',
        'images', // Multiple images support
        'total_sold',
        'status',
    ];

    protected $casts = [
        'images' => 'array', // Multiple images as JSON array
    ];

    /**
     * Relasi: Produk milik satu bisnis
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Relasi: Produk milik satu kategori
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Scope: Filter by category slug
     */
    public function scopeCategory($query, $slug)
    {
        return $query->where('category', $slug);
    }

    /**
     * Scope: Filter by price range
     */
    public function scopePriceBetween($query, $min, $max)
    {
        return $query->whereBetween('price', [$min ?? 0, $max ?? PHP_INT_MAX]);
    }

    /**
     * Scope: Sort by newest
     */
    public function scopeTerbaru($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope: Sort by cheapest
     */
    public function scopeTermurah($query)
    {
        return $query->orderBy('price', 'asc');
    }

    /**
     * Relasi: Produk memiliki banyak order
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Relasi: Mendapatkan review melalui order
     * (untuk menghitung rating)
     */
    public function reviews()
    {
        return $this->hasManyThrough(Review::class, Order::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }}