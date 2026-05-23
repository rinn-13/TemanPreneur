<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IssueReportResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'issue_report_id',
        'admin_id',
        'response_message',
        'action_type',
        'action_details',
        'status',
        'notified_at',
        'completed_at',
    ];

    protected $casts = [
        'action_details' => 'array',
        'notified_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Relasi: Response milik satu laporan
     */
    public function issueReport()
    {
        return $this->belongsTo(IssueReport::class);
    }

    /**
     * Relasi: Response dibuat oleh satu admin
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
