<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TarbiyahMonthlyGrade extends Model
{
    use HasFactory;

    public const SUBJECTS = ['Nahwu', 'Shorof', 'Fiqih'];

    protected $fillable = [
        'monthly_exam_id',
        'santri_id',
        'class_level',
        'subject',
        'score',
        'recorded_by',
    ];

    protected $casts = [
        'score' => 'decimal:2',
    ];

    public function exam()
    {
        return $this->belongsTo(TarbiyahMonthlyExam::class, 'monthly_exam_id');
    }

    public function santri()
    {
        return $this->belongsTo(User::class, 'santri_id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
