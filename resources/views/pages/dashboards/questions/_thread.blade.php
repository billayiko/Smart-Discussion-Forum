<a href="{{ route('questions.index') }}" class="pulse-muted" style="display:inline-block; margin-bottom:14px;"><i class="fas fa-arrow-left"></i> Back to Questions</a>

<section class="pulse-card pulse-pad">
    <div class="pulse-section-head">
        <h2>{{ $question->title }}</h2>
        @if ($question->answers->isEmpty())
            <span class="pulse-tag orange">Not answered</span>
        @else
            <span class="pulse-tag green">Answered</span>
        @endif
    </div>
    <p class="pulse-muted">{{ $question->topic->title ?? 'Other' }} &middot; asked by {{ $question->user->name }} &middot; {{ $question->created_at->diffForHumans() }}</p>
    <p style="margin-top:12px;">{{ $question->body }}</p>

    @if ($canDelete ?? false)
        <form method="POST" action="{{ route('questions.destroy', $question) }}" onsubmit="return confirm('Delete this question?');" style="margin-top:16px;">
            @csrf
            @method('DELETE')
            <button class="pulse-btn light" style="color:#d33;" type="submit"><i class="fas fa-trash"></i> Delete Question</button>
        </form>
    @endif

    @if ($canComplain ?? false)
        <details style="margin-top:16px;">
            <summary class="pulse-muted" style="cursor:pointer;"><i class="fas fa-flag"></i> Report this question</summary>
            <form method="POST" action="{{ route('questions.complaints.store', $question) }}" class="pulse-form" style="margin-top:10px;">
                @csrf
                <div class="pulse-field">
                    <div class="pulse-input" style="min-height:72px; align-items:flex-start; padding-top:10px;">
                        <textarea name="reason" rows="3" placeholder="Why are you reporting this question?" style="width:100%; border:0; outline:0; background:transparent; resize:vertical;" required></textarea>
                    </div>
                </div>
                <div>
                    <button class="pulse-btn light" type="submit"><i class="fas fa-flag"></i> Submit Report</button>
                </div>
            </form>
        </details>
    @endif
</section>

<section class="pulse-card pulse-pad" style="margin-top:18px;">
    <div class="pulse-section-head">
        <h2>Replies ({{ $question->answers->count() }})</h2>
    </div>
    <div class="pulse-list">
        @forelse ($question->answers as $answer)
            <div class="pulse-row" style="align-items:flex-start;">
                <span class="pulse-soft-icon"><i class="fas fa-reply"></i></span>
                <span><strong>{{ $answer->user->name }}</strong><p>{{ $answer->body }}</p><span class="pulse-muted">{{ $answer->created_at->diffForHumans() }}</span></span>
            </div>
        @empty
            <div class="pulse-row"><span class="pulse-soft-icon"><i class="fas fa-circle-question"></i></span><span><strong>No replies yet</strong><p>This question is still awaiting an answer.</p></span></div>
        @endforelse
    </div>

    @if ($canReply ?? false)
        <form method="POST" action="{{ route('questions.answers.store', $question) }}" class="pulse-form" style="margin-top:16px;">
            @csrf
            <div class="pulse-field">
                <label for="body">Your reply</label>
                <div class="pulse-input" style="min-height:96px; align-items:flex-start; padding-top:12px;">
                    <textarea id="body" name="body" rows="4" placeholder="Write a reply..." style="width:100%; border:0; outline:0; background:transparent; resize:vertical;" required></textarea>
                </div>
            </div>
            <div>
                <button class="pulse-btn" type="submit"><i class="fas fa-paper-plane"></i> Post Reply</button>
            </div>
        </form>
    @endif
</section>
