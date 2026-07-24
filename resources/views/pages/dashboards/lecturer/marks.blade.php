<x-layouts.academic-pulse title="Student Marks">
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
                    <a class="active" href="{{ route('lecturer.marks') }}"><i class="fas fa-chart-line"></i> Student Marks</a>
                    <a href="{{ route('profile.edit') }}"><i class="fas fa-gear"></i> Settings</a>
                </nav>

                <div class="pulse-sidebar-footer">
                    <div class="pulse-user">
                        <span class="pulse-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'LC', 0, 2)) }}</span>
                        <span><strong>{{ auth()->user()->name ?? 'Lecturer' }}</strong><span>{{ auth()->user()->roleLabel() ?? 'Lecturer' }}</span></span>
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
                        <h1>Student Marks</h1>
                        <p>Forum participation and quiz scores combined into one mark per student.</p>
                    </div>
                </header>

                <section class="pulse-card pulse-pad">
                    <div class="pulse-section-head">
                        <h2>All Students</h2>
                        <span class="pulse-muted">Combined mark = average of participation score and quiz average</span>
                    </div>

                    <div style="overflow:auto;">
                        <table class="pulse-table">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Forum Posts</th>
                                    <th>Participation</th>
                                    <th>Quizzes Taken</th>
                                    <th>Quiz Average</th>
                                    <th>Combined Mark</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($students as $row)
                                    <tr>
                                        <td>
                                            <strong>{{ $row->student->name }}</strong>
                                            <p class="pulse-muted" style="margin:2px 0 0;">{{ $row->student->email }}</p>
                                        </td>
                                        <td>{{ $row->posts }}</td>
                                        <td>{{ $row->participation_score }}%</td>
                                        <td>{{ $row->quiz_attempts }}</td>
                                        <td>{{ $row->quiz_average !== null ? $row->quiz_average.'%' : '—' }}</td>
                                        <td>
                                            <span class="pulse-tag {{ $row->combined_score >= 70 ? 'green' : ($row->combined_score >= 40 ? 'orange' : 'gray') }}">{{ $row->combined_score }}%</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="pulse-muted" style="text-align:center; padding:32px;">No students found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </main>
        </div>
    </div>
</x-layouts.academic-pulse>
