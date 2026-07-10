<x-layouts.academic-pulse title="Log in">
    <main class="pulse-page pulse-auth">
        <section class="pulse-auth-side">
            <a class="pulse-logo" href="{{ route('home') }}">
                <i class="fas fa-graduation-cap"></i>
                <span>Academic<span>Pulse Forum</span></span>
            </a>

            <div class="pulse-auth-copy">
                <h1>Welcome back!</h1>
                <p>Sign in to continue your learning journey.</p>
            </div>

            <div class="pulse-illustration" aria-hidden="true"></div>
        </section>

        <section class="pulse-card pulse-auth-card">
            <h2>Log in to your account</h2>

            <x-auth-session-status class="notice" :status="session('status')" />

            @if ($teamInvitation)
                <x-team-invitation-alert :invitation="$teamInvitation" :action="__('Log in')" />
            @endif

            @if ($errors->any())
                <div class="pulse-alert"><i class="fas fa-circle-exclamation"></i> {{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="pulse-form">
                @csrf

                <label class="pulse-field" for="email">
                    <span>Email address</span>
                    <span class="pulse-input">
                        <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="Enter your email" required autofocus autocomplete="email">
                    </span>
                </label>

                <label class="pulse-field" for="password">
                    <span>Password</span>
                    <span class="pulse-input">
                        <input id="password" name="password" type="password" placeholder="Enter your password" required autocomplete="current-password">
                        <i class="fas fa-eye"></i>
                    </span>
                </label>

                <div class="pulse-form-row">
                    <label><input type="checkbox" name="remember" @checked(old('remember'))> Remember me</label>
                    @if (Route::has('password.request'))
                        <a style="color:var(--pulse-blue);" href="{{ route('password.request') }}">Forgot password?</a>
                    @endif
                </div>

                <button type="submit" class="pulse-btn" style="width:100%;">Log In</button>

                <div class="pulse-auth-divider">Or continue with</div>

                <div class="pulse-provider-grid">
                    <button type="button" class="pulse-btn light"><i class="fab fa-google"></i> Google</button>
                    <button type="button" class="pulse-btn light"><i class="fab fa-microsoft"></i> Microsoft</button>
                </div>
            </form>

            <p style="margin:28px 0 0;text-align:center;color:var(--pulse-muted);font-weight:750;">
                Don't have an account?
                <a style="color:var(--pulse-blue);" href="{{ $teamInvitation ? route('register', ['invitation' => $teamInvitation['code']]) : route('register') }}">Sign up</a>
            </p>
        </section>
    </main>
</x-layouts.academic-pulse>
