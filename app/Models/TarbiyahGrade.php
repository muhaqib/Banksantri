<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TarbiyahGrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'santri_id',
        'subject_id',
        'class_level',
        'semester',
        'academic_year',
        'score',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'semester' => 'integer',
        'score' => 'decimal:2',
    ];

    public function santri()
    {
        return $this->belongsTo(User::class, 'santri_id');
    }

    public function subject()
    {
        return $this->belongsTo(TarbiyahSubject::class, 'subject_id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
