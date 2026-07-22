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
                    <span class="notice" style="margin:0;"><i class="fas fa-clock"></i> Ends at {{ $endsAt->format('g:i A') }}</span>
                @endif
            </div>

            @if ($errors->any())
                <div class="error-list">{{ $errors->first() }}</div>
            @endif

            <form action="{{ route('quizzes.submit', $quiz) }}" method="POST" style="margin-top:18px; display:grid; gap:22px;">
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

                <button type="submit" class="ap-btn primary" style="align-self:flex-start;"><i class="fas fa-paper-plane"></i> Submit quiz</button>
            </form>
        </div>
    </div>
</x-layouts.academic-pulse>
