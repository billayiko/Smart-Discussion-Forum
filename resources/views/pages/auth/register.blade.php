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
                            <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                        </select>
                    </span>
                </label>

                <label class="pulse-field" for="password">
                    <span>Password</span>
                    <span class="pulse-input">
                        <input id="password" name="password" type="password" placeholder="Create a password" required autocomplete="new-password">
                        <i class="fas fa-eye"></i>
                    </span>
                </label>

                <label class="pulse-field" for="password_confirmation">
                    <span>Confirm password</span>
                    <span class="pulse-input">
                        <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Confirm your password" required autocomplete="new-password">
                        <i class="fas fa-eye"></i>
                    </span>
                </label>

                <label class="pulse-form-row" style="justify-content:flex-start;">
                    <input type="checkbox" required>
                    <span>I agree to the <a style="color:var(--pulse-blue);" href="#">Terms of Service</a> and <a style="color:var(--pulse-blue);" href="#">Privacy Policy</a></span>
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
