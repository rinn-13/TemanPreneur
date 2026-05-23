<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'is_read',
        'related_id',  // Added for linking to business/order/etc
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    /**
     * Relasi: Notifikasi milik satu user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}