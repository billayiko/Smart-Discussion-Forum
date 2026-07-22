<?php

namespace App\Models;

use App\Concerns\Likeable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Answer extends Model
{
    use HasFactory, Likeable;

    protected $fillable = [
        'question_id',
        'user_id',
        'body',
        'topic',
        'views',
    ];

    protected $touches = ['question'];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function excludedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'answer_exclusions')->withTimestamps();
    }

    public function isExcludedFor(User $user): bool
    {
        return $this->excludedUsers->contains('id', $user->id);
    }
}
