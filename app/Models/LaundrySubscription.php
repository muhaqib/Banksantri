<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LaundrySubscription extends Model
{
    protected $fillable = [
        'santri_id',
        'created_by',
        'month',
        'year',
        'monthly_fee',
        'quota_kg',
        'used_kg',
        'status',
        'notes',
    ];

    protected $casts = [
        'month' => 'integer',
        'year' => 'integer',
        'monthly_fee' => 'integer',
        'quota_kg' => 'decimal:2',
        'used_kg' => 'decimal:2',
    ];

    public function santri(): BelongsTo
    {
        return $this->belongsTo(User::class, 'santri_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(LaundryTransaction::class);
    }

    public function getRemainingKgAttribute(): float
    {
        return max(0, (float) $this->quota_kg - (float) $this->used_kg);
    }

    public function canUse(float $weightKg): bool
    {
        return $this->status === 'active' && $weightKg > 0 && $this->remaining_kg >= $weightKg;
    }
}
