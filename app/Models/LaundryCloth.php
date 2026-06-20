<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaundryCloth extends Model
{
    protected $table = 'laundry_clothes';

    protected $fillable = [
        'key',
        'label',
        'icon',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
