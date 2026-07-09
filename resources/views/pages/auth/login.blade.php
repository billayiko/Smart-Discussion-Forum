<<<<<<< HEAD
<x-layouts::auth :title="__('Log in')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Log in to your account')" :description="__('Enter your email and password below to log in')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        @if ($teamInvitation)
            <x-team-invitation-alert :invitation="$teamInvitation" :action="__('Log in')" />
        @endif

        <x-passkey-verify />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Password -->
            <div class="relative">
                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Password')"
                    viewable
                />

                @if (Route::has('password.request'))
                    <flux:link class="absolute top-0 text-sm end-0" :href="route('password.request')" wire:navigate>
                        {{ __('Forgot your password?') }}
                    </flux:link>
                @endif
=======
<x-layouts.academic-pulse title="Login">
    <main class="form-shell">
        <section class="ap-card form-card">
            <div class="form-logo">
                <span class="ap-logo"><i class="fas fa-graduation-cap"></i></span>
                <h1>Academic<span>Pulse</span></h1>
                <p style="color:var(--muted);font-weight:650;margin-top:4px;"><i class="fas fa-comments" style="color:var(--gold);"></i> Smart Discussion Forum</p>
>>>>>>> b198b3de1229c4eca8e9ade11f3a2b6efadc396a
            </div>

            <x-auth-session-status class="notice" :status="session('status')" />

            @if ($errors->any())
                <div class="error-list">
                    <i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}
                </div>
            @endif

            @if ($teamInvitation)
                <x-team-invitation-alert :invitation="$teamInvitation" :action="__('Log in')" />
            @endif

            <form method="POST" action="{{ route('login.store') }}">
                @csrf
                <div class="field">
                    <label for="email"><i class="fas fa-envelope" style="color:var(--gold);"></i> Email</label>
                    <div class="input-wrap">
                        <i class="fas fa-envelope"></i>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="university@example.com" required autofocus autocomplete="email">
                    </div>
                </div>

                <div class="field">
                    <label for="password"><i class="fas fa-lock" style="color:var(--gold);"></i> Password</label>
                    <div class="input-wrap">
                        <i class="fas fa-lock"></i>
                        <input id="password" name="password" type="password" placeholder="Password" required autocomplete="current-password">
                    </div>
                </div>

                <div class="form-row">
                    <label><input type="checkbox" name="remember" @checked(old('remember'))> Remember me</label>
                    @if (Route::has('password.request'))
                        <a style="color:var(--gold);font-weight:800;" href="{{ route('password.request') }}">Forgot?</a>
                    @endif
                </div>

                <button type="submit" class="ap-btn primary" style="width:100%;"><i class="fas fa-sign-in-alt"></i> Sign In</button>
            </form>

            <div style="text-align:center;margin-top:14px;color:var(--muted);font-size:.86rem;font-weight:650;">
                No account?
                <a style="color:var(--gold);font-weight:800;" href="{{ $teamInvitation ? route('register', ['invitation' => $teamInvitation['code']]) : route('register') }}">Register</a>
            </div>
<<<<<<< HEAD
        </form>

        <div class="space-x-1 text-sm text-center rtl:space-x-reverse text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Don\'t have an account?') }}</span>
            <flux:link
                :href="$teamInvitation ? route('register', ['invitation' => $teamInvitation['code']]) : route('register')"
                data-test="register-link"
                wire:navigate
            >
                {{ __('Sign up') }}
            </flux:link>
        </div>
    </div>
</x-layouts::auth>
=======
        </section>
    </main>
</x-layouts.academic-pulse>
>>>>>>> b198b3de1229c4eca8e9ade11f3a2b6efadc396a
