<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'sender_id',
        'receiver_id',
        'message',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    /**
     * Relasi: Pesan milik satu order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relasi: Pengirim pesan
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Relasi: Penerima pesan
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}