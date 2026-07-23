<x-layouts.academic-pulse title="Quiz Management">
    <div class="pulse-page">
        <div class="pulse-app">
            <aside class="pulse-sidebar">
                <a class="pulse-logo" href="{{ route('home') }}">
                    <i class="fas fa-graduation-cap"></i>
                    <span>Academic<span>Pulse Forum</span></span>
                </a>

                <nav class="pulse-menu" aria-label="Lecturer navigation">
                    <a href="{{ route('lecturer.dashboard') }}"><i class="fas fa-house"></i> Dashboard</a>
                    <a href="{{ route('messages.index') }}"><i class="fas fa-message"></i> Messages</a>
                    <a href="{{ route('lecturer.students') }}"><i class="fas fa-users"></i> Students</a>
                    <a href="{{ route('questions.index') }}"><i class="fas fa-circle-question"></i> Discussion Forum</a>
                    <a class="active" href="{{ route('quizzes.index') }}"><i class="fas fa-clipboard-question"></i> Quizzes</a>
                    <a href="{{ route('profile.edit') }}"><i class="fas fa-gear"></i> Settings</a>
                </nav>

                <div class="pulse-sidebar-footer">
                    <div class="pulse-user">
                        <span class="pulse-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'LC', 0, 2)) }}</span>
                        <span><strong>{{ auth()->user()->name ?? 'Lecturer' }}</strong><span>{{ auth()->user()->role_label ?? 'Lecturer' }}</span></span>
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
                        <h1>Quiz Management</h1>
                        <p>Manage quizzes for your lectures and import content quickly.</p>
                    </div>
                    <div class="pulse-tools">
                        <a href="{{ route('quizzes.create') }}" class="pulse-btn" style="text-decoration:none;"><i class="fas fa-plus"></i> Create quiz</a>
                    </div>
                </header>

                @if (session('success'))
                    <div class="pulse-row" style="border-color:#bbf7d0; background:#f0fdf4; margin-bottom:18px;">
                        <span class="pulse-soft-icon" style="color:#0d9f6e; background:#e8fbf2;"><i class="fas fa-circle-check"></i></span>
                        <span><strong>{{ session('success') }}</strong></span>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="pulse-row" style="border-color:#fecaca; background:#fff1f2; margin-bottom:18px;">
                        <span class="pulse-soft-icon" style="color:#b91c1c; background:#fff1f2;"><i class="fas fa-triangle-exclamation"></i></span>
                        <span><strong>{{ $errors->first() }}</strong></span>
                    </div>
                @endif

                <section class="pulse-grid pulse-stats" style="grid-template-columns: repeat(2, minmax(0,1fr));">
                    <article class="pulse-card pulse-stat">
                        <span class="pulse-stat-icon"><i class="fas fa-clipboard-question"></i></span>
                        <span><small>Active quizzes</small><b>{{ $stats['active_count'] }}</b></span>
                    </article>
                    <article class="pulse-card pulse-stat">
                        <span class="pulse-stat-icon green"><i class="fas fa-calendar-check"></i></span>
                        <span><small>Published this week</small><b>{{ $stats['published_this_week'] }}</b></span>
                    </article>
                </section>

                <section class="pulse-card pulse-pad" style="margin-top:18px;">
                    <div class="pulse-section-head">
                        <h2>Bulk import quizzes from CSV</h2>
                    </div>
                    <form action="{{ route('quizzes.import') }}" method="POST" enctype="multipart/form-data" style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
                        @csrf
                        <input type="file" name="file" accept=".csv,.xlsx,.xls" required>
                        <button type="submit" class="pulse-btn light"><i class="fas fa-file-import"></i> Import</button>
                    </form>
                    <p class="pulse-muted" style="margin-top:10px;">Columns: <code>title, subject, total_questions, duration_minutes, scheduled_at, status</code>. Each row creates a new quiz shell &mdash; add its questions afterwards from the table below.</p>
                </section>

                <div class="pulse-card" style="margin-top:18px; overflow-x:auto;">
                    <table class="pulse-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Questions</th>
                                <th>Scheduled</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($quizzes as $quiz)
                                <tr>
                                    <td>{{ $quiz->title }}</td>
                                    <td>{{ $quiz->subject }}</td>
                                    <td><span class="pulse-tag {{ $quiz->stage() === 'active' ? 'green' : ($quiz->stage() === 'due_soon' ? 'orange' : ($quiz->stage() === 'draft' ? 'gray' : '')) }}">{{ str_replace('_', ' ', ucfirst($quiz->stage())) }}</span></td>
                                    <td>{{ $quiz->questions_count }} / {{ $quiz->total_questions }}</td>
                                    <td>{{ $quiz->scheduled_at?->format('M j, Y g:i A') ?? '—' }}</td>
                                    <td class="pulse-actions">
                                        @if ($quiz->isEditable())
                                            <a href="{{ route('quizzes.edit', $quiz) }}" class="pulse-icon-btn" title="Edit quiz details"><i class="fas fa-pen"></i></a>
                                        @endif
                                        @if ($quiz->questions_finalized_at)
                                            <a href="{{ route('quizzes.questions.create', $quiz) }}" class="pulse-btn light" style="text-decoration:none;"><i class="fas fa-eye"></i> View questions</a>
                                        @elseif ($quiz->questions_count < $quiz->total_questions)
                                            <a href="{{ route('quizzes.questions.create', $quiz) }}" class="pulse-btn light" style="text-decoration:none;"><i class="fas fa-list-check"></i> Add questions</a>
                                        @else
                                            <a href="{{ route('quizzes.questions.create', $quiz) }}" class="pulse-btn" style="text-decoration:none;"><i class="fas fa-floppy-disk"></i> Save questions</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align:center; padding:32px; color:var(--pulse-muted);">No quizzes yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div style="margin-top:18px;">
                    {{ $quizzes->links() }}
                </div>
            </main>
        </div>
    </div>
</x-layouts.academic-pulse>
