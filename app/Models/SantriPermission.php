<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SantriPermission extends Model
{
    use HasFactory;

    public const APPROVERS = [
        "Mudirul Ma'had",
        'Ustadz Muhtadi',
    ];

    protected $fillable = [
        'permission_number',
        'santri_id',
        'kamar',
        'start_date',
        'end_date',
        'reason',
        'notes',
        'approved_by',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function santri()
    {
        return $this->belongsTo(User::class, 'santri_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActiveOn($query, $date)
    {
        return $query->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date);
    }

    public function getIsActiveAttribute(): bool
    {
        return today()->betweenIncluded($this->start_date, $this->end_date);
    }
}
