<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'category',
        'phone',
        'address',
        'logo',
        'banner',
        'is_verified',
        'is_premium',
        'status',
        'rejection_reason',
        'processed_at',
        'theme_color',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'is_premium' => 'boolean',
        'processed_at' => 'datetime',
    ];

    /**
     * Relasi: bisnis dimiliki oleh satu user
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Alias relasi owner (opsional, supaya fleksibel)
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi: bisnis memiliki banyak produk
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Relasi: bisnis memiliki banyak blog
     */
    public function blogs()
    {
        return $this->hasMany(Blog::class);
    }

    /**
     * Relasi: bisnis memiliki anggota tim (premium)
     */
    public function teamMembers()
    {
        return $this->hasMany(TeamMember::class);
    }

    /**
     * Get all reviews for this business's products
     */
    public function reviews()
    {
        return Review::whereHas('product', function ($query) {
            $query->where('business_id', $this->id);
        });
    }

    /**
     * Get average rating for this business
     */
    public function getAverageRating()
    {
        return $this->reviews()
            ->average('rating') ?? 0;
    }

    /**
     * Get rating count for this business
     */
    public function getRatingCount()
    {
        return $this->reviews()->count();
    }

    /**
     * Get total sales for this business
     */
    public function getTotalSales()
    {
        return OrderItem::whereHas('product', function ($query) {
            $query->where('business_id', $this->id);
        })->count();
    }

    /**
     * Get total revenue for this business
     */
    public function getTotalRevenue()
    {
        return OrderItem::whereHas('product', function ($query) {
            $query->where('business_id', $this->id);
        })->sum('price');
    }

    /**
     * Get business stats including rating, sales, revenue
     */
    public function getStats()
    {
        return [
            'rating' => round($this->getAverageRating(), 1),
            'rating_count' => $this->getRatingCount(),
            'total_sales' => $this->getTotalSales(),
            'total_revenue' => $this->getTotalRevenue(),
        ];
    }
}