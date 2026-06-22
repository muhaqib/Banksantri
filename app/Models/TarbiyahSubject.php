<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TarbiyahSubject extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_level',
        'name',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function grades()
    {
        return $this->hasMany(TarbiyahGrade::class, 'subject_id');
    }
}
