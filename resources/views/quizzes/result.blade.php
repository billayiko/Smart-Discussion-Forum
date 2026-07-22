@php
    $pct = $attempt->total > 0 ? (int) round(($attempt->score / $attempt->total) * 100) : 0;
@endphp
<x-layouts.academic-pulse title="{{ $quiz->title }} - Result">
    <div class="form-shell">
        <div class="ap-card" style="width:min(760px, 100%); padding:32px;">
            <h1 style="font-size:1.4rem;">{{ $quiz->title }} &mdash; Result</h1>
            <p style="color:var(--muted); font-weight:650; margin-top:4px;">{{ $quiz->subject }}</p>

            <div class="notice" style="margin-top:18px; font-size:1rem;">
                Score: {{ $attempt->score }} / {{ $attempt->total }} ({{ $pct }}%)
            </div>

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

            <a href="{{ route('student.dashboard') }}" class="ap-btn primary" style="margin-top:24px; display:inline-flex;"><i class="fas fa-house"></i> Back to dashboard</a>
        </div>
    </div>
</x-layouts.academic-pulse>
