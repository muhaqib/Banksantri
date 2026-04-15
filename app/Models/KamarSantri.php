<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KamarSantri extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'kamar',
    ];

    protected $casts = [
        'kamar' => 'string',
    ];

    public const KAMAR_LIST = [
        'kamar_1',
        'kamar_2',
        'kamar_3',
        'kamar_4',
        'kamar_5',
        'kamar_6',
        'kamar_7',
        'kamar_8',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
