<x-layouts.academic-pulse title="Lecturer Dashboard">
    <div class="pulse-page">
        <div class="pulse-app">
            <aside class="pulse-sidebar">
                <a class="pulse-logo" href="{{ route('home') }}">
                    <i class="fas fa-graduation-cap"></i>
                    <span>Academic<span>Pulse Forum</span></span>
                </a>

                <nav class="pulse-menu" aria-label="Lecturer navigation">
                    <a class="active" href="{{ route('lecturer.dashboard') }}"><i class="fas fa-house"></i> Dashboard</a>
                    <a href="{{ route('messages.index') }}"><i class="fas fa-message"></i> Messages</a>
                    <a href="{{ route('lecturer.students') }}"><i class="fas fa-users"></i> Students</a>
                    <a href="{{ route('questions.index') }}"><i class="fas fa-circle-question"></i> Discussion Forum</a>
                    <a href="{{ route('lecturer.marks') }}"><i class="fas fa-chart-line"></i> Student Marks</a>
                    <a href="{{ route('profile.edit') }}"><i class="fas fa-gear"></i> Settings</a>
                </nav>

                <div class="pulse-sidebar-footer">
                    <div class="pulse-user">
                        <span class="pulse-avatar">{{ strtoupper(substr($user->name ?? 'LC', 0, 2)) }}</span>
                        <span><strong>{{ $user->name ?? 'Lecturer' }}</strong><span>{{ $user->role_label ?? 'Lecturer' }}</span></span>
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
                        <h1>{{ match (true) {
                            now()->hour < 12 => 'Good morning',
                            now()->hour < 17 => 'Good afternoon',
                            default => 'Good evening',
                        } }}, {{ explode(' ', trim($user->name ?? 'Lecturer'))[0] }}</h1>
                        <p>Here is what is happening with your lectures today.</p>
                    </div>
                    <div class="pulse-tools">
                        <label class="pulse-search">
                            <i class="fas fa-magnifying-glass"></i>
                            <input type="search" placeholder="Search anything...">
                        </label>
                        @include('partials._notification-bell')
                        <span class="pulse-avatar">AC</span>
                    </div>
                </header>

                <section class="pulse-grid pulse-stats">
                    <article class="pulse-card pulse-stat">
                        <span class="pulse-stat-icon green"><i class="fas fa-clipboard-check"></i></span>
                        <span><small>Quizzes</small><b>{{ $stats['quizzes'] }}</b><span class="pulse-trend">+15% this month</span></span>
                    </article>
                    <article class="pulse-card pulse-stat">
                        <span class="pulse-stat-icon"><i class="fas fa-book-open"></i></span>
                        <span><small>Total Lectures</small><b>128</b><span class="pulse-trend">+12% this month</span></span>
                    </article>
                    <article class="pulse-card pulse-stat">
                        <span class="pulse-stat-icon purple"><i class="fas fa-users"></i></span>
                        <span><small>Students</small><b>{{ $stats['students'] }}</b><span class="pulse-trend">+8% this month</span></span>
                    </article>
                    <article class="pulse-card pulse-stat">
                        <span class="pulse-stat-icon orange"><i class="fas fa-calendar"></i></span>
                        <span><small>Upcoming Classes</small><b>6</b><span class="pulse-trend">Today</span></span>
                    </article>
                </section>

                <section class="pulse-grid" style="margin-top:18px; grid-template-columns: repeat(2, minmax(0, 1fr));">
                    <article class="pulse-card pulse-pad">
                        <div class="pulse-section-head">
                            <h2>Quiz Management</h2>
                            <a href="{{ route('quizzes.index') }}">Open</a>
                        </div>
                        <div class="pulse-list">
                            <div class="pulse-row"><span class="pulse-soft-icon"><i class="fas fa-clipboard-question"></i></span><span><strong>{{ $stats['active_quizzes'] }} active quizzes</strong><p>{{ $stats['published_this_week'] }} published this week</p></span><span class="pulse-tag green">Ready</span></div>
                            <div class="pulse-row"><a href="{{ route('quizzes.create') }}" style="display: contents;"><span class="pulse-soft-icon"><i class="fas fa-plus"></i></span><span><strong>Create new quiz</strong><p>Bulk import from templates</p></span><span class="pulse-tag">New</span></a></div>
                        </div>
                    </article>

                    <article class="pulse-card pulse-pad">
                        <div class="pulse-section-head">
                            <h2>Submissions</h2>
                            <a href="#">Review</a>
                        </div>
                        <div class="pulse-list">
                            <div class="pulse-row"><span class="pulse-soft-icon"><i class="fas fa-file-lines"></i></span><span><strong>186 submissions</strong><p>42 pending review</p></span><span class="pulse-tag orange">Pending</span></div>
                            <div class="pulse-row"><span class="pulse-soft-icon"><i class="fas fa-check-double"></i></span><span><strong>82% reviewed</strong><p>Average turnaround 2h</p></span><span class="pulse-tag green">On track</span></div>
                        </div>
                    </article>

                    <article class="pulse-card pulse-pad">
                        <div class="pulse-section-head">
                            <h2>Questions</h2>
                            <a href="{{ route('questions.index') }}">View all</a>
                        </div>
                        @if ($unansweredQuestionsCount > 0)
                            <div class="pulse-row" style="margin-bottom:10px;">
                                <span class="pulse-soft-icon"><i class="fas fa-triangle-exclamation"></i></span>
                                <span><strong>{{ $unansweredQuestionsCount }} question(s) awaiting an answer</strong><p>Help by replying below.</p></span>
                                <span class="pulse-tag orange">Reminder</span>
                            </div>
                        @endif
                        <div class="pulse-list">
                            @forelse ($recentQuestions as $question)
                                <a href="{{ route('questions.show', $question) }}" style="display:contents;">
                                    <div class="pulse-row">
                                        <span class="pulse-soft-icon"><i class="fas fa-circle-question"></i></span>
                                        <span><strong>{{ $question->title }}</strong><p>{{ $question->topic->title ?? 'Other' }} &middot; asked by {{ $question->user->name }}</p></span>
                                        @if ($question->answers_count > 0)
                                            <span class="pulse-tag green">Answered</span>
                                        @else
                                            <span class="pulse-tag orange">Not answered</span>
                                        @endif
                                    </div>
                                </a>
                            @empty
                                <div class="pulse-row"><span class="pulse-soft-icon"><i class="fas fa-circle-question"></i></span><span><strong>No questions yet</strong><p>Questions from students will appear here.</p></span></div>
                            @endforelse
                        </div>
                        <div style="margin-top:12px;">
                            <a href="{{ route('questions.index') }}" class="pulse-btn light" style="width:100%; text-decoration:none; text-align:center;"><i class="fas fa-plus"></i> Ask a question</a>
                        </div>
                    </article>

                    <article class="pulse-card pulse-pad">
                        <div class="pulse-section-head">
                            <h2>Quiz Status Breakdown</h2>
                            <a href="{{ route('quizzes.index') }}">Open</a>
                        </div>
                        <div class="pulse-bars">
                            @foreach ($quizzesByStatus as $row)
                                <div class="pulse-bar-row">
                                    <span class="pulse-bar-label">{{ $row->label }}</span>
                                    <span class="pulse-bar-track"><span class="pulse-bar-fill {{ $row->total > 0 ? 'has-value' : '' }}" style="width: {{ $row->pct }}%;"></span></span>
                                    <span class="pulse-bar-value">{{ $row->total }}</span>
                                </div>
                            @endforeach
                        </div>
                    </article>

                    <article class="pulse-card pulse-pad">
                        <div class="pulse-section-head">
                            <h2>Performance</h2>
                            <a href="#">Details</a>
                        </div>
                        <div class="pulse-list">
                            <div class="pulse-row"><span class="pulse-soft-icon"><i class="fas fa-award"></i></span><span><strong>Avg. score 78%</strong><p>Up 6% from last week</p></span><span class="pulse-tag green">Strong</span></div>
                            <div class="pulse-row"><span class="pulse-soft-icon"><i class="fas fa-chart-line"></i></span><span><strong>Attendance 91%</strong><p>Steady engagement</p></span><span class="pulse-tag">Stable</span></div>
                        </div>
                    </article>

                    <article class="pulse-card pulse-pad">
                        <div class="pulse-section-head">
                            <h2>Discussions</h2>
                            <a href="#">Open</a>
                        </div>
                        <div class="pulse-list">
                            <div class="pulse-row"><span class="pulse-soft-icon"><i class="fas fa-comments"></i></span><span><strong>12 new threads</strong><p>6 unresolved questions</p></span><span class="pulse-tag orange">Active</span></div>
                            <div class="pulse-row"><span class="pulse-soft-icon"><i class="fas fa-user-group"></i></span><span><strong>48 participants</strong><p>Top topic: assignment tips</p></span><span class="pulse-tag">Hot</span></div>
                        </div>
                    </article>
                </section>

                <section class="pulse-card pulse-pad" style="margin-top:18px;">
                    <div class="pulse-section-head">
                        <h2>Participation Criteria</h2>
                    </div>
                    <p class="pulse-muted" style="margin:-6px 0 16px;">Set how many points students earn per forum activity. These points are used to score participation across your topics.</p>

                    @if (session('success'))
                        <div class="pulse-card pulse-pad" style="margin-bottom:16px; color: var(--pulse-green, #1a7f37);">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form class="pulse-form" method="POST" action="{{ route('lecturer.participation-criteria.update') }}">
                        @csrf
                        @method('PATCH')
                        <div class="pulse-form-row" style="display:grid; grid-template-columns: repeat(4, 1fr); gap:16px;">
                            <div class="pulse-field">
                                <label for="points_per_question">Points per question asked</label>
                                <div class="pulse-input">
                                    <input id="points_per_question" type="number" min="0" max="100" name="points_per_question" value="{{ old('points_per_question', $participationCriteria->points_per_question) }}" required>
                                </div>
                                @error('points_per_question')<span style="color:#d33;font-size:.75rem;">{{ $message }}</span>@enderror
                            </div>
                            <div class="pulse-field">
                                <label for="points_per_answer">Points per answer given</label>
                                <div class="pulse-input">
                                    <input id="points_per_answer" type="number" min="0" max="100" name="points_per_answer" value="{{ old('points_per_answer', $participationCriteria->points_per_answer) }}" required>
                                </div>
                                @error('points_per_answer')<span style="color:#d33;font-size:.75rem;">{{ $message }}</span>@enderror
                            </div>
                            <div class="pulse-field">
                                <label for="points_per_like_received">Points per like received</label>
                                <div class="pulse-input">
                                    <input id="points_per_like_received" type="number" min="0" max="100" name="points_per_like_received" value="{{ old('points_per_like_received', $participationCriteria->points_per_like_received) }}" required>
                                </div>
                                @error('points_per_like_received')<span style="color:#d33;font-size:.75rem;">{{ $message }}</span>@enderror
                            </div>
                            <div class="pulse-field">
                                <label for="target_points">Points for 100% score</label>
                                <div class="pulse-input">
                                    <input id="target_points" type="number" min="1" max="1000" name="target_points" value="{{ old('target_points', $participationCriteria->target_points) }}" required>
                                </div>
                                @error('target_points')<span style="color:#d33;font-size:.75rem;">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div>
                            <button class="pulse-btn" type="submit"><i class="fas fa-floppy-disk"></i> Save criteria</button>
                        </div>
                    </form>
                </section>
            </main>
        </div>
    </div>
</x-layouts.academic-pulse>
