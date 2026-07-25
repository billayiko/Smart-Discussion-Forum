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

        $conversation->load(['messages.user', 'messages.excludedUsers', 'participants']);
        $conversation->setRelation(
            'messages',
            $conversation->messages->reject(fn ($message) => $message->isExcludedFor($user))->values()
        );

        $canManageMembers = $conversation->isGroup() && $conversation->created_by === $user->id;

        $addableUsers = $canManageMembers
            ? User::whereNotIn('id', $conversation->participants->pluck('id'))->orderBy('name')->get(['id', 'name', 'role'])
            : collect();

        return response()->json([
            'id' => $conversation->id,
            'display_name' => $conversation->displayNameFor($user),
            'is_group' => $conversation->isGroup(),
            'created_by' => $conversation->created_by,
            'can_manage_members' => $canManageMembers,
            'updated_at' => $conversation->updated_at,
            'participants' => $conversation->participants->map(fn (User $p) => [
                'id' => $p->id,
                'name' => $p->id === $user->id ? 'You' : $p->name,
                'role' => $p->role,
                'is_creator' => $p->id === $conversation->created_by,
            ]),
            'addable_users' => $addableUsers->map(fn (User $p) => ['id' => $p->id, 'name' => $p->name, 'role' => $p->role]),
            'messages' => $conversation->messages->map(fn ($message) => [
                'id' => $message->id,
                'body' => $message->body,
                'created_at' => $message->created_at,
                'user' => [
                    'id' => $message->user->id,
                    'name' => $message->user->id === $user->id ? 'You' : $message->user->name,
                ],
                'excluded_names' => $message->user_id === $user->id
                    ? $message->excludedUsers->pluck('name')
                    : [],
            ]),
        ]);
    }

    public function storeMessage(Request $request, Conversation $conversation)
    {
        $user = $request->user();

        $this->authorizeParticipant($conversation, $user);

        $validated = $request->validate([
            'body' => ['required', 'string'],
            'excluded_user_ids' => ['nullable', 'array'],
            'excluded_user_ids.*' => ['integer'],
        ]);

        $message = $conversation->messages()->create([
            'user_id' => $user->id,
            'body' => $validated['body'],
        ]);

        if ($conversation->isGroup() && ! empty($validated['excluded_user_ids'])) {
            $validExclusions = $conversation->participants->pluck('id')
                ->intersect($validated['excluded_user_ids'])
                ->reject(fn ($id) => $id === $user->id);

            $message->excludedUsers()->sync($validExclusions);
        }

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

    public function storeGroup(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'member_ids' => ['required', 'array', 'min:1'],
            'member_ids.*' => ['exists:users,id'],
        ]);

        $conversation = Conversation::create([
            'type' => 'group',
            'name' => $validated['name'],
            'created_by' => $user->id,
        ]);

        $memberIds = collect($validated['member_ids'])->map(fn ($id) => (int) $id)->push($user->id)->unique();
        $conversation->participants()->attach($memberIds);

        return response()->json(['id' => $conversation->id, 'display_name' => $conversation->displayNameFor($user)], 201);
    }

    public function addMember(Request $request, Conversation $conversation)
    {
        $user = $request->user();

        abort_unless($conversation->isGroup(), 404);
        abort_unless($conversation->created_by === $user->id, 403);

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $conversation->participants()->syncWithoutDetaching([$validated['user_id']]);

        return response()->json(['message' => 'Member added.']);
    }

    public function removeMember(Request $request, Conversation $conversation, User $member)
    {
        $user = $request->user();

        abort_unless($conversation->isGroup(), 404);
        abort_unless($conversation->created_by === $user->id, 403);
        abort_if($member->id === $conversation->created_by, 422, 'The group creator cannot be removed.');

        $conversation->participants()->detach($member->id);

        return response()->json(['message' => 'Member removed.']);
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
            'is_group' => $conversation->isGroup(),
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
