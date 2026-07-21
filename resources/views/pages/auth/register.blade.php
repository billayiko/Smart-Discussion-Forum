<x-layouts.academic-pulse title="Register">
    <main class="pulse-page pulse-auth">
        <section class="pulse-auth-side">
            <a class="pulse-logo" href="{{ route('home') }}">
                <i class="fas fa-graduation-cap"></i>
                <span>Academic<span>Pulse Forum</span></span>
            </a>

            <div class="pulse-auth-copy">
                <h1>Create your account</h1>
                <p>Join Academic Pulse Forum and start your learning journey.</p>
            </div>

            <div class="pulse-illustration" aria-hidden="true"></div>
        </section>

        <section class="pulse-card pulse-auth-card">
            <h2>Create your account</h2>

            <x-auth-session-status class="notice" :status="session('status')" />

            @if ($teamInvitation)
                <x-team-invitation-alert :invitation="$teamInvitation" :action="__('Register')" />
            @endif

            @if ($errors->any())
                <div class="pulse-alert"><i class="fas fa-circle-exclamation"></i> {{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('register.store') }}" class="pulse-form">
                @csrf

                <label class="pulse-field" for="name">
                    <span>Full name</span>
                    <span class="pulse-input">
                        <input id="name" name="name" type="text" value="{{ old('name') }}" placeholder="Enter your full name" required autofocus autocomplete="name">
                    </span>
                </label>

                <label class="pulse-field" for="email">
                    <span>Email address</span>
                    <span class="pulse-input">
                        <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="Enter your email" required autocomplete="email">
                    </span>
                </label>

                <label class="pulse-field" for="role">
                    <span>Role</span>
                    <span class="pulse-input">
                        <select id="role" name="role" required>
                            <option value="">Select your role</option>
                            <option value="student" @selected(old('role') === 'student')>Student</option>
                            <option value="lecturer" @selected(old('role') === 'lecturer')>Lecturer</option>
                        </select>
                    </span>
                </label>

                <label class="pulse-field" for="password">
                    <span>Password</span>
                    <span class="pulse-input">
                        <input id="password" name="password" type="password" placeholder="Create a password" required autocomplete="new-password">
                        <i class="fas fa-eye" data-toggle-password="password" role="button" tabindex="0" style="cursor:pointer;" aria-label="Show password"></i>
                    </span>
                </label>

                <label class="pulse-field" for="password_confirmation">
                    <span>Confirm password</span>
                    <span class="pulse-input">
                        <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Confirm your password" required autocomplete="new-password">
                        <i class="fas fa-eye" data-toggle-password="password_confirmation" role="button" tabindex="0" style="cursor:pointer;" aria-label="Show password"></i>
                    </span>
                </label>

                <details class="pulse-field" style="background:var(--pulse-surface-2, #f6f7f9); border-radius:10px; padding:12px 14px;">
                    <summary style="cursor:pointer; font-weight:700;">Platform rules (read before joining)</summary>
                    <ul style="margin:10px 0 0; padding-left:18px; color:var(--pulse-muted); font-size:0.92em; line-height:1.6;">
                        <li>Keep discussion on-topic; don't flood the forum with unrelated material.</li>
                        <li>Be respectful — harassment or abusive language may result in warnings or a blacklist.</li>
                        <li>Members inactive for a prolonged period will receive up to two warnings before a temporary blacklist.</li>
                        <li>Content you post may be visible to other members of your group and can be exported by them.</li>
                    </ul>
                </details>

                <label class="pulse-form-row" style="justify-content:flex-start;">
                    <input type="checkbox" name="rules_agreement" value="1" required @checked(old('rules_agreement'))>
                    <span>I have read and agree to the platform rules above</span>
                </label>

                <button type="submit" class="pulse-btn" style="width:100%;">Create Account</button>
            </form>

            <p style="margin:28px 0 0;text-align:center;color:var(--pulse-muted);font-weight:750;">
                Already have an account?
                <a style="color:var(--pulse-blue);" href="{{ $teamInvitation ? route('login', ['invitation' => $teamInvitation['code']]) : route('login') }}">Log in</a>
            </p>
        </section>
    </main>
</x-layouts.academic-pulse>
