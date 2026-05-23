<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'title',
        'slug',
        'content',
        'excerpt',
        'image',
        'category',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    /**
     * Relasi: Blog milik satu bisnis
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}