<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrestasiSantri extends Model
{
    use HasFactory;

    protected $fillable = [
        'santri_id',
        'nama_kitab',
        'kategori',
        'keterangan',
        'status',
        'nilai',
        'skor',
        'tanggal_selesai',
        'bulan_tahun_selesai',
        'ustadz_pembimbing',
        'foto_kitab',
        'catatan_ustadz',
        'poin',
        'tags',
    ];

    protected $casts = [
        'tanggal_selesai' => 'date',
        'skor' => 'integer',
        'poin' => 'integer',
    ];

    /**
     * Get the santri that owns this prestasi.
     */
    public function santri()
    {
        return $this->belongsTo(User::class, 'santri_id');
    }

    /**
     * Get status text attribute.
     */
    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            'belum_dihafal' => 'Belum Dihafal',
            'sedang_dihafal' => 'Sedang Dihafal',
            'telah_dihafalkan' => 'Telah Dihafalkan',
            default => 'Unknown',
        };
    }

    /**
     * Check if prestasi is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'telah_dihafalkan';
    }

    /**
     * Check if prestasi is in progress.
     */
    public function isInProgress(): bool
    {
        return $this->status === 'sedang_dihafal';
    }

    /**
     * Get tags as array.
     */
    public function getTagsArrayAttribute(): array
    {
        return $this->tags ? explode(',', $this->tags) : [];
    }

    /**
     * Scope for completed prestasi.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'telah_dihafalkan');
    }

    /**
     * Scope for in-progress prestasi.
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'sedang_dihafal');
    }
}
