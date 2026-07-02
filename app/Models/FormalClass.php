<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormalClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'next_class_id',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function nextClass()
    {
        return $this->belongsTo(self::class, 'next_class_id');
    }

    public function previousClasses()
    {
        return $this->hasMany(self::class, 'next_class_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
