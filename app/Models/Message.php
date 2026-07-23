<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Message extends Model
{
    use HasFactory;

    protected $table = 'chat_messages';

    protected $fillable = [
        'conversation_id',
        'user_id',
        'body',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function excludedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'message_exclusions', 'chat_message_id', 'user_id')->withTimestamps();
    }

    public function isExcludedFor(User $user): bool
    {
        return $this->excludedUsers->contains('id', $user->id);
    }
}
