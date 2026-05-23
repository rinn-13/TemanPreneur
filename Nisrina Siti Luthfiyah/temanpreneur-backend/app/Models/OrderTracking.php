<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderTracking extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'status',
        'updated_by',
    ];

    /**
     * Relasi: Tracking milik satu order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relasi: Tracking diupdate oleh user (admin atau seller)
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}