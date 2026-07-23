<x-layouts.academic-pulse title="Finish your account">
    <main class="pulse-page pulse-auth">
        <section class="pulse-auth-side">
            <a class="pulse-logo" href="{{ route('home') }}">
                <i class="fas fa-graduation-cap"></i>
                <span>Academic<span>Pulse Forum</span></span>
            </a>

            <div class="pulse-auth-copy">
                <h1>Almost there!</h1>
                <p>Tell us a bit more about yourself to finish setting up your account.</p>
            </div>

            <div class="pulse-illustration" aria-hidden="true"></div>
        </section>

        <section class="pulse-card pulse-auth-card">
            <h2>Finish your account</h2>

            @if ($errors->any())
                <div class="pulse-alert"><i class="fas fa-circle-exclamation"></i> {{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('onboarding.update') }}" class="pulse-form">
                @csrf
                @method('PATCH')

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

                <fieldset class="pulse-field" style="border:0; padding:0; margin:0;">
                    <legend style="color:var(--pulse-ink); font-size:.78rem; font-weight:850; padding:0;">Security question</legend>
                    <p style="margin:0 0 8px;color:var(--pulse-muted);font-size:0.92em;">Choose one question and answer it. You'll use this to recover your account if you forget your password.</p>

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
                        <input id="security_answer" name="security_answer" type="text" value="{{ old('security_answer') }}" placeholder="Enter your answer" required autocomplete="off">
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

                <button type="submit" class="pulse-btn" style="width:100%;">Continue to my dashboard</button>
            </form>

            <form method="POST" action="{{ route('onboarding.decline') }}" style="margin-top:10px;" onsubmit="return confirm('Decline the platform rules? Your registration will be cancelled and your account deleted.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="pulse-btn light" style="width:100%;">I do not agree — cancel my registration</button>
            </form>
        </section>
    </main>
</x-layouts.academic-pulse>
