<?php

namespace App\Models;

use App\Events\ChatMessageSent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Log;
use Throwable;

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

    /**
     * Realtime delivery for this message: every conversation participant
     * except the sender and anyone this specific message excludes. Each
     * recipient is addressed on their own private channel (see
     * ChatMessageSent::broadcastOn), so an excluded user's socket never
     * receives the payload at all — the same guarantee
     * MessageController::show already enforces for the non-realtime path.
     *
     * ShouldBroadcastNow means this hits Reverb synchronously, in-request —
     * if Reverb isn't running (or the socket write otherwise fails), that
     * must never take the whole "message sent" response down with it, since
     * the message itself is already persisted by the time this runs. The
     * 5s poll on both clients is the fallback for exactly this case.
     */
    public function notifyParticipants(): void
    {
        $excludedIds = $this->excludedUsers->pluck('id');

        $recipients = $this->conversation->participants
            ->reject(fn (User $participant) => $participant->id === $this->user_id || $excludedIds->contains($participant->id));

        if ($recipients->isEmpty()) {
            return;
        }

        try {
            event(new ChatMessageSent($this, $recipients));
        } catch (Throwable $e) {
            Log::warning('Realtime chat broadcast failed; message was still saved.', [
                'message_id' => $this->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
