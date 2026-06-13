<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DashboardContent extends Model
{
    use HasFactory;

    public const TYPES = [
        'announcement' => 'Pengumuman',
        'news' => 'Berita Pondok',
        'todo' => 'To Do List',
    ];

    public const PRIORITIES = [
        'normal' => 'Normal',
        'important' => 'Penting',
        'urgent' => 'Mendesak',
    ];

    protected $fillable = [
        'created_by',
        'type',
        'title',
        'summary',
        'thumbnail_url',
        'content',
        'priority',
        'event_date',
        'due_date',
        'assign_to_all',
        'is_published',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'due_date' => 'date',
            'assign_to_all' => 'boolean',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(DashboardContentAssignment::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }
}
