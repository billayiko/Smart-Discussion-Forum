<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $conversations = $user->conversations()
            ->where('type', 'direct')
            ->with(['participants', 'messages' => fn ($query) => $query->latest()->limit(1)->with('user')])
            ->get()
            ->sortByDesc(fn (Conversation $conversation) => optional($conversation->messages->first())->created_at ?? $conversation->created_at)
            ->values()
            ->map(fn (Conversation $conversation) => $this->toListItem($conversation, $user));

        return response()->json($conversations);
    }

    public function show(Request $request, Conversation $conversation)
    {
        $user = $request->user();

        $this->authorizeParticipant($conversation, $user);

        $conversation->load(['messages.user', 'messages.excludedUsers']);
        $conversation->setRelation(
            'messages',
            $conversation->messages->reject(fn ($message) => $message->isExcludedFor($user))->values()
        );

        return response()->json([
            'id' => $conversation->id,
            'display_name' => $conversation->displayNameFor($user),
            'updated_at' => $conversation->updated_at,
            'messages' => $conversation->messages->map(fn ($message) => [
                'id' => $message->id,
                'body' => $message->body,
                'created_at' => $message->created_at,
                'user' => [
                    'id' => $message->user->id,
                    'name' => $message->user->name,
                ],
            ]),
        ]);
    }

    public function storeMessage(Request $request, Conversation $conversation)
    {
        $user = $request->user();

        $this->authorizeParticipant($conversation, $user);

        $validated = $request->validate([
            'body' => ['required', 'string'],
        ]);

        $conversation->messages()->create([
            'user_id' => $user->id,
            'body' => $validated['body'],
        ]);

        $conversation->touch();
        $user->recordCommunication();

        return response()->json(['message' => 'Message sent.'], 201);
    }

    public function start(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $targetId = (int) $validated['user_id'];

        abort_if($targetId === $user->id, 422, "You can't message yourself.");

        $conversation = Conversation::where('type', 'direct')
            ->whereHas('participants', fn ($query) => $query->where('users.id', $user->id))
            ->whereHas('participants', fn ($query) => $query->where('users.id', $targetId))
            ->withCount('participants')
            ->get()
            ->first(fn (Conversation $conversation) => $conversation->participants_count === 2);

        if (! $conversation) {
            $conversation = Conversation::create(['type' => 'direct', 'created_by' => $user->id]);
            $conversation->participants()->attach([$user->id, $targetId]);
        }

        return response()->json([
            'id' => $conversation->id,
            'display_name' => $conversation->displayNameFor($user),
        ], 201);
    }

    public function contacts(Request $request)
    {
        $contacts = User::where('id', '!=', $request->user()->id)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);

        return response()->json($contacts);
    }

    protected function toListItem(Conversation $conversation, User $user): array
    {
        $lastMessage = $conversation->messages->first();

        return [
            'id' => $conversation->id,
            'display_name' => $conversation->displayNameFor($user),
            'updated_at' => $conversation->updated_at,
            'last_message' => $lastMessage ? [
                'body' => $lastMessage->body,
                'user_name' => $lastMessage->user->name,
                'created_at' => $lastMessage->created_at,
            ] : null,
        ];
    }

    protected function authorizeParticipant(Conversation $conversation, User $user): void
    {
        abort_unless(
            $conversation->participants()->where('users.id', $user->id)->exists(),
            403
        );
    }
}
