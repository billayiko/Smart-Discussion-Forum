@php
    $pct = $attempt && $attempt->total > 0 ? (int) round(($attempt->score / $attempt->total) * 100) : null;
    $canConfirm = auth()->user()->can('update', $quiz);
    $backRoute = match (auth()->user()->role) {
        'lecturer' => 'quizzes.index',
        'admin' => 'admin.dashboard',
        default => 'student.dashboard',
    };
@endphp
<x-layouts.academic-pulse title="{{ $quiz->title }} - Result">
    <div class="form-shell">
        <div class="ap-card" style="width:min(760px, 100%); padding:32px;">
            <h1 style="font-size:1.4rem;">{{ $quiz->title }} &mdash; Result</h1>
            <p style="color:var(--muted); font-weight:650; margin-top:4px;">{{ $quiz->subject }}</p>

            @if ($attempt)
                <div class="notice" style="margin-top:18px; font-size:1rem;">
                    Score: {{ $attempt->score }} / {{ $attempt->total }} ({{ $pct }}%)
                </div>
                @if ($attempt->proctoring_violations > 0)
                    <div class="error-list" style="margin-top:12px;">
                        <i class="fas fa-triangle-exclamation"></i> {{ $attempt->proctoring_violations }} proctoring warning(s) were recorded during this attempt.
                    </div>
                @endif
            @else
                <div class="error-list" style="margin-top:18px;">
                    <i class="fas fa-circle-info"></i> You did not attempt this quiz before it closed.
                </div>
            @endif

            <div class="notice" style="margin-top:14px; background:var(--bg); color:var(--text);">
                <strong>Class report:</strong> {{ $report['attempts_count'] }} student(s) attempted
                &middot; average score {{ $report['average_score_percent'] !== null ? $report['average_score_percent'].'%' : '—' }}
            </div>

            @if ($canConfirm)
                <div class="notice" style="margin-top:14px; {{ $quiz->marksConfirmed() ? 'background:#f0fdf4; color:#166534;' : 'background:#fffbeb; color:#92400e;' }}">
                    @if ($quiz->marksConfirmed())
                        <i class="fas fa-circle-check"></i> Marks confirmed on {{ $quiz->marks_confirmed_at->format('M j, Y g:i A') }} &mdash; visible to admin.
                    @else
                        <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
                            <span><i class="fas fa-triangle-exclamation"></i> Not yet confirmed &mdash; admin cannot see these marks until you confirm them.</span>
                            <form method="POST" action="{{ route('quizzes.confirm-marks', $quiz) }}">
                                @csrf
                                <button type="submit" class="ap-btn primary"><i class="fas fa-clipboard-check"></i> Confirm marks</button>
                            </form>
                        </div>
                    @endif
                </div>
            @endif

            @if ($report['top_scorers']->isNotEmpty())
                <div style="margin-top:14px; display:grid; gap:8px;">
                    @foreach ($report['top_scorers'] as $index => $top)
                        @php $topPct = $top->total > 0 ? (int) round(($top->score / $top->total) * 100) : 0; @endphp
                        <div style="display:flex; justify-content:space-between; padding:8px 12px; border:2px solid var(--line); border-radius:10px; font-weight:650;">
                            <span>{{ $index + 1 }}. {{ $top->user->name }}</span>
                            <span>{{ $top->score }}/{{ $top->total }} ({{ $topPct }}%)</span>
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($attempt)
                <div style="margin-top:22px; display:grid; gap:16px;">
                    @foreach ($quiz->questions as $index => $question)
                        @php
                            $given = $attempt->answers[$question->id] ?? null;
                            $correct = $given === $question->correct_option;
                        @endphp
                        <div style="padding:14px; border:2px solid var(--line); border-radius:12px;">
                            <p style="font-weight:700;">{{ $index + 1 }}. {{ $question->question }}</p>
                            <p style="margin-top:8px; font-weight:650; color: {{ $correct ? '#166534' : '#b91c1c' }};">
                                <i class="fas {{ $correct ? 'fa-circle-check' : 'fa-circle-xmark' }}"></i>
                                Your answer: {{ $given ? strtoupper($given).'. '.$question->options()[$given] : 'Not answered' }}
                            </p>
                            @unless ($correct)
                                <p style="margin-top:4px; font-weight:650; color:#166534;">
                                    Correct answer: {{ strtoupper($question->correct_option) }}. {{ $question->options()[$question->correct_option] }}
                                </p>
                            @endunless
                        </div>
                    @endforeach
                </div>
            @endif

            <a href="{{ route($backRoute) }}" class="ap-btn primary" style="margin-top:24px; display:inline-flex;"><i class="fas fa-house"></i> Back to dashboard</a>
        </div>
    </div>
</x-layouts.academic-pulse>
