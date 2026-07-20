<aside class="forum-sidebar">
    <div class="forum-sidebar-head">
        <span class="forum-sidebar-title"><i class="fas fa-comments"></i> DISCUSSIONS</span>
        <span class="forum-count-badge">{{ $groupCount }} GROUPS</span>
    </div>

    <a href="{{ route('questions.index') }}" class="forum-all-link"><i class="fas fa-arrow-left"></i> All discussions</a>

    <div class="forum-groups" data-forum-search-scope="discussions">
        @if ($topic)
            <div class="forum-group expanded">
                <div class="forum-group-head">
                    <span><i class="fas fa-chevron-down"></i> {{ $topic->title }}</span>
                    <span class="forum-group-meta"><span class="forum-dot online"></span> {{ $groupMembers->count() }} members</span>
                </div>
                <div class="forum-group-threads">
                    @foreach ($siblingThreads as $thread)
                        <a href="{{ route('questions.show', $thread) }}" class="forum-thread-link {{ ($activeQuestionId ?? null) === $thread->id ? 'active' : '' }}" data-forum-search-text="{{ strtolower($thread->title) }}">
                            <i class="fas fa-code-branch"></i>
                            <span>{{ $thread->title }}</span>
                            <span class="forum-badge">{{ $thread->answers_count }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @foreach ($otherTopics as $other)
            <a href="{{ route('topics.index') }}" class="forum-group collapsed" data-forum-search-text="{{ strtolower($other->title) }}">
                <span><i class="fas fa-chevron-right"></i> {{ $other->title }}</span>
                <span class="forum-group-meta">{{ $other->subscribers_count }} members</span>
            </a>
        @endforeach
    </div>

    <div class="forum-sidebar-head" style="margin-top:20px;">
        <span class="forum-sidebar-title"><i class="fas fa-users"></i> GROUP MEMBERS</span>
        <span class="forum-count-badge">{{ $onlineCount }} ONLINE</span>
    </div>

    <div class="forum-members" data-forum-search-scope="members">
        @forelse ($groupMembers as $member)
            <div class="forum-member" data-forum-search-text="{{ strtolower($member->name) }}">
                <span class="forum-avatar">{{ $member->initials() }}</span>
                <span class="forum-member-info">
                    <strong>{{ $member->name }}</strong>
                    <span class="forum-dot {{ $member->isOnline() ? 'online' : 'offline' }}"></span>
                    <span>{{ $member->isOnline() ? 'Online' : 'Offline' }}</span>
                </span>
                <span class="forum-role-badge small" data-role="{{ $member->role }}">{{ $member->roleLabel() }}</span>
            </div>
        @empty
            <p class="forum-muted">No members in this group yet.</p>
        @endforelse
    </div>
</aside>
