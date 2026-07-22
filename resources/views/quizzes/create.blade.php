<x-layouts.academic-pulse title="Create Quiz">
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
                    <a href="#"><i class="fas fa-gear"></i> Settings</a>
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
                        <h1>Create a new quiz</h1>
                        <p>Set up the quiz details, then add its multiple-choice questions.</p>
                    </div>
                </header>

                <section class="pulse-card pulse-pad" style="max-width:720px;">
                    @if ($errors->any())
                        <div class="pulse-row" style="border-color:#fecaca; background:#fff1f2; margin-bottom:16px;">
                            <span class="pulse-soft-icon" style="color:#b91c1c; background:#fff1f2;"><i class="fas fa-triangle-exclamation"></i></span>
                            <span>
                                <strong>Please fix the following</strong>
                                <p>{{ $errors->first() }}</p>
                            </span>
                        </div>
                    @endif

                    <form action="{{ route('quizzes.store') }}" method="POST" class="pulse-form">
                        @csrf
                        <div class="pulse-field">
                            <label>Title</label>
                            <div class="pulse-input"><input type="text" name="title" value="{{ old('title') }}" required></div>
                        </div>
                        <div class="pulse-field">
                            <label>Subject</label>
                            <div class="pulse-input"><input type="text" name="subject" value="{{ old('subject') }}" required></div>
                        </div>
                        <div class="pulse-grid" style="grid-template-columns: repeat(2, minmax(0,1fr));">
                            <div class="pulse-field">
                                <label>Total questions</label>
                                <div class="pulse-input"><input type="number" name="total_questions" value="{{ old('total_questions', 10) }}" min="1" required></div>
                            </div>
                            <div class="pulse-field">
                                <label>Duration (minutes)</label>
                                <div class="pulse-input"><input type="number" name="duration_minutes" value="{{ old('duration_minutes', 30) }}" min="1" required></div>
                            </div>
                        </div>
                        <div class="pulse-grid" style="grid-template-columns: repeat(2, minmax(0,1fr));">
                            <div class="pulse-field">
                                <label>Scheduled at</label>
                                <div class="pulse-input"><input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}"></div>
                            </div>
                            <div class="pulse-field">
                                <label>Status</label>
                                <div class="pulse-input">
                                    <select name="status">
                                        <option value="draft" @selected(old('status', 'draft') === 'draft')>Draft</option>
                                        <option value="planned" @selected(old('status') === 'planned')>Planned</option>
                                        <option value="scheduled" @selected(old('status') === 'scheduled')>Scheduled</option>
                                        <option value="due_soon" @selected(old('status') === 'due_soon')>Due Soon</option>
                                        <option value="active" @selected(old('status') === 'active')>Active</option>
                                        <option value="closed" @selected(old('status') === 'closed')>Closed</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="pulse-grid" style="grid-template-columns: repeat(2, minmax(0,1fr));">
                            <div class="pulse-field">
                                <label>Course topic</label>
                                <div class="pulse-input">
                                    <select name="course_topic_id">
                                        <option value="">None</option>
                                        @foreach ($topics as $topic)
                                            <option value="{{ $topic->id }}" @selected(old('course_topic_id') == $topic->id)>{{ $topic->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <label style="display:flex; align-items:center; gap:8px; font-size:.82rem; font-weight:700; color: var(--pulse-muted); padding-top:28px;">
                                <input type="checkbox" name="proctored" value="1" @checked(old('proctored'))>
                                Proctored quiz
                            </label>
                        </div>
                        <p class="pulse-muted">Students will see an announcement with the date and time, and will be taken straight to it once it starts, as soon as you save its questions below.</p>

                        <div style="display:flex; gap:12px;">
                            <button type="submit" class="pulse-btn"><i class="fas fa-arrow-right"></i> Continue to add questions</button>
                            <a href="{{ route('quizzes.index') }}" class="pulse-btn light" style="text-decoration:none;">Cancel</a>
                        </div>
                    </form>
                </section>
            </main>
        </div>
    </div>
</x-layouts.academic-pulse>
