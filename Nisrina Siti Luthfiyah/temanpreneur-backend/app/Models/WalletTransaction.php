<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $fillable = [
        'wallet_id',
        'type',
        'amount',
        'description',
        'order_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Relasi: Transaksi milik satu wallet
     */
    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * Relasi: Transaksi terkait dengan order (opsional)
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
