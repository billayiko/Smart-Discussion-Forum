<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModerationSetting extends Model
{
    protected $fillable = [
        'inactivity_threshold_days',
        'compliance_days',
        'blacklist_duration_days',
    ];

    public static function current(): self
    {
        return static::query()->first() ?? static::create([]);
    }
}
