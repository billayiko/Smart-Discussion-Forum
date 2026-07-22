<x-layouts.academic-pulse title="Forgot password">
    <main class="pulse-page pulse-auth">
        <section class="pulse-auth-side">
            <a class="pulse-logo" href="{{ route('home') }}">
                <i class="fas fa-graduation-cap"></i>
                <span>Academic<span>Pulse Forum</span></span>
            </a>

            <div class="pulse-auth-copy">
                <h1>Forgot your password?</h1>
                <p>Enter your registered email and we'll send you a secure link to set a new one.</p>
            </div>

            <div class="pulse-illustration" aria-hidden="true"></div>
        </section>

        <section class="pulse-card pulse-auth-card">
            <h2>Reset your password</h2>

            <x-auth-session-status class="notice" :status="session('status')" />

            @if ($errors->any())
                <div class="pulse-alert"><i class="fas fa-circle-exclamation"></i> {{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="pulse-form">
                @csrf

                <label class="pulse-field" for="email">
                    <span>Email address</span>
                    <span class="pulse-input">
                        <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="Enter your registered email" required autofocus autocomplete="email">
                    </span>
                </label>

                <button type="submit" class="pulse-btn" style="width:100%;" data-test="email-password-reset-link-button">Email password reset link</button>
            </form>

            <p style="margin:28px 0 0;text-align:center;color:var(--pulse-muted);font-weight:750;">
                Remembered your password?
                <a style="color:var(--pulse-blue);" href="{{ route('login') }}">Log in</a>
            </p>
        </section>
    </main>
</x-layouts.academic-pulse>
