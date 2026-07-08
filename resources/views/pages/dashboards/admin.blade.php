<x-layouts.academic-pulse title="Admin Dashboard">
    <div class="ap-shell">
        <header class="ap-header">
            <a class="ap-brand" href="{{ route('home') }}">
                <span class="ap-logo"><i class="fas fa-crown"></i></span>
                <span><h1>Academic<span>Pulse</span></h1><small>Admin Dashboard</small></span>
            </a>
            <div class="ap-nav">
                <span class="user-badge"><span class="avatar">AM</span> Admin Mukasa <span class="pill" style="color:var(--admin);">Admin</span></span>
                <a class="ap-btn danger" href="{{ route('login') }}"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </header>

        <div class="dashboard">
            <aside class="sidebar">
                <section class="side-block">
                    <h3 class="side-title"><i class="fas fa-chart-simple"></i> Platform Stats</h3>
                    <div class="stat-row"><span>Total Users</span><strong>156</strong></div>
                    <div class="stat-row"><span>Active Today</span><strong>89</strong></div>
                    <div class="stat-row"><span>Groups</span><strong>8</strong></div>
                    <div class="stat-row"><span>Topics</span><strong>47</strong></div>
                </section>
                <section class="side-block">
                    <h3 class="side-title"><i class="fas fa-robot"></i> ML Performance</h3>
                    <div class="stat-row"><span>Accuracy</span><strong>87%</strong></div>
                    <div class="stat-row"><span>Recommendations</span><strong>143</strong></div>
                    <div class="stat-row"><span>Version</span><strong>v2.3</strong></div>
                </section>
            </aside>

            <main class="dash-content">
                <div class="dash-title">
                    <div>
                        <h2><i class="fas fa-crown" style="color:var(--gold);"></i> Administrator Dashboard</h2>
                        <p>Manage groups, moderation, and platform settings.</p>
                    </div>
                    <select style="min-height:38px;border:2px solid var(--line);border-radius:12px;padding:0 12px;"><option>All Groups</option><option>BSc Computer Science</option><option>MSc Data Science</option></select>
                </div>

                <div class="grid">
                    <section class="ap-card panel">
                        <h3><i class="fas fa-robot"></i> ML Performance</h3>
                        <div class="metric-grid">
                            <div class="metric"><strong>87%</strong><span>Classification</span></div>
                            <div class="metric"><strong>4.2s</strong><span>Inference</span></div>
                            <div class="metric"><strong>143</strong><span>Recommendations</span></div>
                            <div class="metric"><strong>92%</strong><span>Engagement</span></div>
                        </div>
                        <button class="ap-btn light" style="margin-top:12px;"><i class="fas fa-sync"></i> Retrain Model</button>
                    </section>

                    <section class="ap-card panel">
                        <h3><i class="fas fa-gavel"></i> Moderation Queue</h3>
                        <div class="list">
                            <div class="list-item"><span>David Mwesigwa · Inactive 12 days</span><button class="ap-btn light">Warn</button></div>
                            <div class="list-item"><span>Faith Nabatanzi · Spam: 3 reports</span><button class="ap-btn light">Resolve</button></div>
                            <div class="list-item"><span>John Otim · Inappropriate content</span><button class="ap-btn danger">Blacklist</button></div>
                        </div>
                    </section>
                </div>

                <section class="ap-card panel">
                    <h3><i class="fas fa-users-cog"></i> Group Management</h3>
                    <div class="grid" style="margin-bottom:0;">
                        <div class="metric"><strong>BSc CS</strong><span>42 members · 12 topics</span></div>
                        <div class="metric"><strong>BSc SE</strong><span>38 members · 9 topics</span></div>
                        <div class="metric"><strong>MSc DS</strong><span>24 members · 7 topics</span></div>
                        <div class="metric"><button class="ap-btn primary"><i class="fas fa-plus"></i> Create Group</button></div>
                    </div>
                </section>
            </main>
        </div>
    </div>
</x-layouts.academic-pulse>
