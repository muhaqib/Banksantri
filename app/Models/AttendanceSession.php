<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceSession extends Model
{
    protected $fillable = [
        'title',
        'start_time',
        'end_time',
        'completed_at',
        'created_by',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function records()
    {
        return $this->hasMany(AttendanceSessionRecord::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isCompleted()
    {
        return !is_null($this->completed_at);
    }
}
