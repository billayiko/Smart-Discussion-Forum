@if ($canAsk ?? false)
    <section class="pulse-card pulse-pad">
        <div class="pulse-section-head">
            <h2>Ask a question</h2>
        </div>
        <form class="pulse-form" method="POST" action="{{ route('questions.store') }}">
            @csrf
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
                <div class="pulse-field">
                    <label for="title">Title</label>
                    <div class="pulse-input">
                        <input id="title" type="text" name="title" placeholder="What's your question?" value="{{ old('title') }}" required>
                    </div>
                </div>
                <div class="pulse-field">
                    <label for="course_topic_id">Topic</label>
                    <div class="pulse-input">
                        <select id="course_topic_id" name="course_topic_id">
                            <option value="">Other / General</option>
                            @foreach ($topics as $topic)
                                <option value="{{ $topic->id }}" @selected(old('course_topic_id') == $topic->id)>{{ $topic->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="pulse-field">
                <label for="body">Details</label>
                <div class="pulse-input" style="min-height:96px; align-items:flex-start; padding-top:12px;">
                    <textarea id="body" name="body" rows="4" placeholder="Add more detail to your question..." style="width:100%; border:0; outline:0; background:transparent; resize:vertical;" required>{{ old('body') }}</textarea>
                </div>
            </div>
            <div>
                <button class="pulse-btn" type="submit"><i class="fas fa-paper-plane"></i> Post Question</button>
            </div>
        </form>
    </section>
@endif

@if ($unansweredCount > 0)
    <section class="pulse-card pulse-pad" style="margin-top:18px; border-left:4px solid var(--pulse-orange, #d9822b);">
        <strong><i class="fas fa-triangle-exclamation"></i> {{ $unansweredCount }} question(s) still awaiting an answer.</strong>
    </section>
@endif

<section class="pulse-card pulse-pad" style="margin-top:18px;">
    <div class="pulse-section-head">
        <h2>All Questions</h2>
        <span class="pulse-muted">{{ $questions->count() }} total</span>
    </div>
    <div style="overflow:auto;">
        <table class="pulse-table">
            <thead>
                <tr>
                    <th>Question</th>
                    <th>Topic</th>
                    <th>Asked by</th>
                    <th>Status</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($questions as $question)
                    <tr>
                        <td><strong>{{ $question->title }}</strong></td>
                        <td><span class="pulse-tag">{{ $question->topic->title ?? 'Other' }}</span></td>
                        <td>{{ $question->user->name }}</td>
                        <td>
                            @if ($question->answers_count > 0)
                                <span class="pulse-tag green">Answered</span>
                            @else
                                <span class="pulse-tag orange">Not answered</span>
                            @endif
                        </td>
                        <td style="text-align:right;">
                            <div class="pulse-actions" style="justify-content:flex-end;">
                                <a class="pulse-icon-btn" href="{{ route('questions.show', $question) }}" title="View"><i class="fas fa-eye"></i></a>
                                @if (($user->role ?? null) === 'admin')
                                    <form method="POST" action="{{ route('questions.destroy', $question) }}" onsubmit="return confirm('Delete this question?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="pulse-icon-btn" style="color:#d33;" type="submit" title="Delete"><i class="fas fa-trash"></i></button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="pulse-muted">No questions yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
