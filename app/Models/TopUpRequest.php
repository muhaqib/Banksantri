<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TopUpRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'santri_id',
        'nominal',
        'bukti_pembayaran',
        'status',
        'admin_note',
        'admin_id',
        'verified_at',
    ];

    protected $casts = [
        'nominal' => 'decimal:0',
        'verified_at' => 'datetime',
    ];

    /**
     * Get the santri who requested the top-up.
     */
    public function santri(): BelongsTo
    {
        return $this->belongsTo(User::class, 'santri_id');
    }

    /**
     * Get the admin who verified the top-up.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Check if the request is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the request is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if the request is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Get formatted nominal.
     */
    public function getFormattedNominalAttribute(): string
    {
        return 'Rp ' . number_format($this->nominal, 0, ',', '.');
    }

    /**
     * Get status badge text.
     */
    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Menunggu Verifikasi',
            'approved' => 'Terverifikasi',
            'rejected' => 'Ditolak',
            default => 'Unknown',
        };
    }
}
