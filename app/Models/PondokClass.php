<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PondokClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'homeroom_teacher',
        'sort_order',
        'uses_monthly_exam',
        'uses_semester_exam',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'uses_monthly_exam' => 'boolean',
        'uses_semester_exam' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function students()
    {
        return $this->hasMany(User::class, 'kelas', 'name')->where('role', 'santri');
    }
}
