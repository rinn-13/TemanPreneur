<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IssueReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'buyer_id',
        'subject',
        'type',
        'description',
        'status',
        'attachments',
        'admin_id',
        'admin_response',
        'admin_response_at',
        'resolution_type',
        'refund_status',
        'refund_amount',
        'seller_contacted',
        'seller_contacted_at',
        'resolved_by_buyer_at',
        'admin_locked',
    ];

    protected $casts = [
        'status' => 'string',
        'attachments' => 'array',
        'seller_contacted' => 'boolean',
        'admin_locked' => 'boolean',
        'admin_response_at' => 'datetime',
        'seller_contacted_at' => 'datetime',
        'resolved_by_buyer_at' => 'datetime',
    ];

    /**
     * Relasi: Laporan milik satu order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relasi: Laporan dibuat oleh satu buyer (user)
     */
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Relasi: Admin yang menangani laporan
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Relasi: Laporan memiliki banyak response dari admin
     */
    public function responses()
    {
        return $this->hasMany(IssueReportResponse::class);
    }

    /**
     * Relasi: Response terbaru
     */
    public function latestResponse()
    {
        return $this->hasOne(IssueReportResponse::class)->latest();
    }
}