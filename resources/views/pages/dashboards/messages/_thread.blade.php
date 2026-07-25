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

        <div class="pulse-list" id="messages-list">
            @forelse ($conversation->messages as $message)
                @php($isOwn = $message->user->id === $user->id)
                <div style="display:flex; justify-content:{{ $isOwn ? 'flex-end' : 'flex-start' }}; align-items:flex-end; gap:8px;">
                    @unless ($isOwn)
                        <span class="pulse-soft-icon" style="flex:0 0 auto;"><i class="fas fa-comment"></i></span>
                    @endunless

                    <div style="max-width:72%; padding:10px 14px; border-radius:16px; border-bottom-{{ $isOwn ? 'right' : 'left' }}-radius:4px; background:{{ $isOwn ? 'var(--pulse-blue)' : 'var(--pulse-blue-soft)' }}; color:{{ $isOwn ? '#fff' : 'var(--pulse-ink)' }};">
                        @unless ($isOwn)
                            <strong style="display:block; font-size:.78rem; margin-bottom:3px; color:var(--pulse-blue);">{{ $message->user->name }}</strong>
                        @endunless
                        <p style="margin:0; white-space:pre-wrap; word-break:break-word;">{{ $message->body }}</p>
                        <span style="display:block; margin-top:5px; font-size:.72rem; {{ $isOwn ? 'color:rgba(255,255,255,.8);' : 'color:var(--pulse-muted);' }}">{{ $message->created_at->diffForHumans() }}</span>
                        @if ($isOwn && $message->excludedUsers->isNotEmpty())
                            <span class="pulse-tag orange" style="margin-top:6px;">Hidden from {{ $message->excludedUsers->pluck('name')->join(', ') }}</span>
                        @endif
                    </div>
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

            @if ($conversation->isGroup())
                <details class="pulse-field" style="background:var(--pulse-surface-2, #f6f7f9); border-radius:10px; padding:10px 14px; margin-bottom:12px;">
                    <summary style="cursor:pointer; font-weight:700; font-size:.86rem;">Exclude specific members from this message (optional)</summary>
                    <div style="display:grid; gap:6px; margin-top:10px;">
                        @foreach ($conversation->participants as $participant)
                            @if ($participant->id !== $user->id)
                                <label style="display:flex; align-items:center; gap:8px; font-weight:600; font-size:.86rem;">
                                    <input type="checkbox" name="excluded_user_ids[]" value="{{ $participant->id }}">
                                    {{ $participant->name }}
                                </label>
                            @endif
                        @endforeach
                    </div>
                </details>
            @endif

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

<script>
    (function () {
        async function syncMessages() {
            try {
                const res = await fetch(window.location.href, { headers: { 'X-Sync': '1' } });
                const html = await res.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');

                const fresh = doc.getElementById('messages-list');
                const current = document.getElementById('messages-list');
                if (fresh && current) {
                    current.replaceWith(fresh);
                }
            } catch (e) {
                // leave the thread as-is on network failure; the next tick will retry
            }
        }

        // Poll for new messages every 5s — kept as a safety net even with realtime
        // wired up below, so a missed/dropped socket event costs at most 5s, never
        // a lost update.
        setInterval(syncMessages, 5000);

        // Realtime: nudge an immediate refresh as soon as a message arrives,
        // instead of waiting for the next poll tick.
        if (window.Echo) {
            window.Echo.private('App.Models.User.{{ $user->id }}')
                .listen('.chat.message', (payload) => {
                    if (Number(payload.conversation_id) === {{ $conversation->id }}) {
                        syncMessages();
                    }
                });
        }
    })();
</script>
