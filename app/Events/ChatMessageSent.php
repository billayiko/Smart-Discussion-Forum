<?php

namespace App\Events;

use App\Models\Message;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Support\Collection;

/**
 * Realtime chat delivery. Deliberately ShouldBroadcastNow, not
 * ShouldBroadcast — a plain Notification's 'broadcast' channel routes
 * through Illuminate\Notifications\Events\BroadcastNotificationCreated,
 * whose listener (Illuminate\Broadcasting\BroadcastEvent) always queues
 * regardless of the notification's own ShouldQueue status. That silently
 * stranded every chat broadcast in the jobs table with no queue worker
 * running. ShouldBroadcastNow is the framework's actual mechanism for
 * "broadcast synchronously, no queue involved."
 *
 * One channel per recipient (never a shared per-conversation channel), so
 * a message can be withheld from specific excluded participants simply by
 * never listing their channel here — the same guarantee
 * MessageController::show already enforces for the non-realtime path.
 */
class ChatMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    /** @var array<int, int> */
    private array $recipientIds;

    public function __construct(private readonly Message $message, Collection $recipients)
    {
        $this->recipientIds = $recipients->pluck('id')->all();
    }

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return array_map(
            fn (int $userId) => new PrivateChannel('App.Models.User.'.$userId),
            $this->recipientIds
        );
    }

    public function broadcastAs(): string
    {
        return 'chat.message';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'body' => $this->message->body,
            'sender_id' => $this->message->user_id,
            'sender_name' => $this->message->user->name,
            'created_at' => $this->message->created_at->toIso8601String(),
        ];
    }
}
