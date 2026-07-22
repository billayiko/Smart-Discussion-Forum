@php
    $topic = $question->topic;
    $groupCount = $otherTopics->count() + ($topic ? 1 : 0);
    $onlineCount = $groupMembers->filter(fn ($member) => $member->isOnline())->count();
    $activeQuestionId = $question->id;
    $shareUrl = route('questions.show', $question);
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
                <h1>{{ $question->title }}</h1>
                <p class="forum-breadcrumb">
                    <a href="{{ $topic ? route('topics.show', $topic) : '#' }}">{{ $topic->title ?? 'Other / General' }}</a>
                    <i class="fas fa-chevron-right"></i>
                    <a href="{{ route('questions.index') }}">Discussions</a>
                    <i class="fas fa-chevron-right"></i>
                    <span>{{ $question->title }}</span>
                </p>
                <p class="forum-meta" id="forum-meta">
                    <span><i class="fas fa-clock"></i> Last activity: {{ $question->updated_at->diffForHumans() }}</span>
                    <span><i class="fas fa-eye"></i> {{ number_format($question->views) }} views</span>
                    <span><i class="fas fa-comment"></i> {{ number_format($question->answers->count()) }} repl{{ $question->answers->count() === 1 ? 'y' : 'ies' }}</span>
                </p>
            </div>

            <div class="forum-actions">
                <button type="button" class="forum-btn gold" id="forum-export-btn"><i class="fas fa-file-pdf"></i> Export PDF</button>
                @include('pages.dashboards.questions._share-menu', ['shareUrl' => $shareUrl, 'shareText' => $question->title, 'buttonClass' => 'forum-btn gold', 'buttonLabel' => 'Share'])
                <button type="button" class="forum-btn gold" id="forum-sync-btn"><i class="fas fa-arrows-rotate"></i> Sync</button>
                <details class="forum-report">
                    <summary class="forum-btn light"><i class="fas fa-flag"></i> Report</summary>
                    <form method="POST" action="{{ route('questions.complaints.store', $question) }}" class="forum-report-form">
                        @csrf
                        <textarea name="reason" rows="3" placeholder="Why are you reporting this question?" required></textarea>
                        <button type="submit" class="forum-btn light">Submit report</button>
                    </form>
                </details>
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
                <div class="forum-message">
                    <span class="forum-avatar">{{ $question->user->initials() }}</span>
                    <div class="forum-message-body">
                        <div class="forum-message-head">
                            <strong>{{ $question->user->name }}</strong>
                            <span class="forum-role-badge small" data-role="{{ $question->user->role }}">{{ $question->user->roleLabel() }}</span>
                            <span class="forum-muted">{{ $question->created_at->diffForHumans() }}</span>
                        </div>
                        <p>{{ $question->body }}</p>
                        <div class="forum-message-footer">
                            <button type="button" class="forum-like-btn {{ $question->isLikedBy($user) ? 'liked' : '' }}" data-like-url="{{ route('questions.like', $question) }}">
                                <i class="fas fa-heart"></i> <span class="forum-like-count">{{ $question->likes->count() }}</span>
                            </button>
                            <span class="forum-view-count"><i class="fas fa-eye"></i> {{ number_format($question->views) }}</span>
                            <button type="button" class="forum-reply-trigger"><i class="fas fa-reply"></i> Reply</button>
                            @include('pages.dashboards.questions._share-menu', ['shareUrl' => $shareUrl, 'shareText' => $question->title])
                        </div>
                    </div>
                </div>

                @forelse ($question->answers as $answer)
                    <div class="forum-message">
                        <span class="forum-avatar">{{ $answer->user->initials() }}</span>
                        <div class="forum-message-body">
                            <div class="forum-message-head">
                                <strong>{{ $answer->user->name }}</strong>
                                <span class="forum-role-badge small" data-role="{{ $answer->user->role }}">{{ $answer->user->roleLabel() }}</span>
                                @if ($answer->topic)
                                    <span class="forum-topic-chip">{{ $answer->topic }}</span>
                                @endif
                                <span class="forum-muted">{{ $answer->created_at->diffForHumans() }}</span>
                            </div>
                            <p>{{ $answer->body }}</p>
                            <div class="forum-message-footer">
                                <button type="button" class="forum-like-btn {{ $answer->isLikedBy($user) ? 'liked' : '' }}" data-like-url="{{ route('answers.like', $answer) }}">
                                    <i class="fas fa-heart"></i> <span class="forum-like-count">{{ $answer->likes->count() }}</span>
                                </button>
                                <span class="forum-view-count"><i class="fas fa-eye"></i> {{ number_format($answer->views) }}</span>
                                <button type="button" class="forum-reply-trigger"><i class="fas fa-reply"></i> Reply</button>
                                @include('pages.dashboards.questions._share-menu', ['shareUrl' => $shareUrl, 'shareText' => $answer->user->name.'\'s reply on '.$question->title])
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="forum-muted" style="padding:12px 0;">No replies yet. Be the first to respond.</p>
                @endforelse
            </div>

            <form method="POST" action="{{ route('questions.answers.store', $question) }}" class="forum-composer">
                @csrf
                <input type="text" name="topic" placeholder="Topic (optional)" class="forum-composer-topic">
                <div class="forum-composer-row">
                    <textarea id="forum-reply-input" name="body" rows="1" placeholder="Write your message..." required></textarea>
                    <button type="submit" class="forum-btn gold"><i class="fas fa-paper-plane"></i> Send</button>
                </div>
                <div class="forum-composer-footer">
                    <details class="forum-exclude">
                        <summary class="forum-btn light"><i class="fas fa-user-slash"></i> Exclude <span class="forum-badge" id="forum-exclude-count">0</span> <i class="fas fa-chevron-down"></i></summary>
                        <div class="forum-exclude-list">
                            @forelse ($groupMembers->where('id', '!=', $user->id) as $member)
                                <label>
                                    <input type="checkbox" name="excluded_user_ids[]" value="{{ $member->id }}" class="forum-exclude-checkbox">
                                    {{ $member->name }}
                                </label>
                            @empty
                                <p class="forum-muted">No other group members to exclude.</p>
                            @endforelse
                        </div>
                    </details>
                    <span class="forum-online-pill"><span class="forum-dot online"></span> Online</span>
                </div>
            </form>
        </main>
    </div>
</div>

@include('pages.dashboards.questions._forum-assets')
