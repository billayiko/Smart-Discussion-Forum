<x-layouts.academic-pulse title="Topic Management">
    <div class="pulse-page">
        <div class="pulse-app">
            <aside class="pulse-sidebar">
                <a class="pulse-logo" href="{{ route('home') }}">
                    <i class="fas fa-graduation-cap"></i>
                    <span>Academic<span>Pulse Forum</span></span>
                </a>

                <nav class="pulse-menu" aria-label="Admin navigation">
                    <a href="{{ route('admin.dashboard') }}"><i class="fas fa-house"></i> Dashboard</a>
                    <a class="active" href="{{ route('admin.topics.index') }}"><i class="fas fa-book"></i> Topics</a>
                    <a href="{{ route('questions.index') }}"><i class="fas fa-circle-question"></i> Questions</a>
                    <a href="{{ route('admin.complaints.index') }}"><i class="fas fa-flag"></i> Complaints</a>
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
                        <h1>Topic Management</h1>
                        <p>Create topics, assign lecturers, and reassign or remove them as needed.</p>
                    </div>
                </header>

                @if (session('success'))
                    <div class="pulse-card pulse-pad" style="margin-bottom:18px; color: var(--pulse-green, #1a7f37);">
                        {{ session('success') }}
                    </div>
                @endif

                <section class="pulse-card pulse-pad">
                    <div class="pulse-section-head">
                        <h2>Create a new topic</h2>
                    </div>
                    <form class="pulse-form" method="POST" action="{{ route('admin.topics.store') }}">
                        @csrf
                        <div class="pulse-form-row" style="display:grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div class="pulse-field">
                                <label for="title">Title</label>
                                <div class="pulse-input">
                                    <input id="title" type="text" name="title" placeholder="e.g. Data Structures" value="{{ old('title') }}" required>
                                </div>
                                @error('title')<span style="color:#d33;font-size:.75rem;">{{ $message }}</span>@enderror
                            </div>
                            <div class="pulse-field">
                                <label for="lecturer_id">Assign lecturer (optional)</label>
                                <div class="pulse-input">
                                    <select id="lecturer_id" name="lecturer_id">
                                        <option value="">Unassigned</option>
                                        @foreach ($lecturers as $lecturer)
                                            <option value="{{ $lecturer->id }}" @selected(old('lecturer_id') == $lecturer->id)>{{ $lecturer->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="pulse-field">
                            <label for="description">Description (optional)</label>
                            <div class="pulse-input">
                                <input id="description" type="text" name="description" placeholder="Short description" value="{{ old('description') }}">
                            </div>
                        </div>
                        <div>
                            <button class="pulse-btn" type="submit"><i class="fas fa-plus"></i> Create Topic</button>
                        </div>
                    </form>
                </section>

                <section class="pulse-card pulse-pad" style="margin-top:22px;">
                    <div class="pulse-section-head">
                        <h2>All topics</h2>
                        <span class="pulse-muted">{{ $topics->count() }} total</span>
                    </div>

                    <div style="overflow:auto;">
                        <table class="pulse-table">
                            <thead>
                                <tr>
                                    <th>Topic</th>
                                    <th>Assigned Lecturer</th>
                                    <th>Subscribers</th>
                                    <th>Reassign / Assign</th>
                                    <th style="text-align:right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($topics as $topic)
                                    <tr>
                                        <td>
                                            <strong>{{ $topic->title }}</strong>
                                            @if ($topic->description)
                                                <p class="pulse-muted" style="margin:2px 0 0;">{{ $topic->description }}</p>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($topic->lecturer)
                                                <span class="pulse-tag green">{{ $topic->lecturer->name }}</span>
                                            @else
                                                <span class="pulse-tag gray">Unassigned</span>
                                            @endif
                                        </td>
                                        <td>{{ $topic->subscribers_count }}</td>
                                        <td>
                                            <form method="POST" action="{{ route('admin.topics.assign', $topic) }}" style="display:flex; gap:8px; align-items:center;">
                                                @csrf
                                                @method('PATCH')
                                                <div class="pulse-input" style="min-height:36px;">
                                                    <select name="lecturer_id">
                                                        <option value="">Unassigned</option>
                                                        @foreach ($lecturers as $lecturer)
                                                            <option value="{{ $lecturer->id }}" @selected($topic->lecturer_id === $lecturer->id)>{{ $lecturer->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <button class="pulse-btn light" type="submit"><i class="fas fa-rotate"></i> Save</button>
                                            </form>
                                        </td>
                                        <td>
                                            <div class="pulse-actions" style="justify-content:flex-end;">
                                                <form method="POST" action="{{ route('admin.topics.destroy', $topic) }}" onsubmit="return confirm('Remove this topic? This cannot be undone.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="pulse-icon-btn" style="color:#d33;" type="submit" title="Remove topic"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="pulse-muted">No topics yet. Create one above.</td>
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
