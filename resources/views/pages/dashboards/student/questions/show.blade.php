<x-layouts.academic-pulse title="{{ $question->title }}">
    @php
        $topic = $question->topic;
        $groupCount = $otherTopics->count() + ($topic ? 1 : 0);
        $onlineCount = $groupMembers->filter(fn ($member) => $member->isOnline())->count();
    @endphp

    <div class="forum-shell">
        <header class="forum-topbar">
            <a class="forum-logo" href="{{ route('student.dashboard') }}">
                <i class="fas fa-graduation-cap"></i>
                <span>Academic<span>Pulse Forum</span><small>Smart Discussion Forum</small></span>
            </a>

            <div class="forum-topbar-right">
                <span class="forum-online-pill"><span class="forum-dot online"></span> Online</span>
                <span class="forum-user">
                    <span class="forum-avatar gold">{{ $user->initials() }}</span>
                    <strong>{{ $user->name }}</strong>
                    <span class="forum-role-badge" data-role="{{ $user->role }}"><i class="fas fa-graduation-cap"></i> {{ strtoupper($user->roleLabel()) }}</span>
                </span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="forum-btn danger"><i class="fas fa-right-from-bracket"></i> Logout</button>
                </form>
            </div>
        </header>

        <div class="forum-app">
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
                                    <a href="{{ route('questions.show', $thread) }}" class="forum-thread-link {{ $thread->id === $question->id ? 'active' : '' }}" data-forum-search-text="{{ strtolower($thread->title) }}">
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
                        <p class="forum-muted">This question isn't linked to a topic, so there's no group roster to show.</p>
                    @endforelse
                </div>
            </aside>

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
                        <a href="{{ $topic ? route('topics.index') : '#' }}">{{ $topic->title ?? 'Other / General' }}</a>
                        <i class="fas fa-chevron-right"></i>
                        <a href="{{ route('questions.index') }}">Discussions</a>
                        <i class="fas fa-chevron-right"></i>
                        <span>{{ $question->title }}</span>
                    </p>
                    <p class="forum-meta" id="forum-meta">
                        <span><i class="fas fa-clock"></i> Last activity: {{ $question->updated_at->diffForHumans() }}</span>
                        <span><i class="fas fa-eye"></i> {{ number_format($question->views) }} views</span>
                    </p>
                </div>

                <div class="forum-actions">
                    <button type="button" class="forum-btn gold" id="forum-export-btn"><i class="fas fa-file-pdf"></i> Export PDF</button>
                    <button type="button" class="forum-btn gold" id="forum-share-btn"><i class="fab fa-twitter"></i> Share</button>
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
                        <textarea name="body" rows="1" placeholder="Write your message..." required></textarea>
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

    <style>
        .forum-shell {
            --forum-navy-dark: #0a1628;
            --forum-navy-mid: #0f2b4b;
            --forum-gold: #c9a84c;
            --forum-gold-light: #f0d060;
            --forum-bg: #eef2f7;
            min-height: 100vh;
            background: var(--forum-bg);
            color: #14213d;
            font-family: "Inter", "Segoe UI", -apple-system, BlinkMacSystemFont, system-ui, sans-serif;
        }

        .forum-topbar {
            position: sticky;
            top: 0;
            z-index: 30;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 14px 24px;
            background: linear-gradient(135deg, var(--forum-navy-dark), var(--forum-navy-mid));
            border-bottom: 2px solid var(--forum-gold);
        }

        .forum-logo {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: #fff;
            font-weight: 900;
            font-size: 1.05rem;
        }

        .forum-logo i {
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--forum-gold), var(--forum-gold-light));
            color: var(--forum-navy-dark);
            font-size: 1.1rem;
        }

        .forum-logo span span { display: inline; color: var(--forum-gold); }
        .forum-logo small { display: block; color: #8ea5c1; font-size: .6rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; }

        .forum-topbar-right { display: flex; align-items: center; gap: 14px; }

        .forum-online-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 6px 12px;
            border-radius: 999px;
            background: rgba(34, 197, 94, .14);
            color: #4ade80;
            font-size: .74rem;
            font-weight: 800;
        }

        .forum-dot { width: 8px; height: 8px; border-radius: 50%; background: #64748b; flex: 0 0 auto; }
        .forum-dot.online { background: #22c55e; box-shadow: 0 0 0 3px rgba(34,197,94,.18); }
        .forum-dot.offline { background: #64748b; }

        .forum-user { display: inline-flex; align-items: center; gap: 10px; color: #fff; }
        .forum-user strong { font-size: .86rem; font-weight: 800; }

        .forum-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            background: var(--forum-navy-mid);
            color: #fff;
            font-weight: 900;
            font-size: .74rem;
        }

        .forum-avatar.gold { background: linear-gradient(135deg, var(--forum-gold), var(--forum-gold-light)); color: var(--forum-navy-dark); }

        .forum-role-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 999px;
            background: var(--student, #3b82f6);
            color: #fff;
            font-size: .64rem;
            font-weight: 900;
            letter-spacing: .4px;
        }

        .forum-role-badge[data-role="lecturer"] { background: var(--lecturer, #8b5cf6); }
        .forum-role-badge[data-role="admin"] { background: var(--admin, #ef4444); }
        .forum-role-badge[data-role="member"] { background: #64748b; }
        .forum-role-badge.small { padding: 2px 8px; font-size: .6rem; }

        .forum-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            min-height: 36px;
            padding: 0 14px;
            border: 0;
            border-radius: 999px;
            font-size: .78rem;
            font-weight: 800;
            cursor: pointer;
            white-space: nowrap;
        }

        .forum-btn.danger { background: rgba(239,68,68,.14); color: #fca5a5; }
        .forum-btn.gold { background: linear-gradient(135deg, var(--forum-gold), var(--forum-gold-light)); color: var(--forum-navy-dark); }
        .forum-btn.light { background: #fff; color: #14213d; border: 1px solid rgba(15,31,61,.12); }

        .forum-app { display: grid; grid-template-columns: 300px minmax(0, 1fr); align-items: start; }

        .forum-sidebar {
            position: sticky;
            top: 65px;
            height: calc(100vh - 65px);
            overflow-y: auto;
            padding: 18px;
            background: linear-gradient(180deg, var(--forum-navy-dark), var(--forum-navy-mid));
            color: #c9d6ea;
        }

        .forum-sidebar-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 10px; }
        .forum-sidebar-title { font-size: .68rem; font-weight: 900; letter-spacing: 1px; color: #8ea5c1; }
        .forum-sidebar-title i { color: var(--forum-gold); margin-right: 4px; }

        .forum-count-badge {
            padding: 3px 9px;
            border-radius: 999px;
            background: rgba(255,255,255,.08);
            color: var(--forum-gold-light);
            font-size: .62rem;
            font-weight: 900;
        }

        .forum-all-link { display: inline-flex; align-items: center; gap: 6px; color: #8ea5c1; font-size: .74rem; font-weight: 700; margin-bottom: 14px; }
        .forum-all-link:hover { color: var(--forum-gold-light); }

        .forum-groups { display: grid; gap: 6px; }

        .forum-group {
            display: block;
            border-radius: 14px;
            padding: 10px 12px;
            background: rgba(255,255,255,.05);
            color: #c9d6ea;
        }

        .forum-group-head, .forum-group.collapsed { display: flex; align-items: center; justify-content: space-between; gap: 8px; font-size: .8rem; font-weight: 800; }
        .forum-group.expanded { background: rgba(201,168,76,.08); }
        .forum-group-meta { display: inline-flex; align-items: center; gap: 5px; font-size: .64rem; color: #8ea5c1; font-weight: 700; }

        .forum-group-threads { display: grid; gap: 4px; margin-top: 8px; padding-left: 8px; }

        .forum-thread-link {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 10px;
            border-radius: 10px;
            color: #b9c8e0;
            font-size: .76rem;
            font-weight: 700;
        }

        .forum-thread-link span:nth-child(2) { flex: 1; }
        .forum-thread-link:hover { background: rgba(255,255,255,.06); }
        .forum-thread-link.active { background: rgba(201,168,76,.16); color: var(--forum-gold-light); }

        .forum-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 20px;
            height: 20px;
            padding: 0 6px;
            border-radius: 999px;
            background: rgba(255,255,255,.12);
            font-size: .64rem;
            font-weight: 900;
        }

        .forum-members { display: grid; gap: 8px; }

        .forum-member {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px;
            border-radius: 12px;
            background: rgba(255,255,255,.05);
        }

        .forum-member-info { flex: 1; display: grid; gap: 2px; font-size: .74rem; }
        .forum-member-info strong { color: #fff; }
        .forum-member-info span { color: #8ea5c1; font-size: .66rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; }

        .forum-muted { color: #7182a8; font-size: .8rem; font-weight: 650; }

        .forum-main { min-width: 0; padding: 24px 28px 60px; }

        .forum-alert { margin-bottom: 16px; padding: 12px 14px; border-radius: 12px; font-weight: 700; font-size: .84rem; }
        .forum-alert.success { background: rgba(34,197,94,.1); color: #15803d; }
        .forum-alert.error { background: rgba(239,68,68,.1); color: #b91c1c; }
        .forum-alert.error ul { margin: 0; padding-left: 18px; }

        .forum-search-bar { display: flex; gap: 10px; margin-bottom: 20px; }

        .forum-search {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 10px;
            height: 44px;
            padding: 0 16px;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 8px 20px rgba(15,31,61,.05);
            color: #7182a8;
        }

        .forum-search input { flex: 1; border: 0; outline: 0; background: transparent; font-weight: 700; color: #14213d; }

        #forum-search-scope {
            height: 44px;
            padding: 0 14px;
            border-radius: 14px;
            border: 0;
            background: #fff;
            box-shadow: 0 8px 20px rgba(15,31,61,.05);
            font-weight: 700;
            color: #14213d;
        }

        .forum-heading h1 { margin: 4px 0 0; font-size: 1.7rem; font-weight: 950; }
        .forum-heading h1 { color: var(--forum-navy-dark); }

        .forum-breadcrumb { margin: 8px 0 6px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; color: #7182a8; font-size: .78rem; font-weight: 700; }
        .forum-breadcrumb a { color: #7182a8; }
        .forum-breadcrumb a:hover { color: var(--forum-navy-dark); }
        .forum-breadcrumb i { font-size: .6rem; color: #b7c2d8; }

        .forum-meta { display: flex; gap: 18px; color: #7182a8; font-size: .8rem; font-weight: 700; margin-bottom: 18px; }

        .forum-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 18px; }
        .forum-report-form { display: grid; gap: 8px; margin-top: 10px; padding: 12px; border-radius: 12px; background: #fff; box-shadow: 0 8px 20px rgba(15,31,61,.06); width: min(360px, 90vw); }
        .forum-report-form textarea { border: 1px solid rgba(15,31,61,.12); border-radius: 10px; padding: 8px 10px; font: inherit; resize: vertical; }
        .forum-report { position: relative; }
        .forum-report .forum-report-form { position: absolute; z-index: 5; }

        .forum-quiz-banner {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px 18px;
            margin-bottom: 20px;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 10px 26px rgba(15,31,61,.06);
            border-left: 4px solid var(--forum-gold);
        }

        .forum-quiz-icon {
            width: 44px;
            height: 44px;
            display: grid;
            place-items: center;
            border-radius: 12px;
            background: rgba(201,168,76,.14);
            color: var(--forum-gold);
        }

        .forum-quiz-info { flex: 1; display: grid; gap: 3px; }
        .forum-quiz-info span { color: #7182a8; font-size: .78rem; font-weight: 700; }

        .forum-quiz-countdown {
            padding: 8px 14px;
            border-radius: 12px;
            background: #f1f5f9;
            font-weight: 900;
            font-variant-numeric: tabular-nums;
            color: var(--forum-navy-dark);
        }

        .forum-messages { display: grid; gap: 14px; margin-bottom: 20px; }

        .forum-message { display: flex; gap: 12px; align-items: flex-start; padding: 14px; border-radius: 16px; background: #fff; box-shadow: 0 6px 18px rgba(15,31,61,.04); }
        .forum-message .forum-avatar { background: var(--forum-navy-mid); }

        .forum-message-body { flex: 1; min-width: 0; }
        .forum-message-head { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 4px; }
        .forum-message-body p { margin: 0; color: #2a3a5c; line-height: 1.6; }

        .forum-topic-chip { padding: 2px 8px; border-radius: 999px; background: #eef6ff; color: #2563eb; font-size: .64rem; font-weight: 800; }

        .forum-composer { padding: 16px; border-radius: 18px; background: #fff; box-shadow: 0 10px 26px rgba(15,31,61,.06); }
        .forum-composer-topic { width: 100%; border: 1px solid rgba(15,31,61,.1); border-radius: 12px; padding: 10px 14px; margin-bottom: 10px; font: inherit; }
        .forum-composer-row { display: flex; gap: 10px; align-items: flex-end; }
        .forum-composer-row textarea { flex: 1; min-height: 46px; border: 1px solid rgba(15,31,61,.1); border-radius: 12px; padding: 12px 14px; font: inherit; resize: vertical; }
        .forum-composer-footer { display: flex; align-items: center; justify-content: space-between; margin-top: 10px; }

        .forum-exclude { position: relative; }
        .forum-exclude-list { position: absolute; bottom: calc(100% + 8px); left: 0; z-index: 5; display: grid; gap: 6px; width: 240px; padding: 12px; border-radius: 12px; background: #fff; box-shadow: 0 10px 26px rgba(15,31,61,.12); }
        .forum-exclude-list label { display: flex; align-items: center; gap: 8px; font-size: .8rem; font-weight: 650; }

        [data-forum-hidden] { display: none !important; }

        @media print {
            .forum-topbar, .forum-sidebar, .forum-search-bar, .forum-actions, .forum-composer { display: none !important; }
            .forum-app { display: block; }
            .forum-main { padding: 0; }
        }

        @media (max-width: 900px) {
            .forum-app { grid-template-columns: 1fr; }
            .forum-sidebar { position: static; height: auto; }
        }
    </style>

    <script>
        (function () {
            const searchInput = document.getElementById('forum-search-input');
            const searchScope = document.getElementById('forum-search-scope');
            const searchBtn = document.getElementById('forum-search-btn');

            function runSearch() {
                const term = (searchInput.value || '').trim().toLowerCase();
                const scope = searchScope.value;

                document.querySelectorAll('[data-forum-search-text]').forEach((el) => {
                    const inScope = scope === 'all' || el.closest(`[data-forum-search-scope="${scope}"]`);
                    const matches = !term || el.getAttribute('data-forum-search-text').includes(term);
                    el.toggleAttribute('data-forum-hidden', !(inScope && matches));
                });
            }

            searchInput?.addEventListener('input', runSearch);
            searchScope?.addEventListener('change', runSearch);
            searchBtn?.addEventListener('click', runSearch);

            const exportBtn = document.getElementById('forum-export-btn');
            exportBtn?.addEventListener('click', () => window.print());

            const shareBtn = document.getElementById('forum-share-btn');
            shareBtn?.addEventListener('click', async () => {
                try {
                    await navigator.clipboard.writeText(window.location.href);
                    const original = shareBtn.innerHTML;
                    shareBtn.innerHTML = '<i class="fas fa-check"></i> Copied!';
                    setTimeout(() => { shareBtn.innerHTML = original; }, 1800);
                } catch (e) {
                    window.prompt('Copy this link:', window.location.href);
                }
            });

            const syncBtn = document.getElementById('forum-sync-btn');
            syncBtn?.addEventListener('click', async () => {
                const original = syncBtn.innerHTML;
                syncBtn.innerHTML = '<i class="fas fa-arrows-rotate fa-spin"></i> Syncing...';
                try {
                    const res = await fetch(window.location.href, { headers: { 'X-Sync': '1' } });
                    const html = await res.text();
                    const doc = new DOMParser().parseFromString(html, 'text/html');

                    ['forum-messages', 'forum-meta', 'forum-quiz-banner'].forEach((id) => {
                        const fresh = doc.getElementById(id);
                        const current = document.getElementById(id);
                        if (fresh && current) {
                            current.replaceWith(fresh);
                        } else if (!fresh && current) {
                            current.remove();
                        }
                    });

                    tickQuizCountdown();
                } finally {
                    syncBtn.innerHTML = original;
                }
            });

            function tickQuizCountdown() {
                const countdownEl = document.getElementById('forum-quiz-countdown');
                const banner = document.getElementById('forum-quiz-banner');
                if (!countdownEl || !banner) return;

                const target = new Date(banner.getAttribute('data-scheduled-at')).getTime();
                const diff = target - Date.now();
                if (diff <= 0) {
                    countdownEl.textContent = 'Live now';
                    return;
                }
                const totalSeconds = Math.floor(diff / 1000);
                const hours = Math.floor(totalSeconds / 3600);
                const minutes = Math.floor((totalSeconds % 3600) / 60);
                const seconds = totalSeconds % 60;
                countdownEl.textContent = hours > 0
                    ? `${hours}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`
                    : `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            }

            tickQuizCountdown();
            setInterval(tickQuizCountdown, 1000);

            const excludeCount = document.getElementById('forum-exclude-count');
            document.querySelectorAll('.forum-exclude-checkbox').forEach((checkbox) => {
                checkbox.addEventListener('change', () => {
                    const checked = document.querySelectorAll('.forum-exclude-checkbox:checked').length;
                    if (excludeCount) excludeCount.textContent = checked;
                });
            });
        })();
    </script>
</x-layouts.academic-pulse>
