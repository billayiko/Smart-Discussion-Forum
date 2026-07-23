<style>
    .forum-shell {
        --forum-navy-dark: #0a1628;
        --forum-navy-mid: #0f2b4b;
        --forum-gold: #c9a84c;
        --forum-gold-light: #f0d060;
        --forum-bg: #eef2f7;
        min-height: 100vh;
        background: var(--forum-bg);
        color: #14213d;
        font-family: "Inter", "Segoe UI", -apple-system, BlinkMacSystemFont, system-ui, sans-serif;
    }

    .forum-topbar {
        position: sticky;
        top: 0;
        z-index: 30;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 14px 24px;
        background: linear-gradient(135deg, var(--forum-navy-dark), var(--forum-navy-mid));
        border-bottom: 2px solid var(--forum-gold);
    }

    .forum-logo {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        color: #fff;
        font-weight: 900;
        font-size: 1.05rem;
    }

    .forum-logo i {
        width: 38px;
        height: 38px;
        display: grid;
        place-items: center;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--forum-gold), var(--forum-gold-light));
        color: var(--forum-navy-dark);
        font-size: 1.1rem;
    }

    .forum-logo span span { display: inline; color: var(--forum-gold); }
    .forum-logo small { display: block; color: #8ea5c1; font-size: .6rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; }

    .forum-topbar-right { display: flex; align-items: center; gap: 14px; }

    .forum-online-pill {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 6px 12px;
        border-radius: 999px;
        background: rgba(34, 197, 94, .14);
        color: #4ade80;
        font-size: .74rem;
        font-weight: 800;
    }

    .forum-dot { width: 8px; height: 8px; border-radius: 50%; background: #64748b; flex: 0 0 auto; }
    .forum-dot.online { background: #22c55e; box-shadow: 0 0 0 3px rgba(34,197,94,.18); }
    .forum-dot.offline { background: #64748b; }

    .forum-user { display: inline-flex; align-items: center; gap: 10px; color: #fff; }
    .forum-user strong { font-size: .86rem; font-weight: 800; }

    .forum-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        flex: 0 0 auto;
        background: var(--forum-navy-mid);
        color: #fff;
        font-weight: 900;
        font-size: .74rem;
    }

    .forum-avatar.gold { background: linear-gradient(135deg, var(--forum-gold), var(--forum-gold-light)); color: var(--forum-navy-dark); }

    .forum-role-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 10px;
        border-radius: 999px;
        background: var(--student, #3b82f6);
        color: #fff;
        font-size: .64rem;
        font-weight: 900;
        letter-spacing: .4px;
    }

    .forum-role-badge[data-role="lecturer"] { background: var(--lecturer, #8b5cf6); }
    .forum-role-badge[data-role="admin"] { background: var(--admin, #ef4444); }
    .forum-role-badge[data-role="member"] { background: #64748b; }
    .forum-role-badge.small { padding: 2px 8px; font-size: .6rem; }

    .forum-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-height: 36px;
        padding: 0 14px;
        border: 0;
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 800;
        cursor: pointer;
        white-space: nowrap;
    }

    .forum-btn.danger { background: rgba(239,68,68,.14); color: #fca5a5; }
    .forum-btn.gold { background: linear-gradient(135deg, var(--forum-gold), var(--forum-gold-light)); color: var(--forum-navy-dark); }
    .forum-btn.light { background: #fff; color: #14213d; border: 1px solid rgba(15,31,61,.12); }

    .forum-app { display: grid; grid-template-columns: 300px minmax(0, 1fr); align-items: start; }

    .forum-sidebar {
        position: sticky;
        top: 65px;
        height: calc(100vh - 65px);
        overflow-y: auto;
        padding: 18px;
        background: linear-gradient(180deg, var(--forum-navy-dark), var(--forum-navy-mid));
        color: #c9d6ea;
    }

    .forum-sidebar-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 10px; }
    .forum-sidebar-title { font-size: .68rem; font-weight: 900; letter-spacing: 1px; color: #8ea5c1; }
    .forum-sidebar-title i { color: var(--forum-gold); margin-right: 4px; }

    .forum-count-badge {
        padding: 3px 9px;
        border-radius: 999px;
        background: rgba(255,255,255,.08);
        color: var(--forum-gold-light);
        font-size: .62rem;
        font-weight: 900;
    }

    .forum-all-link { display: inline-flex; align-items: center; gap: 6px; color: #8ea5c1; font-size: .74rem; font-weight: 700; margin-bottom: 14px; }
    .forum-all-link:hover { color: var(--forum-gold-light); }

    .forum-groups { display: grid; gap: 6px; }

    .forum-group {
        display: block;
        border-radius: 14px;
        padding: 10px 12px;
        background: rgba(255,255,255,.05);
        color: #c9d6ea;
    }

    .forum-group-head, .forum-group.collapsed { display: flex; align-items: center; justify-content: space-between; gap: 8px; font-size: .8rem; font-weight: 800; }
    .forum-group.expanded { background: rgba(201,168,76,.08); }
    .forum-group-meta { display: inline-flex; align-items: center; gap: 5px; font-size: .64rem; color: #8ea5c1; font-weight: 700; }

    .forum-group-threads { display: grid; gap: 4px; margin-top: 8px; padding-left: 8px; }

    .forum-thread-link {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 10px;
        border-radius: 10px;
        color: #b9c8e0;
        font-size: .76rem;
        font-weight: 700;
    }

    .forum-thread-link span:nth-child(2) { flex: 1; }
    .forum-thread-link:hover { background: rgba(255,255,255,.06); }
    .forum-thread-link.active { background: rgba(201,168,76,.16); color: var(--forum-gold-light); }

    .forum-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 20px;
        height: 20px;
        padding: 0 6px;
        border-radius: 999px;
        background: rgba(255,255,255,.12);
        font-size: .64rem;
        font-weight: 900;
    }

    .forum-members { display: grid; gap: 8px; }

    .forum-member {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px;
        border-radius: 12px;
        background: rgba(255,255,255,.05);
    }

    .forum-member-info { flex: 1; display: grid; gap: 2px; font-size: .74rem; }
    .forum-member-info strong { color: #fff; }
    .forum-member-info span { color: #8ea5c1; font-size: .66rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; }

    .forum-muted { color: #7182a8; font-size: .8rem; font-weight: 650; }

    .forum-main { min-width: 0; padding: 24px 28px 60px; }

    .forum-alert { margin-bottom: 16px; padding: 12px 14px; border-radius: 12px; font-weight: 700; font-size: .84rem; }
    .forum-alert.success { background: rgba(34,197,94,.1); color: #15803d; }
    .forum-alert.error { background: rgba(239,68,68,.1); color: #b91c1c; }
    .forum-alert.error ul { margin: 0; padding-left: 18px; }

    .forum-search-bar { display: flex; gap: 10px; margin-bottom: 20px; }

    .forum-search {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 10px;
        height: 44px;
        padding: 0 16px;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 8px 20px rgba(15,31,61,.05);
        color: #7182a8;
    }

    .forum-search input { flex: 1; border: 0; outline: 0; background: transparent; font-weight: 700; color: #14213d; }

    #forum-search-scope {
        height: 44px;
        padding: 0 14px;
        border-radius: 14px;
        border: 0;
        background: #fff;
        box-shadow: 0 8px 20px rgba(15,31,61,.05);
        font-weight: 700;
        color: #14213d;
    }

    .forum-heading h1 { margin: 4px 0 0; font-size: 1.7rem; font-weight: 950; }
    .forum-heading h1 { color: var(--forum-navy-dark); }

    .forum-breadcrumb { margin: 8px 0 6px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; color: #7182a8; font-size: .78rem; font-weight: 700; }
    .forum-breadcrumb a { color: #7182a8; }
    .forum-breadcrumb a:hover { color: var(--forum-navy-dark); }
    .forum-breadcrumb i { font-size: .6rem; color: #b7c2d8; }

    .forum-meta { display: flex; gap: 18px; color: #7182a8; font-size: .8rem; font-weight: 700; margin-bottom: 18px; }

    .forum-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 18px; }
    .forum-report-form { display: grid; gap: 8px; margin-top: 10px; padding: 12px; border-radius: 12px; background: #fff; box-shadow: 0 8px 20px rgba(15,31,61,.06); width: min(360px, 90vw); }
    .forum-report-form textarea { border: 1px solid rgba(15,31,61,.12); border-radius: 10px; padding: 8px 10px; font: inherit; resize: vertical; }
    .forum-report { position: relative; }
    .forum-report .forum-report-form { position: absolute; z-index: 5; }

    .forum-quiz-banner {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px 18px;
        margin-bottom: 20px;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 10px 26px rgba(15,31,61,.06);
        border-left: 4px solid var(--forum-gold);
    }

    .forum-quiz-icon {
        width: 44px;
        height: 44px;
        display: grid;
        place-items: center;
        border-radius: 12px;
        background: rgba(201,168,76,.14);
        color: var(--forum-gold);
    }

    .forum-quiz-info { flex: 1; display: grid; gap: 3px; }
    .forum-quiz-info span { color: #7182a8; font-size: .78rem; font-weight: 700; }

    .forum-quiz-countdown {
        padding: 8px 14px;
        border-radius: 12px;
        background: #f1f5f9;
        font-weight: 900;
        font-variant-numeric: tabular-nums;
        color: var(--forum-navy-dark);
    }

    .forum-messages { display: grid; gap: 14px; margin-bottom: 20px; }

    .forum-message { display: flex; gap: 12px; align-items: flex-start; padding: 14px; border-radius: 16px; background: #fff; box-shadow: 0 6px 18px rgba(15,31,61,.04); }
    .forum-message .forum-avatar { background: var(--forum-navy-mid); }
    a.forum-message:hover { box-shadow: 0 10px 24px rgba(15,31,61,.09); }

    .forum-message-body { flex: 1; min-width: 0; }
    .forum-message-head { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 4px; }
    .forum-message-body p { margin: 0; color: #2a3a5c; line-height: 1.6; }

    .forum-topic-chip { padding: 2px 8px; border-radius: 999px; background: #eef6ff; color: #2563eb; font-size: .64rem; font-weight: 800; }

    .forum-message-footer { display: flex; align-items: center; gap: 14px; margin-top: 10px; padding-top: 10px; border-top: 1px solid rgba(15,31,61,.06); }

    .forum-like-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: 0;
        background: transparent;
        padding: 4px 8px;
        border-radius: 999px;
        color: #7182a8;
        font-size: .78rem;
        font-weight: 800;
        cursor: pointer;
    }

    .forum-like-btn:hover { background: rgba(239,68,68,.08); color: #ef4444; }
    .forum-like-btn.liked { color: #ef4444; }
    .forum-like-btn.liked i { font-weight: 900; }

    .forum-view-count { display: inline-flex; align-items: center; gap: 6px; color: #7182a8; font-size: .78rem; font-weight: 700; }

    .forum-reply-trigger {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: 0;
        background: transparent;
        padding: 4px 8px;
        border-radius: 999px;
        color: #7182a8;
        font-size: .78rem;
        font-weight: 800;
        cursor: pointer;
    }

    .forum-reply-trigger:hover { background: rgba(37,99,235,.08); color: var(--forum-gold, #2563eb); }

    .forum-icon-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: 0;
        background: transparent;
        padding: 4px 8px;
        border-radius: 999px;
        color: #7182a8;
        font-size: .78rem;
        font-weight: 800;
        cursor: pointer;
        list-style: none;
        margin-left: auto;
    }

    .forum-icon-btn::-webkit-details-marker { display: none; }
    .forum-icon-btn:hover { background: rgba(15,31,61,.06); color: #14213d; }

    .forum-share { position: relative; }
    .forum-share-menu {
        position: absolute;
        bottom: calc(100% + 8px);
        right: 0;
        z-index: 6;
        display: grid;
        gap: 2px;
        width: 190px;
        padding: 8px;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 10px 26px rgba(15,31,61,.14);
    }

    .forum-share-menu a,
    .forum-share-menu button {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 10px;
        border: 0;
        border-radius: 8px;
        background: transparent;
        color: #2a3a5c;
        font-size: .8rem;
        font-weight: 700;
        text-align: left;
        cursor: pointer;
        width: 100%;
    }

    .forum-share-menu a:hover,
    .forum-share-menu button:hover { background: #f1f5f9; }
    .forum-share-menu i { width: 16px; text-align: center; color: #7182a8; }

    .forum-composer { padding: 16px; border-radius: 18px; background: #fff; box-shadow: 0 10px 26px rgba(15,31,61,.06); }
    .forum-composer-topic { width: 100%; border: 1px solid rgba(15,31,61,.1); border-radius: 12px; padding: 10px 14px; margin-bottom: 10px; font: inherit; }
    .forum-composer-row { display: flex; gap: 10px; align-items: flex-end; }
    .forum-composer-row textarea { flex: 1; min-height: 46px; border: 1px solid rgba(15,31,61,.1); border-radius: 12px; padding: 12px 14px; font: inherit; resize: vertical; }
    .forum-composer-footer { display: flex; align-items: center; justify-content: space-between; margin-top: 10px; }

    .forum-exclude { position: relative; }
    .forum-exclude-list { position: absolute; bottom: calc(100% + 8px); left: 0; z-index: 5; display: grid; gap: 6px; width: 240px; padding: 12px; border-radius: 12px; background: #fff; box-shadow: 0 10px 26px rgba(15,31,61,.12); }
    .forum-exclude-list label { display: flex; align-items: center; gap: 8px; font-size: .8rem; font-weight: 650; }

    .forum-empty {
        display: grid;
        justify-items: center;
        gap: 10px;
        padding: 40px 20px;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 6px 18px rgba(15,31,61,.04);
        text-align: center;
    }

    .forum-empty i { font-size: 1.6rem; color: var(--forum-gold); }

    .forum-panel { margin-top: 20px; padding: 16px; border-radius: 18px; background: #fff; box-shadow: 0 6px 18px rgba(15,31,61,.04); }
    .forum-panel-head { display: flex; align-items: center; gap: 8px; font-weight: 900; color: var(--forum-navy-dark); margin-bottom: 8px; }
    .forum-panel-head i { color: var(--forum-gold); }

    .forum-participation-row { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid rgba(15,31,61,.06); }
    .forum-participation-row:last-child { border-bottom: 0; }
    .forum-participation-name { flex: 1; font-weight: 800; color: #14213d; }
    .forum-score-tag { padding: 3px 10px; border-radius: 999px; background: rgba(34,197,94,.12); color: #15803d; font-size: .74rem; font-weight: 900; }

    .forum-activity-row { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid rgba(15,31,61,.06); }
    .forum-activity-row:last-child { border-bottom: 0; }
    .forum-activity-text { flex: 1; color: #2a3a5c; font-weight: 650; font-size: .86rem; }
    .forum-activity-icon { display: grid; place-items: center; width: 30px; height: 30px; border-radius: 50%; background: rgba(201,168,76,.14); color: var(--forum-gold); flex: 0 0 auto; font-size: .78rem; }

    [data-forum-hidden] { display: none !important; }

    @media print {
        .forum-topbar, .forum-sidebar, .forum-search-bar, .forum-actions, .forum-composer, .forum-message-footer { display: none !important; }
        .forum-app { display: block; }
        .forum-main { padding: 0; }
    }

    @media (max-width: 900px) {
        .forum-app { grid-template-columns: 1fr; }
        .forum-sidebar { position: static; height: auto; }
    }
</style>

<script>
    (function () {
        const searchInput = document.getElementById('forum-search-input');
        const searchScope = document.getElementById('forum-search-scope');
        const searchBtn = document.getElementById('forum-search-btn');

        function runSearch() {
            const term = (searchInput.value || '').trim().toLowerCase();
            const scope = searchScope.value;

            document.querySelectorAll('[data-forum-search-text]').forEach((el) => {
                const inScope = scope === 'all' || el.closest(`[data-forum-search-scope="${scope}"]`);
                const matches = !term || el.getAttribute('data-forum-search-text').includes(term);
                el.toggleAttribute('data-forum-hidden', !(inScope && matches));
            });
        }

        searchInput?.addEventListener('input', runSearch);
        searchScope?.addEventListener('change', runSearch);
        searchBtn?.addEventListener('click', runSearch);

        const exportBtn = document.getElementById('forum-export-btn');
        exportBtn?.addEventListener('click', () => window.print());

        document.addEventListener('click', async (event) => {
            const copyBtn = event.target.closest('.forum-copy-link-btn');
            if (!copyBtn) return;

            const url = copyBtn.getAttribute('data-share-url');
            const original = copyBtn.innerHTML;
            try {
                await navigator.clipboard.writeText(url);
                copyBtn.innerHTML = '<i class="fas fa-check"></i> Copied!';
            } catch (e) {
                window.prompt('Copy this link:', url);
            }
            setTimeout(() => { copyBtn.innerHTML = original; }, 1500);

            copyBtn.closest('.forum-share')?.removeAttribute('open');
        });

        document.addEventListener('click', (event) => {
            if (!event.target.closest('.forum-reply-trigger')) return;

            const replyInput = document.getElementById('forum-reply-input');
            replyInput?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            replyInput?.focus();
        });

        document.addEventListener('click', async (event) => {
            const likeBtn = event.target.closest('.forum-like-btn');
            if (!likeBtn) return;

            const url = likeBtn.getAttribute('data-like-url');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            likeBtn.disabled = true;
            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                });

                if (res.ok) {
                    const data = await res.json();
                    likeBtn.classList.toggle('liked', data.liked);
                    const countEl = likeBtn.querySelector('.forum-like-count');
                    if (countEl) countEl.textContent = data.count;
                }
            } catch (e) {
                // leave the button's state unchanged on network failure
            } finally {
                likeBtn.disabled = false;
            }
        });

        const syncBtn = document.getElementById('forum-sync-btn');
        syncBtn?.addEventListener('click', async () => {
            const original = syncBtn.innerHTML;
            syncBtn.innerHTML = '<i class="fas fa-arrows-rotate fa-spin"></i> Syncing...';
            try {
                const res = await fetch(window.location.href, { headers: { 'X-Sync': '1' } });
                const html = await res.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');

                ['forum-messages', 'forum-meta', 'forum-quiz-banner'].forEach((id) => {
                    const fresh = doc.getElementById(id);
                    const current = document.getElementById(id);
                    if (fresh && current) {
                        current.replaceWith(fresh);
                    } else if (!fresh && current) {
                        current.remove();
                    }
                });

                tickQuizCountdown();
            } finally {
                syncBtn.innerHTML = original;
            }
        });

        function tickQuizCountdown() {
            const countdownEl = document.getElementById('forum-quiz-countdown');
            const banner = document.getElementById('forum-quiz-banner');
            if (!countdownEl || !banner) return;

            const target = new Date(banner.getAttribute('data-scheduled-at')).getTime();
            const diff = target - Date.now();
            if (diff <= 0) {
                countdownEl.textContent = 'Live now';
                return;
            }
            const totalSeconds = Math.floor(diff / 1000);
            const hours = Math.floor(totalSeconds / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;
            countdownEl.textContent = hours > 0
                ? `${hours}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`
                : `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        }

        tickQuizCountdown();
        setInterval(tickQuizCountdown, 1000);

        const excludeCount = document.getElementById('forum-exclude-count');
        document.querySelectorAll('.forum-exclude-checkbox').forEach((checkbox) => {
            checkbox.addEventListener('change', () => {
                const checked = document.querySelectorAll('.forum-exclude-checkbox:checked').length;
                if (excludeCount) excludeCount.textContent = checked;
            });
        });
    })();
</script>
