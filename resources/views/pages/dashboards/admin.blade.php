<x-layouts.academic-pulse title="Quiz Management">
    <div class="pulse-page">
        <div class="pulse-app">
            <aside class="pulse-sidebar">
                <a class="pulse-logo" href="{{ route('home') }}">
                    <i class="fas fa-graduation-cap"></i>
                    <span>Academic<span>Pulse Forum</span></span>
                </a>

                <nav class="pulse-menu" aria-label="Admin navigation">
                    <a class="active" href="#"><i class="fas fa-house"></i> Dashboard</a>
                    <a href="#"><i class="fas fa-chalkboard-user"></i> Lectures</a>
                    <a href="#"><i class="fas fa-calendar-days"></i> Schedule</a>
                    <a href="#"><i class="fas fa-message"></i> Messages</a>
                    <a href="#"><i class="fas fa-users"></i> Students</a>
                    <a href="#"><i class="fas fa-chart-line"></i> Analytics</a>
                    <a href="#"><i class="fas fa-gear"></i> Settings</a>
                </nav>

                <div class="pulse-sidebar-footer">
                    <div class="pulse-user">
                        <span class="pulse-avatar">{{ strtoupper(substr($user->name ?? 'AC', 0, 2)) }}</span>
                        <span><strong>{{ $user->name ?? 'Admin' }}</strong><span>{{ $user->role_label ?? 'Administrator' }}</span></span>
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
                        <h1>Quiz Settings & Management</h1>
                        <p>Create, manage and analyze quizzes for your students.</p>
                    </div>
                    <div class="pulse-tools">
                        <button class="pulse-btn"><i class="fas fa-plus"></i> Create Quiz</button>
                    </div>
                </header>

                <section class="pulse-grid pulse-stats">
                    <article class="pulse-card pulse-stat">
                        <span class="pulse-stat-icon"><i class="fas fa-clipboard-list"></i></span>
                        <span><small>Total Quizzes</small><b>{{ $stats['total_quizzes'] }}</b><span class="pulse-trend">+15% this month</span></span>
                    </article>
                    <article class="pulse-card pulse-stat">
                        <span class="pulse-stat-icon green"><i class="fas fa-calendar-check"></i></span>
                        <span><small>Published Quizzes</small><b>{{ $stats['published_quizzes'] }}</b><span class="pulse-muted">{{ $stats['total_quizzes'] > 0 ? round(($stats['published_quizzes'] / $stats['total_quizzes']) * 100) : 0 }}% of total</span></span>
                    </article>
                    <article class="pulse-card pulse-stat">
                        <span class="pulse-stat-icon purple"><i class="fas fa-layer-group"></i></span>
                        <span><small>Total Attempts</small><b>{{ $stats['total_attempts'] }}</b><span class="pulse-trend">+15% this month</span></span>
                    </article>
                    <article class="pulse-card pulse-stat">
                        <span class="pulse-stat-icon green"><i class="fas fa-bullseye"></i></span>
                        <span><small>Average Score</small><b>{{ $stats['average_score'] }}</b><span class="pulse-trend">+6% this month</span></span>
                    </article>
                </section>

                <section class="pulse-card pulse-pad" style="margin-top:22px;">
                    <div class="pulse-section-head">
                        <div class="pulse-tools" style="gap:6px;">
                            <span class="pulse-tag">All Quizzes</span>
                            <span class="pulse-tag gray">Published</span>
                            <span class="pulse-tag gray">Drafts</span>
                            <span class="pulse-tag gray">Scheduled</span>
                        </div>
                        <div class="pulse-tools">
                            <label class="pulse-search">
                                <i class="fas fa-magnifying-glass"></i>
                                <input type="search" placeholder="Search quizzes...">
                            </label>
                            <button class="pulse-btn light"><i class="fas fa-filter"></i> Filters</button>
                        </div>
                    </div>

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
                                        <td>-</td>
                                        <td>-</td>
                                        <td><span class="pulse-tag {{ $quiz->status === 'active' ? 'green' : ($quiz->status === 'scheduled' ? 'orange' : 'gray') }}">{{ ucfirst(str_replace('_', ' ', $quiz->status)) }}</span></td>
                                        <td><div class="pulse-actions"><span class="pulse-icon-btn" style="width:32px;height:32px;"><i class="fas fa-ellipsis"></i></span></div></td>
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
                        <span class="pulse-muted">Showing 1 to 5 of 24 quizzes</span>
                        <div class="pulse-actions">
                            <span class="pulse-icon-btn" style="width:34px;height:34px;"><i class="fas fa-chevron-left"></i></span>
                            <span class="pulse-tag">1</span>
                            <span class="pulse-tag gray">2</span>
                            <span class="pulse-tag gray">3</span>
                            <span class="pulse-tag gray">5</span>
                            <span class="pulse-icon-btn" style="width:34px;height:34px;"><i class="fas fa-chevron-right"></i></span>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>
</x-layouts.academic-pulse>
