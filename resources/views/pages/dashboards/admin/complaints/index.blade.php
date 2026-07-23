<x-layouts.academic-pulse title="Complaints">
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
                    <a class="active" href="{{ route('admin.complaints.index') }}"><i class="fas fa-flag"></i> Complaints</a>
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
                    <form method="POST" action="{{ route('logout') }}" style="margin-top:12px;">
                        @csrf
                        <button type="submit" class="pulse-btn light" style="width:100%;"><i class="fas fa-arrow-right-from-bracket"></i> Log out</button>
                    </form>
                </div>
            </aside>

            <main class="pulse-main">
                <header class="pulse-topbar">
                    <div class="pulse-title">
                        <h1>Complaints</h1>
                        <p>Review reports raised about questions and take action.</p>
                    </div>
                </header>

                @if (session('success'))
                    <div class="pulse-card pulse-pad" style="margin-bottom:18px; color: var(--pulse-green, #1a7f37);">
                        {{ session('success') }}
                    </div>
                @endif

                <section class="pulse-card pulse-pad">
                    <div class="pulse-section-head">
                        <h2>All Complaints</h2>
                        <span class="pulse-muted">{{ $complaints->count() }} total</span>
                    </div>
                    <div style="overflow:auto;">
                        <table class="pulse-table">
                            <thead>
                                <tr>
                                    <th>Question</th>
                                    <th>Reported by</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th style="text-align:right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($complaints as $complaint)
                                    <tr>
                                        <td>
                                            @if ($complaint->question)
                                                <a href="{{ route('questions.show', $complaint->question) }}"><strong>{{ $complaint->question->title }}</strong></a>
                                            @else
                                                <span class="pulse-muted">Question removed</span>
                                            @endif
                                        </td>
                                        <td>{{ $complaint->user->name }}</td>
                                        <td>{{ $complaint->reason }}</td>
                                        <td>
                                            @if ($complaint->status === 'pending')
                                                <span class="pulse-tag orange">Pending</span>
                                            @elseif ($complaint->status === 'dismissed')
                                                <span class="pulse-tag gray">Dismissed</span>
                                            @else
                                                <span class="pulse-tag green">{{ ucfirst($complaint->status) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($complaint->status === 'pending' && $complaint->question)
                                                <div class="pulse-actions" style="justify-content:flex-end; gap:8px;">
                                                    <form method="POST" action="{{ route('admin.complaints.update', $complaint) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="action" value="dismiss">
                                                        <button class="pulse-btn light" type="submit">Dismiss</button>
                                                    </form>
                                                    <form method="POST" action="{{ route('admin.complaints.update', $complaint) }}" onsubmit="return confirm('Delete the reported question?');">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="action" value="delete_question">
                                                        <button class="pulse-btn" style="background:#d33;" type="submit">Delete Question</button>
                                                    </form>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="pulse-muted">No complaints have been reported.</td>
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
