<?php

namespace App\Models;

use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use App\Concerns\HasTeams;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
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

    public function roleLabel(): string
    {
        return $this->role
            ? Str::of($this->role)->replace(['_', '-'], ' ')->ucfirst()->toString()
            : __('User');
    }
}
