<x-layouts.academic-pulse title="Student Dashboard">
    <div class="pulse-page">
        <div class="pulse-app">
            <aside class="pulse-sidebar">
                <a class="pulse-logo" href="{{ route('home') }}">
                    <i class="fas fa-graduation-cap"></i>
                    <span>Academic<span>Pulse Forum</span></span>
                </a>

                <nav class="pulse-menu" aria-label="Student navigation">
                    <a class="active" href="{{ route('student.dashboard') }}"><i class="fas fa-house"></i> Dashboard</a>
                    <a href="{{ route('messages.index') }}"><i class="fas fa-message"></i> Messages</a>
                    <a href="{{ route('topics.index') }}"><i class="fas fa-book"></i> Topics</a>
                    <a href="{{ route('questions.index') }}"><i class="fas fa-circle-question"></i> Discussion Forum</a>
                    <a href="#"><i class="fas fa-gear"></i> Settings</a>
                </nav>

                <div class="pulse-sidebar-footer">
                    <div class="pulse-user">
                        <span class="pulse-avatar">{{ strtoupper(substr($user->name ?? 'U', 0, 2)) }}</span>
                        <span><strong>{{ $user->name ?? 'Student' }}</strong><span>{{ $user->role_label ?? 'Student' }}</span></span>
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
                        } }}, {{ explode(' ', trim($user->name ?? 'Student'))[0] }}</h1>
                        <p>Here is what is happening with your learning today.</p>
                    </div>
                    <div class="pulse-tools">
                        <label class="pulse-search">
                            <i class="fas fa-magnifying-glass"></i>
                            <input type="search" placeholder="Search anything...">
                        </label>
                        <span class="pulse-icon-btn"><i class="fas fa-bell"></i></span>
                        <span class="pulse-avatar">AJ</span>
                    </div>
                </header>

                <section class="pulse-grid pulse-stats">
                    <article class="pulse-card pulse-stat">
                        <span class="pulse-stat-icon"><i class="fas fa-book-open"></i></span>
                        <span><small>Enrolled Lectures</small><b>{{ $stats['enrolled_lectures'] }}</b><span class="pulse-trend">2 new this week</span></span>
                    </article>
                    <article class="pulse-card pulse-stat">
                        <span class="pulse-stat-icon purple"><i class="fas fa-clipboard-question"></i></span>
                        <span><small>Quizzes</small><b>{{ $stats['quizzes'] }}</b><span class="pulse-trend">3 upcoming</span></span>
                    </article>
                    <article class="pulse-card pulse-stat">
                        <span class="pulse-stat-icon green"><i class="fas fa-calendar-check"></i></span>
                        <span><small>Upcoming Classes</small><b>{{ $stats['upcoming_classes'] }}</b><span class="pulse-trend">Today</span></span>
                    </article>
                    <article class="pulse-card pulse-stat">
                        <span class="pulse-stat-icon orange"><i class="fas fa-award"></i></span>
                        <span><small>Average Grade</small><b>{{ $stats['average_grade'] }}</b><span class="pulse-trend">88.5%</span></span>
                    </article>
                </section>

                <section class="pulse-grid pulse-two" style="margin-top:18px;">
                    <article class="pulse-card pulse-pad">
                        <div class="pulse-section-head">
                            <h2>Quiz Load by Subject</h2>
                            <a href="{{ route('quizzes.index') }}">View all</a>
                        </div>
                        <div class="pulse-bars">
                            @forelse ($quizzesBySubject as $row)
                                <div class="pulse-bar-row">
                                    <span class="pulse-bar-label">{{ $row->subject }}</span>
                                    <span class="pulse-bar-track"><span class="pulse-bar-fill {{ $row->total > 0 ? 'has-value' : '' }}" style="width: {{ $row->pct }}%;"></span></span>
                                    <span class="pulse-bar-value">{{ $row->total }}</span>
                                </div>
                            @empty
                                <p class="pulse-muted">No quizzes published yet.</p>
                            @endforelse
                        </div>
                        <div style="margin-top:16px;">
                            <div class="pulse-meter-label">
                                <span>Discussion Forum answer rate</span>
                                <strong>{{ $answeredRate }}%</strong>
                            </div>
                            <div class="pulse-progress"><span style="width: {{ $answeredRate }}%;"></span></div>
                        </div>
                    </article>

                    <article class="pulse-card pulse-pad">
                        <div class="pulse-section-head">
                            <h2>Upcoming Quizzes</h2>
                            <a href="#">View all</a>
                        </div>
                        <div class="pulse-list">
                            @forelse ($upcomingQuizzes as $quiz)
                                <div class="pulse-row"><span class="pulse-soft-icon"><i class="fas fa-clipboard-question"></i></span><span><strong>{{ $quiz->title }}</strong><p>{{ $quiz->subject }} · {{ $quiz->duration_minutes }} mins</p></span><span class="pulse-tag">{{ $quiz->scheduled_at?->diffForHumans() ?? 'Scheduled' }}</span></div>
                            @empty
                                <div class="pulse-row"><span class="pulse-soft-icon"><i class="fas fa-clipboard-question"></i></span><span><strong>No upcoming quizzes</strong><p>New quiz activity will appear here.</p></span></div>
                            @endforelse
                        </div>
                    </article>
                </section>

                <section class="pulse-grid pulse-three" style="margin-top:18px;">
                    <article class="pulse-card pulse-pad">
                        <div class="pulse-section-head">
                            <h2>Discusion Forum</h2>
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
                                <div class="pulse-row"><span class="pulse-soft-icon"><i class="fas fa-circle-question"></i></span><span><strong>No questions yet</strong><p>Be the first to ask one.</p></span></div>
                            @endforelse
                        </div>
                        <div style="margin-top:12px;">
                            <a href="{{ route('questions.index') }}" class="pulse-btn light" style="width:100%; text-decoration:none; text-align:center;"><i class="fas fa-plus"></i> Ask a question</a>
                        </div>
                    </article>

                    <article class="pulse-card pulse-pad">
                        <div class="pulse-section-head">
                            <h2>Recent Announcements</h2>
                            <a href="#">View all</a>
                        </div>
                        <div class="pulse-list">
                            @foreach ($upcomingQuizAnnouncements as $announcement)
                                <div class="pulse-row"><span class="pulse-soft-icon"><i class="fas fa-bullhorn"></i></span><span><strong>Upcoming quiz: {{ $announcement->title }}</strong><p>{{ $announcement->subject }} &middot; {{ $announcement->scheduled_at->format('M j, Y g:i A') }} &middot; {{ $announcement->duration_minutes }} mins</p></span><span class="pulse-tag orange">{{ $announcement->scheduled_at->diffForHumans() }}</span></div>
                            @endforeach
                            <div class="pulse-row"><span class="pulse-soft-icon"><i class="fas fa-user-tie"></i></span><span><strong>Guest Lecture on ML</strong><p>Guest lecture on Machine Learning by Dr. Sarah Johnson.</p></span><span class="pulse-muted">2d</span></div>
                        </div>
                    </article>

                    <article class="pulse-card pulse-pad">
                        <div class="pulse-section-head">
                            <h2>Quick Resources</h2>
                        </div>
                        <div class="pulse-list">
                            <a class="pulse-resource" href="#"><span class="pulse-stat-icon green"><i class="fas fa-file-lines"></i></span><span><strong>Lecture Notes</strong><span class="pulse-muted">Access materials</span></span></a>
                            <a class="pulse-resource" href="#"><span class="pulse-stat-icon purple"><i class="fas fa-clipboard-list"></i></span><span><strong>Past Quizzes</strong><span class="pulse-muted">Review quizzes</span></span></a>
                            <a class="pulse-resource" href="#"><span class="pulse-stat-icon orange"><i class="fas fa-comments"></i></span><span><strong>Discussion Forum</strong><span class="pulse-muted">Join discussions</span></span></a>
                            <a class="pulse-resource" href="#"><span class="pulse-stat-icon cyan"><i class="fas fa-calendar-days"></i></span><span><strong>Academic Calendar</strong><span class="pulse-muted">View events</span></span></a>
                        </div>
                    </article>
                </section>
            </main>
        </div>
    </div>

    <script>
        (function () {
            const quizzes = @json($upcomingQuizAnnouncements->map(fn ($quiz) => [
                'url' => route('quizzes.take', $quiz),
                'startsAt' => $quiz->scheduled_at->toIso8601String(),
            ]));

            quizzes.forEach(function (quiz) {
                const delay = new Date(quiz.startsAt).getTime() - Date.now();

                if (delay <= 0) {
                    window.location.href = quiz.url;
                    return;
                }

                if (delay < 24 * 60 * 60 * 1000) {
                    setTimeout(function () {
                        window.location.href = quiz.url;
                    }, delay);
                }
            });
        })();
    </script>
</x-layouts.academic-pulse>
