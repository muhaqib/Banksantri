<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WahaMessageLog extends Model
{
    use HasFactory;

    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'schedule_id',
        'teacher_name',
        'target_id',
        'session',
        'message_content',
        'status',
        'http_status',
        'response_body',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status === self::STATUS_SUCCESS ? 'Berhasil' : 'Gagal';
    }
}
