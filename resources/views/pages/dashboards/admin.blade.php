<x-layouts.academic-pulse title="Quiz Management">
    <div class="pulse-page">
        <div class="pulse-app">
            <aside class="pulse-sidebar">
                <a class="pulse-logo" href="{{ route('home') }}">
                    <i class="fas fa-graduation-cap"></i>
                    <span>Academic<span>Pulse Forum</span></span>
                </a>

                <nav class="pulse-menu" aria-label="Admin navigation">
                    <a class="active" href="{{ route('admin.dashboard') }}"><i class="fas fa-house"></i> Dashboard</a>
                    <a href="{{ route('admin.topics.index') }}"><i class="fas fa-book"></i> Topics</a>
                    <a href="{{ route('questions.index') }}"><i class="fas fa-circle-question"></i> Discussion Forum</a>
                    <a href="{{ route('admin.complaints.index') }}"><i class="fas fa-flag"></i> Complaints</a>
                    <a href="{{ route('admin.members.index') }}"><i class="fas fa-user-shield"></i> Members</a>
                    <a href="{{ route('messages.index') }}"><i class="fas fa-message"></i> Messages</a>
                    <a href="{{ route('admin.analytics.index') }}"><i class="fas fa-chart-line"></i> Analytics</a>
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
                </div>
            </aside>

            <main class="pulse-main">
                <header class="pulse-topbar">
                    <div class="pulse-title">
                        <h1>Monitoring & Management</h1>
                        <p>Create, manage and analyze quizzes for your students.</p>
                    </div>
                    <div class="pulse-tools">
                        <a href="{{ route('quizzes.create') }}" class="pulse-btn" style="text-decoration:none;"><i class="fas fa-plus"></i> Create Quiz</a>
                    </div>
                </header>

                <section class="pulse-grid pulse-stats" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
                    <a class="pulse-card pulse-stat" href="{{ route('admin.topics.index') }}" style="text-decoration:none;">
                        <span class="pulse-stat-icon"><i class="fas fa-book"></i></span>
                        <span><small>Topics</small><b>{{ $bubbles['topics'] }}</b><span class="pulse-muted">{{ $bubbles['unassigned_topics'] }} unassigned</span></span>
                    </a>
                    <a class="pulse-card pulse-stat" href="{{ route('questions.index') }}" style="text-decoration:none;">
                        <span class="pulse-stat-icon orange"><i class="fas fa-circle-question"></i></span>
                        <span><small>Questions</small><b>{{ $bubbles['questions'] }}</b><span class="pulse-muted">{{ $bubbles['unanswered_questions'] }} unanswered</span></span>
                    </a>
                    <a class="pulse-card pulse-stat" href="{{ route('admin.complaints.index') }}" style="text-decoration:none;">
                        <span class="pulse-stat-icon" style="color:#d33;"><i class="fas fa-flag"></i></span>
                        <span><small>Complaints</small><b>{{ $bubbles['pending_complaints'] }}</b><span class="pulse-muted">pending review</span></span>
                    </a>
                    <a class="pulse-card pulse-stat" href="{{ route('quizzes.index') }}" style="text-decoration:none;">
                        <span class="pulse-stat-icon green"><i class="fas fa-clipboard-list"></i></span>
                        <span><small>Quizzes</small><b>{{ $bubbles['quizzes'] }}</b><span class="pulse-muted">{{ $bubbles['published_quizzes'] }} published</span></span>
                    </a>
                    <a class="pulse-card pulse-stat" href="{{ route('admin.members.index') }}" style="text-decoration:none;">
                        <span class="pulse-stat-icon purple"><i class="fas fa-user-graduate"></i></span>
                        <span><small>Students</small><b>{{ $bubbles['students'] }}</b><span class="pulse-muted">enrolled</span></span>
                    </a>
                    <a class="pulse-card pulse-stat" href="{{ route('admin.members.index') }}" style="text-decoration:none;">
                        <span class="pulse-stat-icon purple"><i class="fas fa-chalkboard-user"></i></span>
                        <span><small>Lecturers</small><b>{{ $bubbles['lecturers'] }}</b><span class="pulse-muted">teaching</span></span>
                    </a>
                </section>

                <section class="pulse-card pulse-pad" style="margin-top:22px;">
                    <form method="GET" action="{{ route('admin.dashboard') }}" class="pulse-section-head">
                        <div class="pulse-tools" style="gap:6px;">
                            <a href="{{ route('admin.dashboard') }}" class="pulse-tag {{ ! $statusFilter ? '' : 'gray' }}">All Quizzes</a>
                            <a href="{{ route('admin.dashboard', ['status' => 'published']) }}" class="pulse-tag {{ $statusFilter === 'published' ? '' : 'gray' }}">Published</a>
                            <a href="{{ route('admin.dashboard', ['status' => 'draft']) }}" class="pulse-tag {{ $statusFilter === 'draft' ? '' : 'gray' }}">Drafts</a>
                            <a href="{{ route('admin.dashboard', ['status' => 'scheduled']) }}" class="pulse-tag {{ $statusFilter === 'scheduled' ? '' : 'gray' }}">Scheduled</a>
                        </div>
                        <div class="pulse-tools">
                            <label class="pulse-search">
                                <i class="fas fa-magnifying-glass"></i>
                                <input type="search" name="q" value="{{ $search }}" placeholder="Search quizzes...">
                            </label>
                            @if ($statusFilter)
                                <input type="hidden" name="status" value="{{ $statusFilter }}">
                            @endif
                            <button type="submit" class="pulse-btn light"><i class="fas fa-magnifying-glass"></i> Search</button>
                        </div>
                    </form>

                    <div style="overflow:auto;">
                        <table class="pulse-table">
                            <thead>
                                <tr>
                                    <th>Quiz Title</th>
                                    <th>Lecture</th>
                                    <th>Questions</th>
                                    <th>Duration</th>
                                    <th>Attempts</th>
                                    <th>Avg. Score</th>
                                    <th>Status</th>
                                    <th style="text-align:right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($quizzes as $quiz)
                                    <tr>
                                        <td><strong>{{ $quiz->title }}</strong></td>
                                        <td>{{ $quiz->subject }}</td>
                                        <td>{{ $quiz->total_questions }}</td>
                                        <td>{{ $quiz->duration_minutes }} mins</td>
                                        <td>{{ $quiz->attempts_count }}</td>
                                        <td>{{ $quiz->average_score_percent !== null ? $quiz->average_score_percent.'%' : '—' }}</td>
                                        <td><span class="pulse-tag {{ $quiz->stage() === 'active' ? 'green' : ($quiz->stage() === 'due_soon' ? 'orange' : 'gray') }}">{{ ucfirst(str_replace('_', ' ', $quiz->stage())) }}</span></td>
                                        <td><div class="pulse-actions"><a href="{{ route('quizzes.result', $quiz) }}" class="pulse-icon-btn" style="width:32px;height:32px;" title="View marks report"><i class="fas fa-eye"></i></a></div></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="pulse-muted">No quizzes available yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="pulse-section-head" style="margin:18px 0 0;">
                        <span class="pulse-muted">Showing {{ $quizzes->count() }} most recent quiz(zes)</span>
                        <a class="pulse-btn light" href="{{ route('quizzes.index') }}">View all quizzes</a>
                    </div>
                </section>
            </main>
        </div>
    </div>
</x-layouts.academic-pulse>
