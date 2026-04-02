<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WithdrawalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'petugas_id',
        'nominal',
        'catatan',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'nominal' => 'integer',
        'approved_at' => 'datetime',
    ];

    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
