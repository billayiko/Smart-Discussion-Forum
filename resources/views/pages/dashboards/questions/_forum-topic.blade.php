@php
    $groupCount = $otherTopics->count() + 1;
    $onlineCount = $groupMembers->filter(fn ($member) => $member->isOnline())->count();
@endphp

<div class="forum-shell">
    @include('pages.dashboards.questions._forum-topbar')

    <div class="forum-app">
        @include('pages.dashboards.questions._forum-sidebar')

        <main class="forum-main">
            @if (session('success'))
                <div class="forum-alert success">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="forum-alert error">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="forum-search-bar">
                <label class="forum-search">
                    <i class="fas fa-magnifying-glass"></i>
                    <input type="search" id="forum-search-input" placeholder="Search discussions, topics, or members...">
                </label>
                <select id="forum-search-scope">
                    <option value="all">All</option>
                    <option value="discussions">Discussions</option>
                    <option value="members">Members</option>
                </select>
                <button type="button" class="forum-btn gold" id="forum-search-btn"><i class="fas fa-arrow-right"></i> Search</button>
            </div>

            <div class="forum-heading">
                <h1>{{ $topic->title }}</h1>
                <p class="forum-breadcrumb">
                    <span>{{ $topic->title }}</span>
                    <i class="fas fa-chevron-right"></i>
                    <a href="{{ route('questions.index') }}">Discussions</a>
                </p>
                <p class="forum-meta" id="forum-meta">
                    <span><i class="fas fa-comments"></i> {{ $siblingThreads->count() }} threads</span>
                    <span><i class="fas fa-users"></i> {{ $groupMembers->count() }} members</span>
                </p>
            </div>

            <div class="forum-actions">
                <button type="button" class="forum-btn gold" id="forum-export-btn"><i class="fas fa-file-pdf"></i> Export PDF</button>
                <button type="button" class="forum-btn gold" id="forum-share-btn"><i class="fab fa-twitter"></i> Share</button>
                <button type="button" class="forum-btn gold" id="forum-sync-btn"><i class="fas fa-arrows-rotate"></i> Sync</button>
            </div>

            @if ($quiz)
                <div class="forum-quiz-banner" id="forum-quiz-banner" data-scheduled-at="{{ $quiz->scheduled_at->toIso8601String() }}">
                    <span class="forum-quiz-icon"><i class="fas fa-clock"></i></span>
                    <span class="forum-quiz-info">
                        <strong>Quiz: {{ $quiz->title }}</strong>
                        <span>{{ $quiz->subject }} &middot; {{ $quiz->duration_minutes }} min{{ $quiz->proctored ? ' · Proctored' : '' }}</span>
                    </span>
                    <span class="forum-quiz-countdown" id="forum-quiz-countdown">--:--</span>
                    <a href="{{ route('quizzes.index') }}" class="forum-btn gold">Enter Quiz <i class="fas fa-arrow-right"></i></a>
                </div>
            @endif

            <div class="forum-messages" id="forum-messages">
                @forelse ($siblingThreads as $thread)
                    <a href="{{ route('questions.show', $thread) }}" class="forum-message" style="text-decoration:none; color:inherit;">
                        <span class="forum-avatar">{{ $thread->user->initials() }}</span>
                        <div class="forum-message-body">
                            <div class="forum-message-head">
                                <strong>{{ $thread->title }}</strong>
                                <span class="forum-role-badge small" data-role="{{ $thread->user->role }}">{{ $thread->user->roleLabel() }}</span>
                                <span class="forum-muted">asked by {{ $thread->user->name }} &middot; {{ $thread->created_at->diffForHumans() }}</span>
                            </div>
                            <p>{{ \Illuminate\Support\Str::limit($thread->body, 140) }}</p>
                        </div>
                        <span class="forum-badge">{{ $thread->answers_count }}</span>
                    </a>
                @empty
                    <div class="forum-empty">
                        <i class="fas fa-comments"></i>
                        <strong>No discussions yet</strong>
                        <p class="forum-muted">Be the first to ask a question in this topic.</p>
                    </div>
                @endforelse
            </div>

            @if ($canAsk)
                <form method="POST" action="{{ route('questions.store') }}" class="forum-composer">
                    @csrf
                    <input type="hidden" name="course_topic_id" value="{{ $topic->id }}">
                    <input type="text" name="title" placeholder="Question title" class="forum-composer-topic" required>
                    <div class="forum-composer-row">
                        <textarea name="body" rows="1" placeholder="Describe your question..." required></textarea>
                        <button type="submit" class="forum-btn gold"><i class="fas fa-paper-plane"></i> Ask</button>
                    </div>
                </form>
            @endif

            <div class="forum-panel">
                <div class="forum-panel-head"><i class="fas fa-ranking-star"></i> Student Participation</div>
                @forelse ($participationLeaderboard as $row)
                    <div class="forum-participation-row">
                        <span class="forum-avatar">{{ $row->user->initials() }}</span>
                        <span class="forum-participation-name">{{ $row->user->name }}</span>
                        <span class="forum-muted">Posts: {{ $row->posts }}</span>
                        <span class="forum-score-tag">Score: {{ $row->score }}%</span>
                    </div>
                @empty
                    <p class="forum-muted">No student activity in this group yet.</p>
                @endforelse
            </div>

            <div class="forum-panel">
                <div class="forum-panel-head"><i class="fas fa-bolt"></i> Recent Activity</div>
                @forelse ($recentActivity as $event)
                    <div class="forum-activity-row">
                        <span class="forum-activity-icon"><i class="fas {{ $event->icon }}"></i></span>
                        <span class="forum-activity-text">{{ $event->text }}</span>
                        <span class="forum-muted">{{ $event->at->diffForHumans() }}</span>
                    </div>
                @empty
                    <p class="forum-muted">No recent activity yet.</p>
                @endforelse
            </div>
        </main>
    </div>
</div>

@include('pages.dashboards.questions._forum-assets')
