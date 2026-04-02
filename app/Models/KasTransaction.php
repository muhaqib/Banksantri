<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KasTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'jenis',
        'nominal',
        'sumber_dana',
        'keperluan',
        'keterangan',
        'saldo_sebelum',
        'saldo_setelah',
        'created_by',
    ];

    protected $casts = [
        'nominal' => 'integer',
        'saldo_sebelum' => 'integer',
        'saldo_setelah' => 'integer',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
