<x-layouts::auth :title="__('Log in')">
    <div style="display: flex; flex-direction: column; gap: 16px;">
        
        <x-auth-header :title="__('Smart Discussion Forum')" :description="__('Enter your university details below to connect.')" />

        <div id="errorMsg" style="background: rgba(239, 68, 68, 0.06); color: #ef4444; padding: 8px 12px; border-radius: 8px; font-size: 0.75rem; display: none; border-left: 4px solid #ef4444; font-weight: 600;">
            <i class="fas fa-exclamation-circle"></i> <span id="errorText"></span>
        </div>

        <form id="loginForm" method="POST" action="{{ route('login') }}" style="display: flex; flex-direction: column; gap: 12px;">
            @csrf

            <div class="form-group">
                <label>{{ __('Email Address') }}</label>
                <div class="input-wrapper">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" placeholder="university@pulse.mak.ac.ug" required autofocus autocomplete="email">
                </div>
            </div>

            <div class="form-group">
                <label>{{ __('Password') }}</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" placeholder="••••••••" required autocomplete="current-password">
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.75rem; color: var(--color-text-muted); margin: 4px 0;">
                <label style="display: flex; align-items: center; gap: 4px; cursor: pointer;">
                    <input type="checkbox" name="remember" style="accent-color: var(--color-gold-primary);"> {{ __('Remember me') }}
                </label>
                <a href="#" style="color: var(--color-gold-primary); text-decoration: none; font-weight: 600;">{{ __('Forgot password?') }}</a>
            </div>

            <button type="submit" class="btn-gold" id="submitBtn">
                <i class="fas fa-sign-in-alt"></i> {{ __('Log in') }}
            </button>
        </form>

        <div style="text-align: center; font-size: 0.8rem; color: var(--color-text-muted); margin-top: 8px;">
            <span>{{ __('Don\'t have an account?') }}</span>
            <a href="{{ url('/register') }}" style="color: var(--color-gold-primary); text-decoration: none; font-weight: 700; margin-left: 4px;">
                {{ __('Sign up') }}
            </a>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const errorMsg = document.getElementById('errorMsg');
            const errorText = document.getElementById('errorText');
            const btn = document.getElementById('submitBtn');

            errorMsg.style.display = 'none';
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Authenticating...';
            btn.disabled = true;

            try {
                const response = await fetch("{{ url('/api/login') }}", {
                    method: "POST",
                    headers: { "Content-Type": "application/json", "Accept": "application/json" },
                    body: JSON.stringify({ email, password })
                });
                const data = await response.json();
                if (response.ok) {
                    sessionStorage.setItem('academic_pulse_token', data.access_token);
                    sessionStorage.setItem('user_profile', JSON.stringify(data.user));
                    window.location.href = "{{ url('/dashboard') }}";
                } else {
                    throw new Error(data.message || 'Invalid credentials.');
                }
            } catch (err) {
                errorText.textContent = err.message;
                errorMsg.style.display = 'block';
                btn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Log in';
                btn.disabled = false;
            }
        });
    </script>
</x-layouts::auth>