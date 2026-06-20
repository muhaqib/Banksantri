<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SantriHealthRecord extends Model
{
    use HasFactory;

    public const STATUSES = [
        'sehat' => 'Sehat',
        'sakit' => 'Sakit',
        'sembuh' => 'Sembuh',
        'dirawat' => 'Dirawat',
    ];

    protected $fillable = [
        'santri_id',
        'created_by',
        'checkup_date',
        'title',
        'status',
        'location',
        'weight_kg',
        'height_cm',
        'blood_pressure',
        'temperature_c',
        'complaint',
        'treatment',
        'notes',
    ];

    protected $casts = [
        'checkup_date' => 'date',
        'weight_kg' => 'decimal:2',
        'height_cm' => 'decimal:2',
        'temperature_c' => 'decimal:1',
    ];

    public function santri()
    {
        return $this->belongsTo(User::class, 'santri_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }
}
