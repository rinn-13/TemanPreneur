<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'is_used',
        'quantity',
        'used_by',
        'used_at',
        'expires_at',
        'description',
        'created_by',
    ];

    protected $casts = [
        'is_used' => 'boolean',
        'used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Relasi: Access code digunakan oleh satu user (buyer)
     */
    public function usedBy()
    {
        return $this->belongsTo(User::class, 'used_by');
    }

    /**
     * Relasi: Access code dibuat oleh admin
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check apakah access code masih valid
     */
    public function isValid(): bool
    {
        if ($this->is_used) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }
}
