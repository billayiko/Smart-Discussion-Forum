<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseTopic extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'lecturer_id',
    ];

    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lecturer_id');
    }

    public function subscribers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_topic_subscriptions', 'course_topic_id', 'user_id')->withTimestamps();
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}
