<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    public const RECIPIENT_PERSONAL = 'personal';
    public const RECIPIENT_GROUP = 'group';

    public const DAYS = [
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu',
        'Sunday' => 'Minggu',
    ];

    protected $fillable = [
        'teacher_name',
        'recipient_type',
        'target_id',
        'day_of_week',
        'send_time',
        'message_content',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'send_time' => 'datetime:H:i:s',
    ];

    public function getDayLabelAttribute(): string
    {
        return self::DAYS[$this->day_of_week] ?? $this->day_of_week;
    }

    public function getTargetLabelAttribute(): string
    {
        if ($this->recipient_type === self::RECIPIENT_PERSONAL) {
            return str_replace('@c.us', '', $this->target_id);
        }

        return $this->target_id;
    }
}
