<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SantriViolation extends Model
{
    use HasFactory;

    protected $fillable = [
        'santri_id',
        'created_by',
        'jenis_pelanggaran',
        'waktu',
        'pengurangan_point',
        'keterangan',
    ];

    protected $casts = [
        'waktu' => 'datetime',
        'pengurangan_point' => 'integer',
    ];

    public function santri()
    {
        return $this->belongsTo(User::class, 'santri_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
