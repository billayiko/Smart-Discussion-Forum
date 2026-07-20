@if ($topics->isEmpty())
    <section class="pulse-card pulse-pad" style="text-align:center; padding:48px 24px;">
        <span class="pulse-stat-icon" style="margin:0 auto 14px;"><i class="fas fa-book"></i></span>
        @if ($user->role === 'student')
            <h2 style="margin-bottom:6px;">No subscribed topics yet</h2>
            <p class="pulse-muted">Subscribe to a topic to start asking questions and joining discussions.</p>
            <div style="margin-top:16px;">
                <a href="{{ route('topics.index') }}" class="pulse-btn" style="text-decoration:none;">Browse Topics</a>
            </div>
        @else
            <h2 style="margin-bottom:6px;">No assigned topics yet</h2>
            <p class="pulse-muted">You haven't been assigned to teach any topics yet. An admin needs to assign you first.</p>
        @endif
    </section>
@else
    <section class="pulse-grid" style="grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));">
        @foreach ($topics as $topic)
            <article class="pulse-card pulse-pad">
                <div class="pulse-section-head">
                    <h2>{{ $topic->title }}</h2>
                </div>
                @if ($topic->description)
                    <p class="pulse-muted" style="margin-bottom:12px;">{{ \Illuminate\Support\Str::limit($topic->description, 90) }}</p>
                @endif
                <div style="display:flex; gap:10px; margin-bottom:16px;">
                    <span class="pulse-tag"><i class="fas fa-users"></i> {{ $topic->subscribers_count }} members</span>
                    <span class="pulse-tag gray"><i class="fas fa-comments"></i> {{ $topic->questions_count }} threads</span>
                </div>
                <a href="{{ route('topics.show', $topic) }}" class="pulse-btn" style="width:100%; text-decoration:none; text-align:center;"><i class="fas fa-arrow-right"></i> View Forum</a>
            </article>
        @endforeach
    </section>
@endif
