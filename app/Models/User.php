<?php

namespace App\Models;

use App\Concerns\HasTeams;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Passkeys\PasskeyAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Cleaned up SDD User Model
 */
#[Fillable(['name', 'email', 'password', 'role', 'current_team_id', 'rules_agreed_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory */
    use HasApiTokens, HasFactory, HasTeams, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'blacklisted' => 'boolean',
            'blacklisted_until' => 'datetime',
            'last_communication_at' => 'datetime',
            'last_warned_at' => 'datetime',
            'rules_agreed_at' => 'datetime',
        ];
    }

    /**
     * Get the user's initials.
     */
    public function initials(): string
    {
        $words = explode(' ', $this->name);
        $initials = '';

        foreach ($words as $word) {
            if (! empty($word)) {
                $initials .= strtoupper($word[0]);
            }
        }

        return substr($initials, 0, 2); // Returns max 2 characters
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }

    public function assignedTopics(): HasMany
    {
        return $this->hasMany(CourseTopic::class, 'lecturer_id');
    }

    public function subscribedTopics(): BelongsToMany
    {
        return $this->belongsToMany(CourseTopic::class, 'course_topic_subscriptions', 'user_id', 'course_topic_id')->withTimestamps();
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    public function conversations(): BelongsToMany
    {
        return $this->belongsToMany(Conversation::class, 'conversation_user')->withTimestamps();
    }

    /**
     * IDs of other users to prioritize when starting a new message, based on
     * shared topic subscriptions (students <-> students, students <-> their topic's lecturer).
     */
    public function priorityContactIds(): Collection
    {
        if ($this->role === 'student') {
            $topicIds = $this->subscribedTopics()->pluck('course_topics.id');

            $studentIds = static::where('role', 'student')
                ->where('id', '!=', $this->id)
                ->whereHas('subscribedTopics', fn ($query) => $query->whereIn('course_topics.id', $topicIds))
                ->pluck('id');

            $lecturerIds = static::where('role', 'lecturer')
                ->whereHas('assignedTopics', fn ($query) => $query->whereIn('course_topics.id', $topicIds))
                ->pluck('id');

            return $studentIds->merge($lecturerIds)->unique()->values();
        }

        if ($this->role === 'lecturer') {
            $topicIds = $this->assignedTopics()->pluck('id');

            return static::where('role', 'student')
                ->whereHas('subscribedTopics', fn ($query) => $query->whereIn('course_topics.id', $topicIds))
                ->pluck('id')
                ->unique()
                ->values();
        }

        return collect();
    }

    /**
     * Record that the user communicated (sent a message or posted an answer),
     * resetting their inactivity warnings.
     */
    public function recordCommunication(): void
    {
        $this->forceFill([
            'last_communication_at' => now(),
            'warning_count' => 0,
            'last_warned_at' => null,
        ])->save();
    }

    public function isBlacklisted(): bool
    {
        if (! $this->blacklisted) {
            return false;
        }

        if ($this->blacklisted_until && $this->blacklisted_until->isPast()) {
            $this->liftBlacklist();

            return false;
        }

        return true;
    }

    public function liftBlacklist(): void
    {
        $this->forceFill([
            'blacklisted' => false,
            'blacklisted_until' => null,
            'warning_count' => 0,
            'last_warned_at' => null,
            'last_communication_at' => now(),
        ])->save();
    }

    public function isOnline(): bool
    {
        return DB::table('sessions')
            ->where('user_id', $this->id)
            ->where('last_activity', '>=', now()->subMinutes(5)->timestamp)
            ->exists();
    }

    public function roleLabel(): string
    {
        return $this->role
            ? Str::of($this->role)->replace(['_', '-'], ' ')->ucfirst()->toString()
            : __('User');
    }

    /**
     * The name of the route this user should land on after authenticating.
     */
    public function dashboardRouteName(): string
    {
        return match ($this->role) {
            'student' => 'student.dashboard',
            'lecturer' => 'lecturer.dashboard',
            'admin' => 'admin.dashboard',
            default => 'home',
        };
    }
}
