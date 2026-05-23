<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Order;
use App\Models\User;

class OrderGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_code',
        'buyer_id',
        'payment_method',
        'shipping_name',
        'shipping_phone',
        'shipping_address',
        'total_items_price',
        'total_shipping_cost',
        'grand_total',
        'buyer_notes',
    ];

    protected $casts = [
        'total_items_price' => 'decimal:2',
        'total_shipping_cost' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
