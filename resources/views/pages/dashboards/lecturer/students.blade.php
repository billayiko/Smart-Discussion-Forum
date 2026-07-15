<x-layouts.academic-pulse title="Students">
    <div class="pulse-page">
        <div class="pulse-app">
            <aside class="pulse-sidebar">
                <a class="pulse-logo" href="{{ route('home') }}">
                    <i class="fas fa-graduation-cap"></i>
                    <span>Academic<span>Pulse Forum</span></span>
                </a>

                <nav class="pulse-menu" aria-label="Lecturer navigation">
                    <a href="{{ route('lecturer.dashboard') }}"><i class="fas fa-house"></i> Dashboard</a>
                    <a href="#"><i class="fas fa-message"></i> Messages</a>
                    <a class="active" href="{{ route('lecturer.students') }}"><i class="fas fa-users"></i> Students</a>
                    <a href="#"><i class="fas fa-chart-line"></i> Analytics</a>
                    <a href="#"><i class="fas fa-gear"></i> Settings</a>
                </nav>

                <div class="pulse-sidebar-footer">
                    <div class="pulse-user">
                        <span class="pulse-avatar">{{ strtoupper(substr($user->name ?? 'LC', 0, 2)) }}</span>
                        <span><strong>{{ $user->name ?? 'Lecturer' }}</strong><span>{{ $user->roleLabel() ?? 'Lecturer' }}</span></span>
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
                        <h1>Students</h1>
                        <p>See who is currently online and who is subscribed to your topics.</p>
                    </div>
                </header>

                <section class="pulse-grid pulse-stats">
                    <article class="pulse-card pulse-stat">
                        <span class="pulse-stat-icon"><i class="fas fa-book"></i></span>
                        <span><small>Your Topics</small><b>{{ $topics->count() }}</b></span>
                    </article>
                    <article class="pulse-card pulse-stat">
                        <span class="pulse-stat-icon purple"><i class="fas fa-users"></i></span>
                        <span><small>Total Students</small><b>{{ $students->count() }}</b></span>
                    </article>
                    <article class="pulse-card pulse-stat">
                        <span class="pulse-stat-icon green"><i class="fas fa-circle-check"></i></span>
                        <span><small>Online Now</small><b>{{ $students->where('is_online', true)->count() }}</b></span>
                    </article>
                    <article class="pulse-card pulse-stat">
                        <span class="pulse-stat-icon orange"><i class="fas fa-bell"></i></span>
                        <span><small>Subscribed to Your Topics</small><b>{{ $students->filter(fn ($s) => $s->subscribedTopics->isNotEmpty())->count() }}</b></span>
                    </article>
                </section>

                @if ($topics->isEmpty())
                    <section class="pulse-card pulse-pad" style="margin-top:22px;">
                        <p class="pulse-muted">You have no topics assigned yet. Ask an admin to assign one to you.</p>
                    </section>
                @endif

                <section class="pulse-card pulse-pad" style="margin-top:22px;">
                    <div class="pulse-section-head">
                        <h2>All Students</h2>
                    </div>

                    <div style="overflow:auto;">
                        <table class="pulse-table">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Status</th>
                                    <th>Subscribed to your topics</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($students as $student)
                                    <tr>
                                        <td>
                                            <strong>{{ $student->name }}</strong>
                                            <p class="pulse-muted" style="margin:2px 0 0;">{{ $student->email }}</p>
                                        </td>
                                        <td>
                                            @if ($student->is_online)
                                                <span class="pulse-tag green"><i class="fas fa-circle" style="font-size:.5rem;"></i> Online</span>
                                            @else
                                                <span class="pulse-tag gray">Offline</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($student->subscribedTopics->isNotEmpty())
                                                @foreach ($student->subscribedTopics as $topic)
                                                    <span class="pulse-tag">{{ $topic->title }}</span>
                                                @endforeach
                                            @else
                                                <span class="pulse-tag gray">Not subscribed</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="pulse-muted">No students found.</td>
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
