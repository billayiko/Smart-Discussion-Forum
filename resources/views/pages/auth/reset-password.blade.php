<x-layouts.academic-pulse title="Reset password">
    <main class="pulse-page pulse-auth">
        <section class="pulse-auth-side">
            <a class="pulse-logo" href="{{ route('home') }}">
                <i class="fas fa-graduation-cap"></i>
                <span>Academic<span>Pulse Forum</span></span>
            </a>

            <div class="pulse-auth-copy">
                <h1>Set a new password</h1>
                <p>Choose a strong password you haven't used before on Academic Pulse Forum.</p>
            </div>

            <div class="pulse-illustration" aria-hidden="true"></div>
        </section>

        <section class="pulse-card pulse-auth-card">
            <h2>Reset password</h2>

            <x-auth-session-status class="notice" :status="session('status')" />

            @if ($errors->any())
                <div class="pulse-alert"><i class="fas fa-circle-exclamation"></i> {{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="pulse-form">
                @csrf
                <input type="hidden" name="token" value="{{ request()->route('token') }}">

                <label class="pulse-field" for="email">
                    <span>Email address</span>
                    <span class="pulse-input">
                        <input id="email" name="email" type="email" value="{{ old('email', request('email')) }}" placeholder="Enter your email" required autocomplete="email">
                    </span>
                </label>

                <label class="pulse-field" for="password">
                    <span>New password</span>
                    <span class="pulse-input">
                        <input id="password" name="password" type="password" placeholder="Create a new password" required autocomplete="new-password" passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}">
                        <i class="fas fa-eye" data-toggle-password="password" role="button" tabindex="0" style="cursor:pointer;" aria-label="Show password"></i>
                    </span>
                </label>

                <label class="pulse-field" for="password_confirmation">
                    <span>Confirm new password</span>
                    <span class="pulse-input">
                        <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Confirm your new password" required autocomplete="new-password">
                        <i class="fas fa-eye" data-toggle-password="password_confirmation" role="button" tabindex="0" style="cursor:pointer;" aria-label="Show password"></i>
                    </span>
                </label>

                <button type="submit" class="pulse-btn" style="width:100%;" data-test="reset-password-button">Reset password</button>
            </form>

            <p style="margin:28px 0 0;text-align:center;color:var(--pulse-muted);font-weight:750;">
                Remembered your password?
                <a style="color:var(--pulse-blue);" href="{{ route('login') }}">Log in</a>
            </p>
        </section>
    </main>
</x-layouts.academic-pulse>
