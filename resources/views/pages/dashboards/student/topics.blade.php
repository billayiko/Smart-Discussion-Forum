<x-layouts.academic-pulse title="Topics">
    <div class="pulse-page">
        <div class="pulse-app">
            <aside class="pulse-sidebar">
                <a class="pulse-logo" href="{{ route('home') }}">
                    <i class="fas fa-graduation-cap"></i>
                    <span>Academic<span>Pulse Forum</span></span>
                </a>

                <nav class="pulse-menu" aria-label="Student navigation">
                    <a href="{{ route('student.dashboard') }}"><i class="fas fa-house"></i> Dashboard</a>
                    <a href="#"><i class="fas fa-message"></i> Messages</a>
                    <a class="active" href="{{ route('topics.index') }}"><i class="fas fa-book"></i> Topics</a>
                    <a href="#"><i class="fas fa-gear"></i> Settings</a>
                </nav>

                <div class="pulse-sidebar-footer">
                    <div class="pulse-user">
                        <span class="pulse-avatar">{{ strtoupper(substr($user->name ?? 'U', 0, 2)) }}</span>
                        <span><strong>{{ $user->name ?? 'Student' }}</strong><span>{{ $user->roleLabel() ?? 'Student' }}</span></span>
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
                        <h1>Topics</h1>
                        <p>Subscribe to the topics you're studying so your lecturer can see you're following along.</p>
                    </div>
                </header>

                @if (session('success'))
                    <div class="pulse-card pulse-pad" style="margin-bottom:18px; color: var(--pulse-green, #1a7f37);">
                        {{ session('success') }}
                    </div>
                @endif

                <section class="pulse-grid pulse-three">
                    @forelse ($topics as $topic)
                        <article class="pulse-card pulse-pad">
                            <div class="pulse-section-head">
                                <h2>{{ $topic->title }}</h2>
                                @if ($subscribedTopicIds->contains($topic->id))
                                    <span class="pulse-tag green">Subscribed</span>
                                @endif
                            </div>
                            <p class="pulse-muted">{{ $topic->description ?? 'No description provided.' }}</p>
                            <p class="pulse-muted" style="margin-top:8px;">
                                <i class="fas fa-chalkboard-user"></i>
                                {{ $topic->lecturer->name ?? 'No lecturer assigned yet' }}
                            </p>
                            <p class="pulse-muted">{{ $topic->subscribers_count }} subscriber(s)</p>

                            @if ($subscribedTopicIds->contains($topic->id))
                                <form method="POST" action="{{ route('topics.unsubscribe', $topic) }}" style="margin-top:12px;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="pulse-btn light" type="submit"><i class="fas fa-bell-slash"></i> Unsubscribe</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('topics.subscribe', $topic) }}" style="margin-top:12px;">
                                    @csrf
                                    <button class="pulse-btn" type="submit"><i class="fas fa-bell"></i> Subscribe</button>
                                </form>
                            @endif
                        </article>
                    @empty
                        <p class="pulse-muted">No topics have been created yet.</p>
                    @endforelse
                </section>
            </main>
        </div>
    </div>
</x-layouts.academic-pulse>
