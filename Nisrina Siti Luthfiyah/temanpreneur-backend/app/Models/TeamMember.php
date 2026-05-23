<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'user_id',
        'role',
    ];

    /**
     * Relasi: Anggota tim milik satu bisnis
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Relasi: Anggota tim adalah seorang user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}