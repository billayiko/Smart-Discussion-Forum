<?php

namespace App\Models;

use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use App\Concerns\HasTeams;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Passkeys\PasskeyAuthenticatable;

/**
 * Cleaned up SDD User Model
 */
#[Fillable(['name', 'email', 'password', 'role', 'current_team_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory */
    use HasFactory, HasApiTokens, HasTeams, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

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
        ];
    }
        /**
     * Get the user's initials.
     *
     * @return string
     */
    public function initials(): string
    {
        $words = explode(' ', $this->name);
        $initials = '';

        foreach ($words as $word) {
            if (!empty($word)) {
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
}
