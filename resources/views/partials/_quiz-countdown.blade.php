@php
    $quizWatchForJs = $quizWatchForJs ?? collect();
    $nextQuizForCountdown = $quizWatchForJs->sortBy('startsAt')->first();
@endphp
@if ($nextQuizForCountdown)
    <div class="pulse-card pulse-pad" style="margin-bottom:18px; display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap;">
        <div>
            <div class="pulse-section-head" style="margin-bottom:2px;">
                <h2><i class="fas fa-hourglass-half"></i> Next Quiz</h2>
            </div>
            <p style="font-weight:700;">{{ $nextQuizForCountdown['title'] }}</p>
            <p class="pulse-muted" style="font-weight:600;">{{ $nextQuizForCountdown['subject'] }} &middot; opens for you when the timer ends</p>
        </div>
        <div style="text-align:right;">
            <div class="quiz-countdown-timer" data-starts-at="{{ $nextQuizForCountdown['startsAt'] }}" style="font-size:1.6rem; font-weight:800; color:var(--pulse-blue, inherit); font-variant-numeric:tabular-nums;">--:--:--</div>
            <p class="pulse-muted" style="font-size:.76rem; font-weight:600;">Time until you can enter the quiz</p>
        </div>
    </div>

    <script>
        (function () {
            document.querySelectorAll('.quiz-countdown-timer').forEach(function (el) {
                if (el.dataset.countdownBound) {
                    return;
                }

                el.dataset.countdownBound = '1';

                const target = new Date(el.dataset.startsAt).getTime();

                const tick = function () {
                    const remaining = target - Date.now();

                    if (remaining <= 0) {
                        el.textContent = 'Starting now…';
                        clearInterval(intervalId);

                        return;
                    }

                    const totalSeconds = Math.floor(remaining / 1000);
                    const hours = Math.floor(totalSeconds / 3600);
                    const minutes = Math.floor((totalSeconds % 3600) / 60);
                    const seconds = totalSeconds % 60;

                    el.textContent = String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
                };

                const intervalId = setInterval(tick, 1000);
                tick();
            });
        })();
    </script>
@endif
