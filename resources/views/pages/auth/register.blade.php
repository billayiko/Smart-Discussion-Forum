<x-layouts.academic-pulse title="Register">
    <main class="form-shell">
        <section class="ap-card form-card">
            <div class="form-logo">
                <span class="ap-logo"><i class="fas fa-user-plus"></i></span>
                <h1>Academic<span>Pulse</span></h1>
                <p style="color:var(--muted);font-weight:650;margin-top:4px;"><i class="fas fa-user-edit" style="color:var(--gold);"></i> Create account</p>
            </div>

            <x-auth-session-status class="notice" :status="session('status')" />

            @if ($errors->any())
                <div class="error-list">
                    <i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}
                </div>
            @endif

            @if ($teamInvitation)
                <x-team-invitation-alert :invitation="$teamInvitation" :action="__('Register')" />
            @endif

            <form method="POST" action="{{ route('register.store') }}">
                @csrf
                <div class="field">
                    <label for="name"><i class="fas fa-user" style="color:var(--gold);"></i> Full Name</label>
                    <div class="input-wrap">
                        <i class="fas fa-user"></i>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" placeholder="John Doe" required autofocus autocomplete="name">
                    </div>
                </div>

                <div class="field">
                    <label for="email"><i class="fas fa-envelope" style="color:var(--gold);"></i> Email</label>
                    <div class="input-wrap">
                        <i class="fas fa-envelope"></i>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="university@example.com" required autocomplete="email">
                    </div>
                </div>

                <div class="field">
                    <label for="password"><i class="fas fa-lock" style="color:var(--gold);"></i> Password</label>
                    <div class="input-wrap">
                        <i class="fas fa-lock"></i>
                        <input id="password" name="password" type="password" placeholder="Minimum 8 characters" required autocomplete="new-password">
                    </div>
                </div>

                <div class="field">
                    <label for="password_confirmation"><i class="fas fa-check-circle" style="color:var(--gold);"></i> Confirm Password</label>
                    <div class="input-wrap">
                        <i class="fas fa-check-circle"></i>
                        <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Re-enter password" required autocomplete="new-password">
                    </div>
                </div>

                <div class="field">
                    <label for="role"><i class="fas fa-user-tag" style="color:var(--gold);"></i> Role</label>
                    <div class="input-wrap">
                        <i class="fas fa-user-tag"></i>
                        <select id="role" name="role">
                            <option value="student" @selected(old('role') === 'student')>Student</option>
                            <option value="lecturer" @selected(old('role') === 'lecturer')>Lecturer</option>
                            <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                        </select>
                    </div>
                </div>

                <label class="form-row" style="justify-content:flex-start;">
                    <input type="checkbox" required>
                    <span>I agree to the platform guidelines</span>
                </label>

                <button type="submit" class="ap-btn primary" style="width:100%;"><i class="fas fa-user-check"></i> Register</button>
            </form>

            <div style="text-align:center;margin-top:14px;color:var(--muted);font-size:.86rem;font-weight:650;">
                Have an account?
                <a style="color:var(--gold);font-weight:800;" href="{{ $teamInvitation ? route('login', ['invitation' => $teamInvitation['code']]) : route('login') }}">Sign In</a>
            </div>
        </section>
    </main>
</x-layouts.academic-pulse>
