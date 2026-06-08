<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'santri_id',
        'kamar',
        'attendance_date',
        'status',
        'method',
        'notes',
        'recorded_by',
        'recorded_at',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'recorded_at' => 'datetime',
    ];

    public function santri()
    {
        return $this->belongsTo(User::class, 'santri_id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
