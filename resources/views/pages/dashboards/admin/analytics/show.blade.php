<x-layouts.academic-pulse title="{{ $topic->title }} Statistics">
    <div class="pulse-page">
        <div class="pulse-app">
            <aside class="pulse-sidebar">
                <a class="pulse-logo" href="{{ route('home') }}">
                    <i class="fas fa-graduation-cap"></i>
                    <span>Academic<span>Pulse Forum</span></span>
                </a>

                <nav class="pulse-menu" aria-label="Admin navigation">
                    <a href="{{ route('admin.dashboard') }}"><i class="fas fa-house"></i> Dashboard</a>
                    <a href="{{ route('admin.topics.index') }}"><i class="fas fa-book"></i> Topics</a>
                    <a href="{{ route('questions.index') }}"><i class="fas fa-circle-question"></i> Discussion Forum</a>
                    <a href="{{ route('admin.complaints.index') }}"><i class="fas fa-flag"></i> Complaints</a>
                    <a href="{{ route('admin.members.index') }}"><i class="fas fa-user-shield"></i> Members</a>
                    <a href="{{ route('messages.index') }}"><i class="fas fa-message"></i> Messages</a>
                    <a class="active" href="{{ route('admin.analytics.index') }}"><i class="fas fa-chart-line"></i> Analytics</a>
                    <a href="{{ route('profile.edit') }}"><i class="fas fa-gear"></i> Settings</a>
                </nav>

                <div class="pulse-sidebar-footer">
                    <div class="pulse-user">
                        <span class="pulse-avatar">{{ strtoupper(substr($user->name ?? 'AC', 0, 2)) }}</span>
                        <span><strong>{{ $user->name ?? 'Admin' }}</strong><span>{{ $user->roleLabel() ?? 'Administrator' }}</span></span>
                    </div>
                    <div class="pulse-theme-panel" role="group" aria-label="Theme selector">
                        <button type="button" class="pulse-theme-btn active" data-theme="light"><i class="fas fa-sun"></i> Light</button>
                        <button type="button" class="pulse-theme-btn" data-theme="dark"><i class="fas fa-moon"></i> Dark</button>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" style="margin-top:12px;">
                        @csrf
                        <button type="submit" class="pulse-btn light" style="width:100%;"><i class="fas fa-arrow-right-from-bracket"></i> Log out</button>
                    </form>
                </div>
            </aside>

            <main class="pulse-main">
                <header class="pulse-topbar">
                    <div class="pulse-title">
                        <h1><a href="{{ route('admin.analytics.index') }}" style="color:inherit;"><i class="fas fa-arrow-left" style="font-size:.8em; margin-right:8px;"></i></a>{{ $topic->title }}</h1>
                        <p>Statistics for this group only &middot; led by {{ $topic->lecturer->name ?? 'an unassigned lecturer' }}</p>
                    </div>
                </header>

                <section class="pulse-grid pulse-stats" style="grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));">
                    <article class="pulse-card pulse-stat">
                        <span class="pulse-stat-icon purple"><i class="fas fa-users"></i></span>
                        <span><small>Subscribers</small><b>{{ $summary['subscribers'] }}</b></span>
                    </article>
                    <article class="pulse-card pulse-stat">
                        <span class="pulse-stat-icon orange"><i class="fas fa-circle-question"></i></span>
                        <span><small>Questions</small><b>{{ $summary['questions'] }}</b><span class="pulse-muted">{{ $summary['unanswered_questions'] }} unanswered</span></span>
                    </article>
                    <article class="pulse-card pulse-stat">
                        <span class="pulse-stat-icon green"><i class="fas fa-reply"></i></span>
                        <span><small>Answers</small><b>{{ $summary['answers'] }}</b></span>
                    </article>
                    <article class="pulse-card pulse-stat">
                        <span class="pulse-stat-icon" style="color:#d33;"><i class="fas fa-flag"></i></span>
                        <span><small>Complaints</small><b>{{ $summary['pending_complaints'] }}</b><span class="pulse-muted">pending</span></span>
                    </article>
                    <article class="pulse-card pulse-stat">
                        <span class="pulse-stat-icon"><i class="fas fa-clipboard-check"></i></span>
                        <span><small>Quizzes</small><b>{{ $summary['quizzes'] }}</b></span>
                    </article>
                    <article class="pulse-card pulse-stat">
                        <span class="pulse-stat-icon green"><i class="fas fa-chart-simple"></i></span>
                        <span><small>Avg. quiz score</small><b>{{ $summary['average_quiz_score'] !== null ? $summary['average_quiz_score'].'%' : '—' }}</b></span>
                    </article>
                </section>

                <section class="pulse-grid" style="margin-top:18px; grid-template-columns: repeat(2, minmax(0, 1fr));">
                    <article class="pulse-card pulse-pad">
                        <div class="pulse-section-head">
                            <h2>Most Active Students</h2>
                            <span class="pulse-muted">in this group, by questions asked</span>
                        </div>
                        <div class="pulse-list">
                            @forelse ($topAskers as $index => $student)
                                <div class="pulse-row">
                                    <span class="pulse-soft-icon">{{ $index + 1 }}</span>
                                    <span><strong>{{ $student->name }}</strong><p>{{ $student->email }}</p></span>
                                    <span class="pulse-tag">{{ $student->questions_count }} question(s)</span>
                                </div>
                            @empty
                                <div class="pulse-row"><span class="pulse-muted">No questions asked yet.</span></div>
                            @endforelse
                        </div>
                    </article>

                    <article class="pulse-card pulse-pad">
                        <div class="pulse-section-head">
                            <h2>Most Helpful Repliers</h2>
                            <span class="pulse-muted">in this group, by answers given</span>
                        </div>
                        <div class="pulse-list">
                            @forelse ($topAnswerers as $index => $person)
                                <div class="pulse-row">
                                    <span class="pulse-soft-icon">{{ $index + 1 }}</span>
                                    <span><strong>{{ $person->name }}</strong><p>{{ $person->roleLabel() }}</p></span>
                                    <span class="pulse-tag green">{{ $person->answers_count }} answer(s)</span>
                                </div>
                            @empty
                                <div class="pulse-row"><span class="pulse-muted">No answers posted yet.</span></div>
                            @endforelse
                        </div>
                    </article>
                </section>
            </main>
        </div>
    </div>
</x-layouts.academic-pulse>
