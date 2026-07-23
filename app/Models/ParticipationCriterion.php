<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParticipationCriterion extends Model
{
    protected $table = 'participation_criteria';

    protected $fillable = [
        'user_id',
        'points_per_question',
        'points_per_answer',
        'points_per_like_received',
        'target_points',
    ];

    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function forLecturer(User $lecturer): self
    {
        return static::firstOrCreate(['user_id' => $lecturer->id]);
    }

    public function rawPointsFor(int $questions, int $answers, int $likesReceived): int
    {
        return $questions * $this->points_per_question
            + $answers * $this->points_per_answer
            + $likesReceived * $this->points_per_like_received;
    }

    /**
     * Raw points as a percentage of the lecturer's target, capped at 100.
     */
    public function scorePercentageFor(int $rawPoints): int
    {
        if ($this->target_points <= 0) {
            return 0;
        }

        return (int) min(100, round($rawPoints / $this->target_points * 100));
    }
}
