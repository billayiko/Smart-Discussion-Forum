<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TopicSuggestionDismissal extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'course_topic_id',
        'first_dismissed_at',
        'last_dismissed_at',
    ];

    protected $casts = [
        'first_dismissed_at' => 'datetime',
        'last_dismissed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(CourseTopic::class, 'course_topic_id');
    }

    /**
     * Whether a full week has passed since this topic was first ignored,
     * after which it stops being re-suggested for good.
     */
    public function isPermanent(): bool
    {
        return $this->first_dismissed_at->diffInDays(now()) >= 7;
    }
}
