<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = [
        'user_id',
        'balance',
        'total_earned',
        'total_withdrawn',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'total_earned' => 'decimal:2',
        'total_withdrawn' => 'decimal:2',
    ];

    /**
     * Relasi: Wallet milik satu user (seller)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi: Wallet memiliki banyak transaksi
     */
    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    /**
     * Tambah saldo dari penjualan
     */
    public function addBalance(float $amount, string $description = null, $orderId = null)
    {
        $this->increment('balance', $amount);
        $this->increment('total_earned', $amount);

        // Record transaction
        WalletTransaction::create([
            'wallet_id' => $this->id,
            'type' => 'credit',
            'amount' => $amount,
            'description' => $description ?? 'Penjualan produk',
            'order_id' => $orderId,
        ]);

        return $this;
    }

    /**
     * Kurangi saldo untuk penarikan
     */
    public function withdrawBalance(float $amount, string $description = null)
    {
        if ($this->balance < $amount) {
            throw new \Exception('Saldo tidak cukup');
        }

        $this->decrement('balance', $amount);
        $this->increment('total_withdrawn', $amount);

        // Record transaction
        WalletTransaction::create([
            'wallet_id' => $this->id,
            'type' => 'debit',
            'amount' => $amount,
            'description' => $description ?? 'Penarikan saldo',
        ]);

        return $this;
    }
}
