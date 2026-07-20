<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_topic_id',
        'title',
        'subject',
        'total_questions',
        'scheduled_at',
        'duration_minutes',
        'status',
        'proctored',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'proctored' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(CourseTopic::class, 'course_topic_id');
    }
}
