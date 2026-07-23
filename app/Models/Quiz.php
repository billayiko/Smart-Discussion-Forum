<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\CarbonInterface;

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
        'questions_finalized_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'proctored' => 'boolean',
        'questions_finalized_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(CourseTopic::class, 'course_topic_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    /**
     * Whether enough questions have been added to save/finalize this quiz.
     */
    public function hasEnoughQuestions(): bool
    {
        return $this->questions()->count() >= $this->total_questions;
    }

    /**
     * Whether the lecturer has saved the quiz's questions (reached the
     * required count and confirmed them).
     */
    public function isFinalized(): bool
    {
        return $this->questions_finalized_at !== null;
    }

    public function markQuestionsFinalized(): void
    {
        $this->update(['questions_finalized_at' => now()]);
    }

    public function endsAt(): ?CarbonInterface
    {
        return $this->scheduled_at?->copy()->addMinutes($this->duration_minutes);
    }

    /**
     * Whether the quiz window is currently open for students to take it.
     */
    public function isLive(): bool
    {
        if (! $this->scheduled_at || $this->status === 'draft' || $this->status === 'closed' || ! $this->isFinalized()) {
            return false;
        }

        $endsAt = $this->endsAt();
        $now = now();

        return $now->greaterThanOrEqualTo($this->scheduled_at) && ($endsAt === null || $now->lessThan($endsAt));
    }

    public function hasStarted(): bool
    {
        return $this->scheduled_at && now()->greaterThanOrEqualTo($this->scheduled_at);
    }
}
