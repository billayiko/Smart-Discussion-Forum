<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; color: #14213d; font-size: 12px; }
        h1 { font-size: 20px; margin-bottom: 4px; }
        .meta { color: #7182a8; font-size: 11px; margin-bottom: 20px; }
        .thread { margin-bottom: 22px; padding-bottom: 14px; border-bottom: 1px solid #dbe2ee; }
        .thread h2 { font-size: 15px; margin: 0 0 4px; }
        .thread .by { color: #7182a8; font-size: 10px; margin-bottom: 8px; }
        .thread p { margin: 0 0 10px; line-height: 1.5; }
        .answer { margin: 0 0 8px 18px; padding: 8px 10px; background: #f6f7fb; border-radius: 6px; }
        .answer .by { color: #7182a8; font-size: 10px; margin-bottom: 4px; }
        .empty { color: #7182a8; font-style: italic; }
    </style>
</head>
<body>
    <h1>{{ $topic->title }}</h1>
    <div class="meta">
        Exported {{ now()->format('F j, Y \a\t g:i A') }}
        @if ($topic->lecturer) &middot; Lecturer: {{ $topic->lecturer->name }} @endif
        &middot; {{ $threads->count() }} discussion thread(s)
    </div>

    @forelse ($threads as $thread)
        <div class="thread">
            <h2>{{ $thread->title }}</h2>
            <div class="by">Asked by {{ $thread->user->name }} &middot; {{ $thread->created_at->format('M j, Y g:i A') }}</div>
            <p>{{ $thread->body }}</p>

            @forelse ($thread->answers as $answer)
                <div class="answer">
                    <div class="by">{{ $answer->user->name }} replied &middot; {{ $answer->created_at->format('M j, Y g:i A') }}</div>
                    <div>{{ $answer->body }}</div>
                </div>
            @empty
                <p class="empty">No replies yet.</p>
            @endforelse
        </div>
    @empty
        <p class="empty">No discussions in this topic yet.</p>
    @endforelse
</body>
</html>
