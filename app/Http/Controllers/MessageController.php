<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $search = trim((string) $request->query('search', ''));

        $conversations = $user->conversations()
            ->with(['participants', 'messages' => fn ($query) => $query->latest()->limit(1)])
            ->get()
            ->sortByDesc(fn (Conversation $conversation) => optional($conversation->messages->first())->created_at ?? $conversation->created_at)
            ->values();

        $priorityContacts = User::whereIn('id', $user->priorityContactIds())
            ->orderBy('name')
            ->get();

        $searchResults = collect();

        if ($search !== '') {
            $searchResults = User::where('id', '!=', $user->id)
                ->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                ->orderBy('name')
                ->get();
        }

        $allUsers = User::where('id', '!=', $user->id)->orderBy('name')->get();

        return view("pages.dashboards.{$user->role}.messages.index", compact(
            'user',
            'conversations',
            'priorityContacts',
            'searchResults',
            'search',
            'allUsers'
        ));
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

        return redirect()->route('messages.show', $conversation);
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

        return redirect()->route('messages.show', $conversation)->with('success', 'Group created.');
    }

    public function show(Request $request, Conversation $conversation)
    {
        $user = $request->user();

        $this->authorizeParticipant($conversation, $user);

        $conversation->load(['messages.user', 'participants']);

        $canManageMembers = $conversation->isGroup() && $conversation->created_by === $user->id;

        $addableUsers = $canManageMembers
            ? User::whereNotIn('id', $conversation->participants->pluck('id'))->orderBy('name')->get()
            : collect();

        return view("pages.dashboards.{$user->role}.messages.show", compact('user', 'conversation', 'canManageMembers', 'addableUsers'));
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

        return back()->with('success', 'Message sent.');
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

        return back()->with('success', 'Member added.');
    }

    public function removeMember(Request $request, Conversation $conversation, User $member)
    {
        $user = $request->user();

        abort_unless($conversation->isGroup(), 404);
        abort_unless($conversation->created_by === $user->id, 403);
        abort_if($member->id === $conversation->created_by, 422, 'The group creator cannot be removed.');

        $conversation->participants()->detach($member->id);

        return back()->with('success', 'Member removed.');
    }

    protected function authorizeParticipant(Conversation $conversation, User $user): void
    {
        abort_unless(
            $conversation->participants()->where('users.id', $user->id)->exists(),
            403
        );
    }
}
