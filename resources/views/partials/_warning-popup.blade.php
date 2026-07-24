@php
    $warningPopup = $warningPopup ?? null;
@endphp
@if ($warningPopup)
    <div class="warning-popup-overlay">
        <div class="warning-popup-modal">
            <span class="warning-popup-icon"><i class="fas fa-triangle-exclamation"></i></span>
            <h2>{{ $warningPopup['title'] ?? 'Inactivity warning' }}</h2>
            <p>{{ $warningPopup['message'] }}</p>
            <form method="POST" action="{{ route('notifications.acknowledge', $warningPopup['id']) }}">
                @csrf
                <button type="submit">I understand</button>
            </form>
        </div>
    </div>

    <style>
        .warning-popup-overlay {
            position: fixed;
            inset: 0;
            z-index: 999;
            display: grid;
            place-items: center;
            background: rgba(10, 22, 40, .55);
            padding: 20px;
        }

        .warning-popup-modal {
            width: min(420px, 100%);
            padding: 28px;
            border-radius: 16px;
            background: var(--bg, #f7f7fb);
            color: var(--text, #1e1b2e);
            text-align: center;
            box-shadow: 0 24px 60px rgba(0, 0, 0, .35);
        }

        .warning-popup-icon {
            display: inline-grid;
            place-items: center;
            width: 56px;
            height: 56px;
            border-radius: 999px;
            background: #fef3c7;
            color: #b45309;
            font-size: 1.4rem;
            margin-bottom: 14px;
        }

        .warning-popup-modal h2 { font-size: 1.15rem; margin-bottom: 8px; }
        .warning-popup-modal p { color: var(--muted, #4a6a8a); font-weight: 600; line-height: 1.5; }

        .warning-popup-modal button {
            margin-top: 20px;
            width: 100%;
            border: 0;
            border-radius: 10px;
            padding: 12px 0;
            background: #b45309;
            color: #fff;
            font-weight: 800;
            font-size: .9rem;
            cursor: pointer;
        }

        .warning-popup-modal button:hover { background: #92400e; }
    </style>
@endif
