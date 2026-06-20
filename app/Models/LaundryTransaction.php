<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaundryTransaction extends Model
{
    protected $fillable = [
        'santri_id',
        'petugas_id',
        'laundry_subscription_id',
        'transaction_id',
        'payment_type',
        'payment_method',
        'laundry_date',
        'weight_kg',
        'price_per_kg',
        'total_price',
        'total_clothes',
        'clothes_detail',
        'notes',
    ];

    protected $casts = [
        'laundry_date' => 'date',
        'payment_method' => 'string',
        'weight_kg' => 'decimal:2',
        'price_per_kg' => 'integer',
        'total_price' => 'integer',
        'total_clothes' => 'integer',
        'clothes_detail' => 'array',
    ];

    public function santri(): BelongsTo
    {
        return $this->belongsTo(User::class, 'santri_id');
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(LaundrySubscription::class, 'laundry_subscription_id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
