@php
    $endsAt = $quiz->endsAt();
@endphp
<x-layouts.academic-pulse title="{{ $quiz->title }}">
    <div class="form-shell">
        <div class="ap-card" style="width:min(760px, 100%); padding:32px;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap; margin-bottom:8px;">
                <div>
                    <h1 style="font-size:1.4rem;">{{ $quiz->title }}</h1>
                    <p style="color:var(--muted); font-weight:650; margin-top:4px;">{{ $quiz->subject }} &middot; {{ $quiz->questions->count() }} question(s)</p>
                </div>
                @if ($endsAt)
                    <span class="notice" id="quiz-countdown" style="margin:0;"><i class="fas fa-clock"></i> <span id="quiz-countdown-text">Ends at {{ $endsAt->format('g:i A') }}</span></span>
                @endif
            </div>

            @if ($errors->any())
                <div class="error-list">{{ $errors->first() }}</div>
            @endif

            <form id="quiz-take-form" action="{{ route('quizzes.submit', $quiz) }}" method="POST" style="margin-top:18px; display:grid; gap:22px;" @if ($endsAt) data-ends-at="{{ $endsAt->toIso8601String() }}" @endif>
                @csrf
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

            const tick = () => {
                const remainingMs = endsAt - Date.now();

                if (remainingMs <= 0) {
                    clearInterval(intervalId);

                    if (!autoSubmitted) {
                        autoSubmitted = true;
                        countdownText.textContent = "Time's up — submitting...";
                        // Submit first: disabled fields are excluded from the payload,
                        // so answers must be captured before anything is disabled.
                        form.submit();
                        form.querySelectorAll('input, button').forEach((el) => { el.disabled = true; });
                    }

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
        })();
    </script>
</x-layouts.academic-pulse>
