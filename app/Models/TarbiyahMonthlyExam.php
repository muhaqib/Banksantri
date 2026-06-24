<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TarbiyahMonthlyExam extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'exam_date',
        'created_by',
    ];

    protected $casts = [
        'exam_date' => 'date',
    ];

    public function grades()
    {
        return $this->hasMany(TarbiyahMonthlyGrade::class, 'monthly_exam_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
