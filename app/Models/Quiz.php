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

    /**
     * How long past the scheduled end a submission is still accepted, to
     * absorb network latency on the client's auto-submit-at-zero timer.
     */
    const SUBMISSION_GRACE_SECONDS = 30;

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
        'marks_confirmed_at' => 'datetime',
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

    /**
     * Whether the lecturer has confirmed this quiz's marks. Admin-facing
     * attempts/scores stay hidden until this is set, so a lecturer gets a
     * chance to review results before they're shared upward.
     */
    public function marksConfirmed(): bool
    {
        return $this->marks_confirmed_at !== null;
    }

    public function markMarksConfirmed(): void
    {
        $this->marks_confirmed_at = now();
        $this->save();
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
        return $this->isOpenAt(now());
    }

    /**
     * Whether a submission arriving right now should still be accepted.
     * Slightly more lenient than isLive() to absorb the network latency
     * between the client's countdown hitting zero and the request landing.
     */
    public function canStillSubmit(): bool
    {
        return $this->isOpenAt(now(), self::SUBMISSION_GRACE_SECONDS);
    }

    protected function isOpenAt(CarbonInterface $now, int $graceSeconds = 0): bool
    {
        if (! $this->scheduled_at || $this->status === 'draft' || $this->status === 'closed' || ! $this->isFinalized()) {
            return false;
        }

        $endsAt = $this->endsAt();

        return $now->greaterThanOrEqualTo($this->scheduled_at)
            && ($endsAt === null || $now->lessThan($endsAt->copy()->addSeconds($graceSeconds)));
    }

    public function hasStarted(): bool
    {
        return $this->scheduled_at && now()->greaterThanOrEqualTo($this->scheduled_at);
    }

    /**
     * Whether this quiz's targeted category of students includes the given
     * user: untargeted quizzes (no course_topic_id) are open to every
     * student; targeted ones are restricted to that topic's subscribers.
     */
    public function isTargetedAt(User $user): bool
    {
        if (! $this->course_topic_id) {
            return true;
        }

        return $user->subscribedTopics()->where('course_topics.id', $this->course_topic_id)->exists();
    }

    /**
     * The single quiz currently live and available to this student, if any.
     * Shared by the dashboard's immediate redirect and the site-wide
     * middleware that keeps the "pop up and interrupt" behavior working
     * from any page, not just the dashboard.
     */
    public static function liveFor(User $user): ?self
    {
        $attemptedQuizIds = QuizAttempt::where('user_id', $user->id)->pluck('quiz_id');

        return static::where('status', '!=', 'draft')
            ->whereNotNull('scheduled_at')
            ->whereNotNull('questions_finalized_at')
            ->where('scheduled_at', '<=', now())
            ->whereNotIn('id', $attemptedQuizIds)
            ->orderBy('scheduled_at')
            ->get()
            ->first(fn (Quiz $quiz) => $quiz->isLive() && $quiz->isTargetedAt($user));
    }

    /**
     * This student's not-yet-live, not-yet-attempted, targeted quizzes,
     * soonest first. Shared by the dashboard's announcement list and the
     * site-wide client-side watcher that redirects the moment one goes
     * live while the student is sitting on any page.
     */
    public static function upcomingFor(User $user): \Illuminate\Support\Collection
    {
        $attemptedQuizIds = QuizAttempt::where('user_id', $user->id)->pluck('quiz_id');

        return static::where('status', '!=', 'draft')
            ->whereNotNull('scheduled_at')
            ->whereNotNull('questions_finalized_at')
            ->where('scheduled_at', '>', now())
            ->whereNotIn('id', $attemptedQuizIds)
            ->orderBy('scheduled_at')
            ->get()
            ->filter(fn (Quiz $quiz) => $quiz->isTargetedAt($user))
            ->values();
    }

    /**
     * A published quiz stays editable right up until its scheduled start;
     * once students could be sitting it, its details are locked.
     */
    public function isEditable(): bool
    {
        return ! $this->hasStarted();
    }

    /**
     * The quiz's real-world lifecycle stage, computed live from time and
     * finalization rather than trusting a stored value that nothing ever
     * updates. "draft" and "closed" remain the lecturer's own authored
     * choices (a hard "don't show this" / "force end this" override);
     * every other stage is derived from scheduled_at, endsAt(), and
     * isFinalized() so it can never go stale.
     */
    public function stage(): string
    {
        if ($this->status === 'draft') {
            return 'draft';
        }

        if ($this->status === 'closed') {
            return 'closed';
        }

        if (! $this->scheduled_at) {
            return 'planned';
        }

        $now = now();
        $endsAt = $this->endsAt();

        if ($endsAt !== null && $now->greaterThanOrEqualTo($endsAt)) {
            return 'closed';
        }

        if ($this->isLive()) {
            return 'active';
        }

        $hoursUntilStart = ($this->scheduled_at->getTimestamp() - $now->getTimestamp()) / 3600;

        return $hoursUntilStart <= 24 ? 'due_soon' : 'scheduled';
    }
}
