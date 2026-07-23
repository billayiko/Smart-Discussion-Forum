@php
    $addedCount = $quiz->questions->count();
    $targetReached = $addedCount >= $quiz->total_questions;
    $remaining = max($quiz->total_questions - $addedCount, 0);
    $progressPct = $quiz->total_questions > 0 ? (int) round(min($addedCount / $quiz->total_questions, 1) * 100) : 0;
@endphp
<x-layouts.academic-pulse title="{{ $quiz->title }} - Questions">
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
                        <h1>{{ $quiz->title }}</h1>
                        <p>{{ $quiz->subject }} &middot; {{ $quiz->duration_minutes }} mins &middot; {{ $quiz->scheduled_at?->format('M j, Y g:i A') ?? 'Not scheduled yet' }}</p>
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

                <section class="pulse-card pulse-pad" style="margin-bottom:18px;">
                    <div class="pulse-meter-label">
                        <span>Questions added</span>
                        <strong>{{ $addedCount }} / {{ $quiz->total_questions }}</strong>
                    </div>
                    <div class="pulse-progress"><span style="width: {{ $progressPct }}%;"></span></div>

                    @if ($quiz->questions_finalized_at)
                        <div style="margin-top:16px;">
                            <p class="pulse-muted" style="margin:0; color:#0d9f6e; font-weight:800;"><i class="fas fa-circle-check"></i> Saved on {{ $quiz->questions_finalized_at->format('M j, Y g:i A') }}. Students will see this quiz announced ahead of time and be taken to it once it starts.</p>
                        </div>
                    @elseif ($targetReached)
                        <div style="margin-top:16px; display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
                            <p class="pulse-muted" style="margin:0;">All required questions have been added. Save them to notify students.</p>
                            <form action="{{ route('quizzes.questions.finalize', $quiz) }}" method="POST">
                                @csrf
                                <button type="submit" class="pulse-btn"><i class="fas fa-floppy-disk"></i> Save quiz questions</button>
                            </form>
                        </div>
                    @else
                        <p class="pulse-muted" style="margin-top:10px;">{{ $remaining }} more question(s) needed before you can save this quiz.</p>
                    @endif
                </section>

                <section class="pulse-grid pulse-two">
                    <article class="pulse-card pulse-pad">
                        <div class="pulse-section-head">
                            <h2>Added questions</h2>
                        </div>
                        <div class="pulse-list">
                            @forelse ($quiz->questions as $index => $question)
                                <div class="pulse-row" style="grid-template-columns: auto minmax(0,1fr) auto; align-items:flex-start;">
                                    <span class="pulse-soft-icon">{{ $index + 1 }}</span>
                                    <span>
                                        <strong>{{ $question->question }}</strong>
                                        <p style="display:grid; gap:2px; margin-top:6px;">
                                            @foreach ($question->options() as $letter => $text)
                                                <span style="{{ $letter === $question->correct_option ? 'color:#0d9f6e; font-weight:800;' : '' }}">
                                                    {{ strtoupper($letter) }}. {{ $text }}
                                                    @if ($letter === $question->correct_option) <i class="fas fa-check"></i> @endif
                                                </span>
                                            @endforeach
                                        </p>
                                    </span>
                                    @unless ($quiz->questions_finalized_at)
                                        <form action="{{ route('quizzes.questions.destroy', [$quiz, $question]) }}" method="POST" onsubmit="return confirm('Remove this question?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="pulse-icon-btn" style="color:#b91c1c;" title="Remove"><i class="fas fa-trash"></i></button>
                                        </form>
                                    @endunless
                                </div>
                            @empty
                                <div class="pulse-row"><span class="pulse-soft-icon"><i class="fas fa-clipboard-question"></i></span><span><strong>No questions added yet</strong><p>Add your first question using the form.</p></span></div>
                            @endforelse
                        </div>
                    </article>

                    <article class="pulse-card pulse-pad">
                        @unless ($targetReached)
                            <div class="pulse-section-head">
                                <h2>Add a question</h2>
                            </div>
                            <form action="{{ route('quizzes.questions.store', $quiz) }}" method="POST" class="pulse-form">
                                @csrf
                                <div class="pulse-field">
                                    <label>Question</label>
                                    <div class="pulse-input">
                                        <input type="text" name="question" placeholder="Type the question text" required>
                                    </div>
                                </div>
                                @foreach (['a', 'b', 'c', 'd'] as $letter)
                                    <div class="pulse-field">
                                        <label>Option {{ strtoupper($letter) }}</label>
                                        <div class="pulse-input">
                                            <input type="text" name="option_{{ $letter }}" placeholder="Option {{ strtoupper($letter) }} text" required>
                                        </div>
                                    </div>
                                @endforeach
                                <div class="pulse-field">
                                    <label>Correct answer</label>
                                    <div class="pulse-input">
                                        <input type="text" name="correct_option" maxlength="1" placeholder="a, b, c or d" required style="text-transform:uppercase;">
                                    </div>
                                    <p class="pulse-muted" style="margin:0;">Type the letter (a, b, c or d) of the correct option above.</p>
                                </div>
                                <button type="submit" class="pulse-btn"><i class="fas fa-plus"></i> Add question</button>
                            </form>

                            <div class="pulse-section-head" style="margin-top:24px;">
                                <h2>Or import from CSV</h2>
                            </div>
                            <form action="{{ route('quizzes.questions.import', $quiz) }}" method="POST" enctype="multipart/form-data" class="pulse-form">
                                @csrf
                                <div class="pulse-field">
                                    <div class="pulse-input"><input type="file" name="file" accept=".csv,.txt" required></div>
                                </div>
                                <p class="pulse-muted">Columns: <code>question, option_a, option_b, option_c, option_d, correct_option</code>. <code>correct_option</code> must be a, b, c or d. Rows beyond the remaining {{ $remaining }} question(s) needed are ignored.</p>
                                <button type="submit" class="pulse-btn light"><i class="fas fa-file-import"></i> Import questions</button>
                            </form>
                        @elseif ($quiz->questions_finalized_at)
                            <div class="pulse-section-head">
                                <h2>Questions saved</h2>
                            </div>
                            <p class="pulse-muted">This quiz's questions are locked in and students will be notified ahead of the scheduled time.</p>
                        @else
                            <div class="pulse-section-head">
                                <h2>Target reached</h2>
                            </div>
                            <p class="pulse-muted">This quiz has all {{ $quiz->total_questions }} question(s) it needs. Remove a question above if you need to swap one out, or save them below.</p>
                        @endunless
                    </article>
                </section>

                <div style="margin-top:18px;">
                    <a href="{{ route('quizzes.index') }}" class="pulse-btn light" style="text-decoration:none;"><i class="fas fa-arrow-left"></i> Back to quizzes</a>
                </div>
            </main>
        </div>
    </div>
</x-layouts.academic-pulse>
