<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    @include('partials.head')
    @include('partials._pulse-styles')
</head>
<body class="pulse-body">
    @php
        $user = auth()->user();

        $navItems = match ($user->role ?? null) {
            'student' => [
                ['route' => 'student.dashboard', 'icon' => 'fa-house', 'label' => 'Dashboard'],
                ['route' => 'messages.index', 'icon' => 'fa-message', 'label' => 'Messages'],
                ['route' => 'topics.index', 'icon' => 'fa-book', 'label' => 'Topics'],
                ['route' => 'questions.index', 'icon' => 'fa-circle-question', 'label' => 'Discussion Forum'],
            ],
            'lecturer' => [
                ['route' => 'lecturer.dashboard', 'icon' => 'fa-house', 'label' => 'Dashboard'],
                ['route' => 'messages.index', 'icon' => 'fa-message', 'label' => 'Messages'],
                ['route' => 'lecturer.students', 'icon' => 'fa-users', 'label' => 'Students'],
                ['route' => 'questions.index', 'icon' => 'fa-circle-question', 'label' => 'Discussion Forum'],
            ],
            'admin' => [
                ['route' => 'admin.dashboard', 'icon' => 'fa-house', 'label' => 'Dashboard'],
                ['route' => 'admin.topics.index', 'icon' => 'fa-book', 'label' => 'Topics'],
                ['route' => 'questions.index', 'icon' => 'fa-circle-question', 'label' => 'Discussion Forum'],
                ['route' => 'admin.complaints.index', 'icon' => 'fa-flag', 'label' => 'Complaints'],
                ['route' => 'admin.members.index', 'icon' => 'fa-user-shield', 'label' => 'Members'],
                ['route' => 'messages.index', 'icon' => 'fa-message', 'label' => 'Messages'],
            ],
            default => [],
        };

        $dashboardRoute = $user ? $user->dashboardRouteName() : 'home';
    @endphp

    <div class="pulse-page">
        <div class="pulse-app">
            <aside class="pulse-sidebar">
                <a class="pulse-logo" href="{{ route('home') }}">
                    <i class="fas fa-graduation-cap"></i>
                    <span>Academic<span>Pulse Forum</span></span>
                </a>

                <nav class="pulse-menu" aria-label="Settings navigation">
                    @foreach ($navItems as $item)
                        <a href="{{ route($item['route']) }}"><i class="fas {{ $item['icon'] }}"></i> {{ $item['label'] }}</a>
                    @endforeach
                    <a class="active" href="{{ route('profile.edit') }}"><i class="fas fa-gear"></i> Settings</a>
                </nav>

                <div class="pulse-sidebar-footer">
                    <div class="pulse-user">
                        <span class="pulse-avatar">{{ strtoupper(substr($user->name ?? 'U', 0, 2)) }}</span>
                        <span><strong>{{ $user->name ?? 'User' }}</strong><span>{{ $user?->roleLabel() ?? 'User' }}</span></span>
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
                        <h1><a href="{{ route($dashboardRoute) }}" style="color:inherit;"><i class="fas fa-arrow-left" style="font-size:.8em; margin-right:8px;"></i></a>{{ $title ?? 'Settings' }}</h1>
                    </div>
                    <div class="pulse-tools">
                        @include('partials._notification-bell')
                        <span class="pulse-avatar">{{ strtoupper(substr($user->name ?? 'U', 0, 2)) }}</span>
                    </div>
                </header>

                <section class="pulse-card pulse-pad" style="margin-top:18px;">
                    {{ $slot }}
                </section>
            </main>
        </div>
    </div>

    <livewire:create-team-modal />

    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist

    @include('partials._pulse-scripts')
    @fluxScripts
</body>
</html>
