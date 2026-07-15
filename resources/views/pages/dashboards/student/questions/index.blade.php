<x-layouts.academic-pulse title="Questions">
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
                    <a href="{{ route('topics.index') }}"><i class="fas fa-book"></i> Topics</a>
                    <a class="active" href="{{ route('questions.index') }}"><i class="fas fa-circle-question"></i> Questions</a>
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
                @include('pages.dashboards.questions._header', ['title' => 'Questions', 'subtitle' => 'Ask a question and get help from lecturers and fellow students.'])
                @include('pages.dashboards.questions._list', ['canAsk' => true])
            </main>
        </div>
    </div>
</x-layouts.academic-pulse>
