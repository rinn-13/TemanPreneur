<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Business;
use App\Models\OrderGroup;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_id',
        'business_id',
        'order_group_id',
        'product_id', // legacy, deprecated
        'quantity', // legacy, deprecated
        'total_price', // legacy, deprecated
        'status',
        'payment_method',
        'shipping_cost',
        'total_amount',
        'shipping_address',
        'shipping_phone',
        'shipping_name',
        'buyer_notes',
        'cancellation_reason',
        'cancelled_at',
        'cancelled_by',
    ];

    /** Status yang masih boleh dibatalkan oleh buyer */
    public const CANCELLABLE_STATUSES = ['pending', 'diproses', 'dikemas'];

    /** Status yang tidak boleh dibatalkan */
    public const NON_CANCELLABLE_STATUSES = ['diantarkan', 'selesai', 'dibatalkan'];

    protected function casts(): array
    {
        return [
            'total_price' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'cancelled_at' => 'datetime',
        ];
    }

    /**
     * Relasi: Order milik satu pembeli (user)
     */
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Relasi: Order milik satu bisnis
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Relasi: Order bagian dari satu grup checkout
     */
    public function orderGroup()
    {
        return $this->belongsTo(OrderGroup::class);
    }

    /**
     * Relasi BARU: Order memiliki banyak item
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Relasi: Order untuk satu produk (legacy, untuk backward compatibility)
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Relasi: Order memiliki banyak tracking status
     */
    public function trackings()
    {
        return $this->hasMany(OrderTracking::class);
    }

    /**
     * Relasi: Order memiliki satu review
     */
    public function review()
    {
        return $this->hasOne(Review::class);
    }

    /**
     * Relasi: Order memiliki banyak laporan issue
     */
    public function issueReports()
    {
        return $this->hasMany(IssueReport::class);
    }

    /**
     * Relasi: Order memiliki banyak pesan chat
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}