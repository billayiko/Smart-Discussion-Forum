@if (session('success'))
    <div class="pulse-card pulse-pad" style="margin-bottom:18px; color: var(--pulse-green, #1a7f37);">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="pulse-card pulse-pad" style="margin-bottom:18px; color:#d33;">
        <ul style="margin:0; padding-left:18px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<section class="pulse-grid" style="grid-template-columns: minmax(0,1.1fr) minmax(320px,.9fr); align-items:start;">
    <article class="pulse-card pulse-pad">
        <div class="pulse-section-head">
            <h2>Your Conversations</h2>
        </div>
        <div class="pulse-list">
            @forelse ($conversations as $conversation)
                <a href="{{ route('messages.show', $conversation) }}" style="display:contents;">
                    <div class="pulse-row">
                        <span class="pulse-soft-icon"><i class="fas {{ $conversation->isGroup() ? 'fa-user-group' : 'fa-user' }}"></i></span>
                        <span>
                            <strong>{{ $conversation->displayNameFor($user) }}</strong>
                            <p>{{ optional($conversation->messages->first())->body ?? 'No messages yet.' }}</p>
                        </span>
                        @if ($conversation->isGroup())
                            <span class="pulse-tag">Group</span>
                        @endif
                    </div>
                </a>
            @empty
                <div class="pulse-row"><span class="pulse-muted">No conversations yet. Start one from the panel on the right.</span></div>
            @endforelse
        </div>
    </article>

    <article class="pulse-card pulse-pad">
        <div class="pulse-section-head">
            <h2>New Message</h2>
        </div>
        <form method="GET" action="{{ route('messages.index') }}" style="display:flex; gap:8px; margin-bottom:18px;">
            <div class="pulse-input" style="flex:1;">
                <input type="search" name="search" placeholder="Search users by name or email..." value="{{ $search }}">
            </div>
            <button class="pulse-btn light" type="submit"><i class="fas fa-magnifying-glass"></i></button>
        </form>

        @if ($search !== '')
            <p class="pulse-muted" style="margin:0 0 8px;">Search results</p>
            <div class="pulse-list" style="margin-bottom:18px;">
                @forelse ($searchResults as $person)
                    <div class="pulse-row">
                        <span class="pulse-soft-icon"><i class="fas fa-user"></i></span>
                        <span><strong>{{ $person->name }}</strong><p>{{ $person->roleLabel() }}</p></span>
                        <form method="POST" action="{{ route('messages.start') }}">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $person->id }}">
                            <button class="pulse-btn light" type="submit">Message</button>
                        </form>
                    </div>
                @empty
                    <div class="pulse-row"><span class="pulse-muted">No users found.</span></div>
                @endforelse
            </div>
        @endif

        <p class="pulse-muted" style="margin:0 0 8px;">Suggested &middot; subscribed to your topics</p>
        <div class="pulse-list" style="margin-bottom:18px;">
            @forelse ($priorityContacts as $person)
                <div class="pulse-row">
                    <span class="pulse-soft-icon"><i class="fas fa-user"></i></span>
                    <span><strong>{{ $person->name }}</strong><p>{{ $person->roleLabel() }}</p></span>
                    <form method="POST" action="{{ route('messages.start') }}">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ $person->id }}">
                        <button class="pulse-btn light" type="submit">Message</button>
                    </form>
                </div>
            @empty
                <div class="pulse-row"><span class="pulse-muted">No suggestions yet &mdash; topic subscriptions will surface classmates here.</span></div>
            @endforelse
        </div>

        <details>
            <summary class="pulse-muted" style="cursor:pointer;"><i class="fas fa-user-group"></i> Create a group</summary>
            <form method="POST" action="{{ route('messages.groups.store') }}" class="pulse-form" style="margin-top:12px;">
                @csrf
                <div class="pulse-field">
                    <label for="group_name">Group name</label>
                    <div class="pulse-input">
                        <input id="group_name" type="text" name="name" placeholder="e.g. Study Group" required>
                    </div>
                </div>
                <div class="pulse-field">
                    <label>Add members</label>
                    <div style="max-height:200px; overflow:auto; display:grid; gap:8px; padding:10px; border:1px solid #dce5f5; border-radius:12px;">
                        @foreach ($allUsers as $person)
                            <label style="display:flex; align-items:center; gap:8px; font-weight:600;">
                                <input type="checkbox" name="member_ids[]" value="{{ $person->id }}">
                                {{ $person->name }} <span class="pulse-muted">({{ $person->roleLabel() }})</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <button class="pulse-btn" type="submit"><i class="fas fa-plus"></i> Create Group</button>
            </form>
        </details>
    </article>
</section>
