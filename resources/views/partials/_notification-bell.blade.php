@php
    $unreadNotifications = $unreadNotifications ?? collect();
    $unreadNotificationsCount = $unreadNotificationsCount ?? 0;
@endphp
<details class="notif-bell">
    <summary class="notif-bell-toggle" title="Notifications">
        <i class="fas fa-bell"></i>
        @if ($unreadNotificationsCount > 0)
            <span class="notif-badge">{{ $unreadNotificationsCount > 9 ? '9+' : $unreadNotificationsCount }}</span>
        @endif
    </summary>
    <div class="notif-dropdown">
        <div class="notif-dropdown-head">
            <strong>Notifications</strong>
            @if ($unreadNotificationsCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit">Mark all read</button>
                </form>
            @endif
        </div>
        <div class="notif-list">
            @forelse ($unreadNotifications as $notification)
                @if ($notification->type === \App\Notifications\TopicSuggested::class)
                    <div class="notif-item">
                        <strong>Suggested topic:</strong> <em>{{ $notification->data['topic_title'] ?? 'A topic' }}</em>
                        <p>Students with similar recent activity are engaging with this{{ ! empty($notification->data['topic_description']) ? ' &mdash; '.$notification->data['topic_description'] : '' }}.</p>
                        <div class="notif-actions">
                            <form method="POST" action="{{ route('topics.subscribe', $notification->data['topic_id']) }}">
                                @csrf
                                <button type="submit" class="notif-action-btn primary">Subscribe</button>
                            </form>
                            <form method="POST" action="{{ route('topics.ignore-suggestion', $notification->data['topic_id']) }}">
                                @csrf
                                <button type="submit" class="notif-action-btn">Ignore</button>
                            </form>
                        </div>
                        <span>{{ $notification->created_at->diffForHumans() }}</span>
                    </div>
                @else
                    <a href="{{ route('notifications.open', $notification->id) }}" class="notif-item">
                        @if ($notification->type === \App\Notifications\QuizScheduled::class)
                            <strong>New quiz scheduled:</strong> <em>{{ $notification->data['quiz_title'] ?? 'A quiz' }}</em>
                            <p>{{ $notification->data['subject'] ?? '' }} &middot; {{ $notification->data['duration_minutes'] ?? '?' }} min
                                @if (! empty($notification->data['scheduled_at']))
                                    &middot; starts {{ \Illuminate\Support\Carbon::parse($notification->data['scheduled_at'])->diffForHumans() }}
                                @endif
                            </p>
                        @else
                            <strong>{{ $notification->data['answerer_name'] ?? 'Someone' }}</strong> replied to
                            <em>{{ $notification->data['question_title'] ?? 'your question' }}</em>
                            <p>{{ $notification->data['excerpt'] ?? '' }}</p>
                        @endif
                        <span>{{ $notification->created_at->diffForHumans() }}</span>
                    </a>
                @endif
            @empty
                <p class="notif-empty">No new notifications.</p>
            @endforelse
        </div>
    </div>
</details>

<style>
    .notif-bell { position: relative; }

    .notif-bell-toggle {
        position: relative;
        display: grid;
        place-items: center;
        width: 42px;
        height: 42px;
        border-radius: 14px;
        background: rgba(255, 255, 255, .96);
        border: 1px solid rgba(255, 255, 255, .8);
        color: #2563eb;
        cursor: pointer;
        list-style: none;
    }

    .notif-bell-toggle::-webkit-details-marker { display: none; }

    .notif-badge {
        position: absolute;
        top: -4px;
        right: -4px;
        min-width: 17px;
        height: 17px;
        padding: 0 4px;
        display: grid;
        place-items: center;
        border-radius: 999px;
        background: #ef4444;
        color: #fff;
        font-size: .6rem;
        font-weight: 900;
        line-height: 1;
    }

    .notif-dropdown {
        position: absolute;
        top: calc(100% + 10px);
        right: 0;
        z-index: 40;
        width: 320px;
        max-width: 90vw;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 20px 46px rgba(15, 31, 61, .18);
        overflow: hidden;
    }

    .notif-dropdown-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 12px 14px;
        border-bottom: 1px solid rgba(15, 31, 61, .08);
        color: #14213d;
        font-size: .84rem;
    }

    .notif-dropdown-head form button {
        border: 0;
        background: transparent;
        color: #2563eb;
        font-size: .74rem;
        font-weight: 800;
        cursor: pointer;
    }

    .notif-list { max-height: 360px; overflow-y: auto; }

    .notif-item {
        display: block;
        padding: 12px 14px;
        border-bottom: 1px solid rgba(15, 31, 61, .06);
        color: #2a3a5c;
        font-size: .82rem;
    }

    .notif-item:hover { background: #f7f9fc; }
    .notif-item strong { color: #14213d; }
    .notif-item em { font-style: normal; color: #2563eb; font-weight: 700; }
    .notif-item p { margin: 4px 0 0; color: #7182a8; font-weight: 600; }
    .notif-item span { display: block; margin-top: 6px; color: #a2acc2; font-size: .7rem; font-weight: 700; }

    .notif-actions { display: flex; gap: 8px; margin-top: 8px; }

    .notif-action-btn {
        flex: 1;
        border: 1px solid rgba(15, 31, 61, .12);
        background: #fff;
        color: #2a3a5c;
        border-radius: 8px;
        padding: 6px 0;
        font-size: .76rem;
        font-weight: 800;
        cursor: pointer;
    }

    .notif-action-btn.primary { background: #2563eb; border-color: #2563eb; color: #fff; }

    .notif-empty { padding: 20px 14px; text-align: center; color: #7182a8; font-size: .82rem; font-weight: 650; }
</style>
