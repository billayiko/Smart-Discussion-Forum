@php
    $quizWatchForJs = $quizWatchForJs ?? collect();
@endphp
@if ($quizWatchForJs->isNotEmpty())
    <script>
        (function () {
            const quizzes = @json($quizWatchForJs);

            quizzes.forEach(function (quiz) {
                const delay = new Date(quiz.startsAt).getTime() - Date.now();

                if (delay <= 0) {
                    window.location.href = quiz.url;
                    return;
                }

                if (delay < 24 * 60 * 60 * 1000) {
                    setTimeout(function () {
                        window.location.href = quiz.url;
                    }, delay);
                }
            });
        })();
    </script>
@endif
