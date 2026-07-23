@php
    $endsAt = $quiz->endsAt();
@endphp
<x-layouts.academic-pulse title="{{ $quiz->title }}">
    <div class="form-shell">
        <div class="ap-card" style="width:min(760px, 100%); padding:32px;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap; margin-bottom:8px;">
                <div>
                    <h1 style="font-size:1.4rem;">{{ $quiz->title }}</h1>
                    <p style="color:var(--muted); font-weight:650; margin-top:4px;">{{ $quiz->subject }} &middot; {{ $quiz->questions->count() }} question(s){{ $quiz->proctored ? ' · Proctored' : '' }}</p>
                </div>
                @if ($endsAt)
                    <span class="notice" id="quiz-countdown" style="margin:0;"><i class="fas fa-clock"></i> <span id="quiz-countdown-text">Ends at {{ $endsAt->format('g:i A') }}</span></span>
                @endif
            </div>

            @if ($errors->any())
                <div class="error-list">{{ $errors->first() }}</div>
            @endif

            @if ($quiz->proctored)
                <div id="quiz-proctor-warning" class="error-list" style="display:none; margin-top:12px;"></div>

                <div id="quiz-proctor-gate" class="notice" style="margin-top:18px; display:grid; gap:12px; justify-items:start;">
                    <p style="font-weight:700;"><i class="fas fa-shield-halved"></i> This is a proctored quiz.</p>
                    <p style="font-weight:600;">Leaving fullscreen or switching tabs will be recorded as a warning. After 3 warnings your quiz will be auto-submitted as-is.</p>
                    <button type="button" id="quiz-proctor-start-btn" class="ap-btn primary"><i class="fas fa-expand"></i> Enter fullscreen &amp; begin</button>
                </div>
            @endif

            <form id="quiz-take-form" action="{{ route('quizzes.submit', $quiz) }}" method="POST" style="margin-top:18px; display:{{ $quiz->proctored ? 'none' : 'grid' }}; gap:22px;" @if ($endsAt) data-ends-at="{{ $endsAt->toIso8601String() }}" @endif>
                @csrf
                <input type="hidden" name="violations" id="quiz-violations-input" value="0">

                @foreach ($quiz->questions as $index => $question)
                    <div class="field" style="margin-bottom:0;">
                        <label style="font-size:.92rem; color:var(--text); margin-bottom:10px;">{{ $index + 1 }}. {{ $question->question }}</label>
                        <div style="display:grid; gap:8px; margin-top:8px;">
                            @foreach ($question->options() as $letter => $text)
                                <label style="display:flex; align-items:center; gap:10px; padding:10px 12px; border:2px solid var(--line); border-radius:12px; cursor:pointer; font-weight:600;">
                                    <input type="radio" name="answers[{{ $question->id }}]" value="{{ $letter }}" required>
                                    {{ strtoupper($letter) }}. {{ $text }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <button type="submit" id="quiz-submit-btn" class="ap-btn primary" style="align-self:flex-start;"><i class="fas fa-paper-plane"></i> Submit quiz</button>
            </form>
        </div>
    </div>

    <script>
        (function () {
            const form = document.getElementById('quiz-take-form');
            const endsAtRaw = form?.dataset.endsAt;

            if (!form || !endsAtRaw) {
                return;
            }

            const endsAt = new Date(endsAtRaw).getTime();
            const countdownText = document.getElementById('quiz-countdown-text');
            const countdownBadge = document.getElementById('quiz-countdown');
            const submitBtn = document.getElementById('quiz-submit-btn');
            let autoSubmitted = false;

            const submitNow = (reason) => {
                if (autoSubmitted) {
                    return;
                }

                autoSubmitted = true;
                countdownText.textContent = reason;
                // Submit first: disabled fields are excluded from the payload,
                // so answers must be captured before anything is disabled.
                form.submit();
                form.querySelectorAll('input, button').forEach((el) => { el.disabled = true; });
            };

            const tick = () => {
                const remainingMs = endsAt - Date.now();

                if (remainingMs <= 0) {
                    clearInterval(intervalId);
                    submitNow("Time's up — submitting...");

                    return;
                }

                const totalSeconds = Math.floor(remainingMs / 1000);
                const minutes = Math.floor(totalSeconds / 60);
                const seconds = totalSeconds % 60;
                countdownText.textContent = `Time left: ${minutes}:${String(seconds).padStart(2, '0')}`;

                if (totalSeconds <= 60) {
                    countdownBadge.style.color = '#dc2626';
                    countdownBadge.style.fontWeight = '800';
                }
            };

            const intervalId = setInterval(tick, 1000);
            tick();

            form.addEventListener('submit', () => {
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Submitting…';
                }
            });

            const MAX_VIOLATIONS = 3;
            const gate = document.getElementById('quiz-proctor-gate');
            const startBtn = document.getElementById('quiz-proctor-start-btn');
            const warningBox = document.getElementById('quiz-proctor-warning');
            const violationsInput = document.getElementById('quiz-violations-input');

            if (!gate || !startBtn) {
                return;
            }

            let violations = 0;
            let proctoringActive = false;

            const recordViolation = (label) => {
                if (!proctoringActive || autoSubmitted) {
                    return;
                }

                violations += 1;
                violationsInput.value = violations;

                if (violations >= MAX_VIOLATIONS) {
                    warningBox.style.display = 'block';
                    warningBox.textContent = `Final warning (${violations}/${MAX_VIOLATIONS}): ${label}. Submitting your quiz now.`;
                    submitNow('Too many proctoring warnings — submitting...');

                    return;
                }

                warningBox.style.display = 'block';
                warningBox.textContent = `Warning ${violations}/${MAX_VIOLATIONS}: ${label}. Stay in fullscreen and on this tab.`;
            };

            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    recordViolation('you switched away from the quiz tab');
                }
            });

            document.addEventListener('fullscreenchange', () => {
                if (proctoringActive && !document.fullscreenElement) {
                    recordViolation('you exited fullscreen');
                }
            });

            startBtn.addEventListener('click', () => {
                const request = document.documentElement.requestFullscreen?.();

                const begin = () => {
                    proctoringActive = true;
                    gate.style.display = 'none';
                    form.style.display = 'grid';
                };

                if (request && typeof request.then === 'function') {
                    request.then(begin).catch(begin);
                } else {
                    begin();
                }
            });
        })();
    </script>
</x-layouts.academic-pulse>
