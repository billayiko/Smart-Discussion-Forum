<x-layouts.academic-pulse title="Forgot password">
    <main class="pulse-page pulse-auth">
        <section class="pulse-auth-side">
            <a class="pulse-logo" href="{{ route('home') }}">
                <i class="fas fa-graduation-cap"></i>
                <span>Academic<span>Pulse Forum</span></span>
            </a>

            <div class="pulse-auth-copy">
                <h1>Forgot your password?</h1>
                <p>Verify your identity by answering your security question, then set a new password.</p>
            </div>

            <div class="pulse-illustration" aria-hidden="true"></div>
        </section>

        <section class="pulse-card pulse-auth-card">
            <h2>Verify your identity</h2>

            <x-auth-session-status class="notice" :status="session('status')" />

            @if ($errors->any())
                <div class="pulse-alert"><i class="fas fa-circle-exclamation"></i> {{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('password.verify') }}" class="pulse-form">
                @csrf

                <label class="pulse-field" for="email">
                    <span>Email address</span>
                    <span class="pulse-input">
                        <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="Enter your registered email" required autofocus autocomplete="email">
                    </span>
                </label>

                <fieldset class="pulse-field" style="border:0; padding:0; margin:0;">
                    <legend style="color:var(--pulse-ink); font-size:.78rem; font-weight:850; padding:0;">Security question</legend>

                    @foreach ($securityQuestions as $key => $label)
                        <label class="pulse-form-row" style="justify-content:flex-start;">
                            <input type="radio" name="security_question" value="{{ $key }}" required @checked(old('security_question') === $key)>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </fieldset>

                <label class="pulse-field" for="security_answer">
                    <span>Your answer</span>
                    <span class="pulse-input">
                        <input id="security_answer" name="security_answer" type="text" placeholder="Enter your answer" required autocomplete="off">
                    </span>
                </label>

                <button type="submit" class="pulse-btn" style="width:100%;" data-test="verify-security-answer-button">Verify and continue</button>
            </form>

            <p style="margin:28px 0 0;text-align:center;color:var(--pulse-muted);font-weight:750;">
                Remembered your password?
                <a style="color:var(--pulse-blue);" href="{{ route('login') }}">Log in</a>
            </p>
        </section>
    </main>
</x-layouts.academic-pulse>
