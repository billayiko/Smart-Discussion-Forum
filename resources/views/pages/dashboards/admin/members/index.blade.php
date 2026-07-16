<x-layouts.academic-pulse title="Members">
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
                    <a href="{{ route('questions.index') }}"><i class="fas fa-circle-question"></i> Questions</a>
                    <a href="{{ route('admin.complaints.index') }}"><i class="fas fa-flag"></i> Complaints</a>
                    <a class="active" href="{{ route('admin.members.index') }}"><i class="fas fa-user-shield"></i> Members</a>
                    <a href="{{ route('messages.index') }}"><i class="fas fa-message"></i> Messages</a>
                    <a href="{{ route('admin.analytics.index') }}"><i class="fas fa-chart-line"></i> Analytics</a>
                    <a href="#"><i class="fas fa-gear"></i> Settings</a>
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
                        <h1>Members</h1>
                        <p>Manage inactivity warnings and blacklisting for students and lecturers.</p>
                    </div>
                </header>

                @if (session('success'))
                    <div class="pulse-card pulse-pad" style="margin-bottom:18px; color: var(--pulse-green, #1a7f37);">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="pulse-card pulse-pad" style="margin-bottom:18px; color:#d33;">
                        <ul style="margin:0; padding-left:18px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <section class="pulse-card pulse-pad">
                    <div class="pulse-section-head">
                        <h2>Inactivity Policy</h2>
                        <span class="pulse-muted">2 warnings, then a compliance window, then blacklisting</span>
                    </div>
                    <form method="POST" action="{{ route('admin.members.settings') }}" style="display:grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap:16px; align-items:end;">
                        @csrf
                        @method('PATCH')
                        <div class="pulse-field">
                            <label for="inactivity_threshold_days">Days idle before each warning</label>
                            <div class="pulse-input">
                                <input id="inactivity_threshold_days" type="number" min="1" name="inactivity_threshold_days" value="{{ old('inactivity_threshold_days', $settings->inactivity_threshold_days) }}" required>
                            </div>
                        </div>
                        <div class="pulse-field">
                            <label for="compliance_days">Days to comply after 2nd warning</label>
                            <div class="pulse-input">
                                <input id="compliance_days" type="number" min="1" name="compliance_days" value="{{ old('compliance_days', $settings->compliance_days) }}" required>
                            </div>
                        </div>
                        <div class="pulse-field">
                            <label for="blacklist_duration_days">Blacklist duration (days)</label>
                            <div class="pulse-input">
                                <input id="blacklist_duration_days" type="number" min="1" name="blacklist_duration_days" value="{{ old('blacklist_duration_days', $settings->blacklist_duration_days) }}" required>
                            </div>
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <button class="pulse-btn" type="submit"><i class="fas fa-floppy-disk"></i> Save Policy</button>
                        </div>
                    </form>
                </section>

                <section class="pulse-card pulse-pad" style="margin-top:22px;">
                    <div class="pulse-section-head">
                        <h2>All Members</h2>
                        <span class="pulse-muted">{{ $members->count() }} total</span>
                    </div>
                    <div style="overflow:auto;">
                        <table class="pulse-table">
                            <thead>
                                <tr>
                                    <th>Member</th>
                                    <th>Role</th>
                                    <th>Last Communication</th>
                                    <th>Warnings</th>
                                    <th>Status</th>
                                    <th style="text-align:right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($members as $member)
                                    <tr>
                                        <td>
                                            <strong>{{ $member->name }}</strong>
                                            <p class="pulse-muted" style="margin:2px 0 0;">{{ $member->email }}</p>
                                        </td>
                                        <td>{{ $member->roleLabel() }}</td>
                                        <td>{{ $member->last_communication_at?->diffForHumans() ?? 'Never' }}</td>
                                        <td>
                                            @if ($member->warning_count > 0)
                                                <span class="pulse-tag orange">{{ $member->warning_count }} / 2</span>
                                            @else
                                                <span class="pulse-tag gray">0 / 2</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($member->isBlacklisted())
                                                <span class="pulse-tag" style="background:#fde2e2; color:#d33;">Blacklisted until {{ $member->blacklisted_until?->toFormattedDateString() }}</span>
                                            @else
                                                <span class="pulse-tag green">Active</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="pulse-actions" style="justify-content:flex-end; gap:8px;">
                                                @if ($member->isBlacklisted())
                                                    <form method="POST" action="{{ route('admin.members.unblacklist', $member) }}">
                                                        @csrf
                                                        <button class="pulse-btn light" type="submit">Reinstate</button>
                                                    </form>
                                                @else
                                                    <form method="POST" action="{{ route('admin.members.warn', $member) }}">
                                                        @csrf
                                                        <button class="pulse-btn light" type="submit" @disabled($member->warning_count >= 2)>Warn</button>
                                                    </form>
                                                    <form method="POST" action="{{ route('admin.members.blacklist', $member) }}" onsubmit="return confirm('Blacklist this member?');">
                                                        @csrf
                                                        <button class="pulse-btn" style="background:#d33;" type="submit">Blacklist</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="pulse-muted">No students or lecturers yet.</td>
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
