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
        'returned_at',
        'reason',
        'notes',
        'approved_by',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'returned_at' => 'datetime',
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
            ->where(function ($q) use ($date) {
                $q->whereNull('returned_at')
                  ->whereDate('end_date', '>=', $date)
                  ->orWhere(function ($q2) use ($date) {
                      $q2->whereNotNull('returned_at')
                         ->whereDate('returned_at', '>=', $date);
                  });
            });
    }

    public function getIsActiveAttribute(): bool
    {
        $end = $this->returned_at ?? $this->end_date;
        return today()->betweenIncluded($this->start_date, $end);
    }
}
