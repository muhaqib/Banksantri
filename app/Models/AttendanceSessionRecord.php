<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceSessionRecord extends Model
{
    protected $fillable = [
        'attendance_session_id',
        'santri_id',
        'status',
        'scanned_at',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(AttendanceSession::class, 'attendance_session_id');
    }

    public function santri()
    {
        return $this->belongsTo(User::class, 'santri_id');
    }
}
