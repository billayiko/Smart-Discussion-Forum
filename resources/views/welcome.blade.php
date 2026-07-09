<x-layouts.academic-pulse title="Welcome">
    <div class="ap-shell">
        <header class="ap-header">
            <a class="ap-brand" href="{{ route('home') }}">
                <span class="ap-logo"><i class="fas fa-graduation-cap"></i></span>
                <span>
                    <h1>Academic<span>Pulse</span></h1>
                    <small>Smart Discussion Forum</small>
                </span>
            </a>
            <nav class="ap-nav">
                @if (Route::has('register'))
                    <a class="ap-btn primary" href="{{ route('register') }}"><i class="fas fa-user-plus"></i> Sign Up</a>
                @endif
                @if (Route::has('login'))
                    <a class="ap-btn light" href="{{ route('login') }}"><i class="fas fa-sign-in-alt"></i> Login</a>
                @endif
            </nav>
        </header>

        <main class="ap-main">
            <section style="min-height:calc(100vh - 170px);display:grid;place-items:center;text-align:center;">
                <div style="max-width:720px;">
                    <span class="pill"><i class="fas fa-comments"></i> Academic Pulse Forum</span>
                    <h2 style="margin-top:18px;font-size:clamp(2.4rem,7vw,5rem);line-height:1;color:var(--blue-dark);">Welcome</h2>
                    <p style="margin:18px auto 26px;color:var(--muted);font-size:1.08rem;font-weight:650;line-height:1.7;">
                        Welcome to Academic Pulse Forum. Sign up to join the discussion or log in to continue.
                    </p>
                    <div class="ap-nav" style="justify-content:center;">
                        @if (Route::has('register'))
                            <a class="ap-btn primary" href="{{ route('register') }}"><i class="fas fa-user-plus"></i> Sign Up</a>
                        @endif
                        @if (Route::has('login'))
                            <a class="ap-btn light" href="{{ route('login') }}"><i class="fas fa-sign-in-alt"></i> Login</a>
                        @endif
                    </div>
                </div>
            </section>
        </main>

        <footer class="ap-footer">
            <span><i class="fas fa-graduation-cap"></i> Academic Pulse Forum</span>
            <span>Laravel Blade templates</span>
        </footer>
    </div>
</x-layouts.academic-pulse>
