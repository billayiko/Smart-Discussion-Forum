<a href="{{ route('messages.index') }}" class="pulse-muted" style="display:inline-block; margin-bottom:14px;"><i class="fas fa-arrow-left"></i> Back to Messages</a>

@if (session('success'))
    <div class="pulse-card pulse-pad" style="margin-bottom:18px; color: var(--pulse-green, #1a7f37);">
        {{ session('success') }}
    </div>
@endif

<section class="pulse-grid" style="grid-template-columns: {{ $conversation->isGroup() ? 'minmax(0,1.1fr) minmax(280px,.6fr)' : 'minmax(0,1fr)' }}; align-items:start;">
    <article class="pulse-card pulse-pad">
        <div class="pulse-section-head">
            <h2>{{ $conversation->displayNameFor($user) }}</h2>
            @if ($conversation->isGroup())
                <span class="pulse-tag">Group &middot; {{ $conversation->participants->count() }} members</span>
            @endif
        </div>

        <div class="pulse-list">
            @forelse ($conversation->messages as $message)
                <div class="pulse-row" style="align-items:flex-start;">
                    <span class="pulse-soft-icon"><i class="fas fa-comment"></i></span>
                    <span>
                        <strong>{{ $message->user->id === $user->id ? 'You' : $message->user->name }}</strong>
                        <p>{{ $message->body }}</p>
                        <span class="pulse-muted">{{ $message->created_at->diffForHumans() }}</span>
                    </span>
                </div>
            @empty
                <div class="pulse-row"><span class="pulse-muted">No messages yet. Say hello!</span></div>
            @endforelse
        </div>

        <form method="POST" action="{{ route('messages.messages.store', $conversation) }}" class="pulse-form" style="margin-top:16px;">
            @csrf
            <div class="pulse-field">
                <div class="pulse-input" style="min-height:80px; align-items:flex-start; padding-top:12px;">
                    <textarea name="body" rows="3" placeholder="Write a message..." style="width:100%; border:0; outline:0; background:transparent; resize:vertical;" required></textarea>
                </div>
            </div>
            <div>
                <button class="pulse-btn" type="submit"><i class="fas fa-paper-plane"></i> Send</button>
            </div>
        </form>
    </article>

    @if ($conversation->isGroup())
        <article class="pulse-card pulse-pad">
            <div class="pulse-section-head">
                <h2>Members</h2>
            </div>
            <div class="pulse-list">
                @foreach ($conversation->participants as $participant)
                    <div class="pulse-row">
                        <span class="pulse-soft-icon"><i class="fas fa-user"></i></span>
                        <span>
                            <strong>{{ $participant->id === $user->id ? 'You' : $participant->name }}</strong>
                            <p>{{ $participant->roleLabel() }}{{ $participant->id === $conversation->created_by ? ' · Creator' : '' }}</p>
                        </span>
                        @if ($canManageMembers && $participant->id !== $conversation->created_by)
                            <form method="POST" action="{{ route('messages.members.destroy', [$conversation, $participant]) }}" onsubmit="return confirm('Remove this member?');">
                                @csrf
                                @method('DELETE')
                                <button class="pulse-icon-btn" style="color:#d33;" type="submit" title="Remove"><i class="fas fa-user-minus"></i></button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>

            @if ($canManageMembers)
                <form method="POST" action="{{ route('messages.members.store', $conversation) }}" style="margin-top:16px; display:flex; gap:8px;">
                    @csrf
                    <div class="pulse-input" style="flex:1; min-height:42px;">
                        <select name="user_id" required>
                            <option value="">Add a member...</option>
                            @foreach ($addableUsers as $person)
                                <option value="{{ $person->id }}">{{ $person->name }} ({{ $person->roleLabel() }})</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="pulse-btn light" type="submit"><i class="fas fa-user-plus"></i></button>
                </form>
            @endif
        </article>
    @endif
</section>
