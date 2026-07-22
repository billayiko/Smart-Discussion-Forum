<header class="forum-topbar">
    <a class="forum-logo" href="{{ route("{$user->role}.dashboard") }}">
        <i class="fas fa-graduation-cap"></i>
        <span>Academic<span>Pulse Forum</span><small>Smart Discussion Forum</small></span>
    </a>

    <div class="forum-topbar-right">
        <span class="forum-online-pill"><span class="forum-dot online"></span> Online</span>
        @include('partials._notification-bell')
        <span class="forum-user">
            <span class="forum-avatar gold">{{ $user->initials() }}</span>
            <strong>{{ $user->name }}</strong>
            <span class="forum-role-badge" data-role="{{ $user->role }}"><i class="fas fa-graduation-cap"></i> {{ strtoupper($user->roleLabel()) }}</span>
        </span>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="forum-btn danger"><i class="fas fa-right-from-bracket"></i> Logout</button>
        </form>
    </div>
</header>
