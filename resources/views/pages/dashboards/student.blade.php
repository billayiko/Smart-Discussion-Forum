<x-layouts.academic-pulse title="Student Dashboard">
    <div class="ap-shell">
        <header class="ap-header">
            <a class="ap-brand" href="{{ route('home') }}">
                <span class="ap-logo"><i class="fas fa-user-graduate"></i></span>
                <span><h1>Academic<span>Pulse</span></h1><small>Student Dashboard</small></span>
            </a>
            <div class="ap-nav">
                <span class="user-badge"><span class="avatar">SJ</span> Sarah Johnson <span class="pill" style="color:var(--student);">Student</span></span>
                <a class="ap-btn danger" href="{{ route('login') }}"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </header>

        <div class="dashboard">
            <aside class="sidebar">
                <section class="side-block">
                    <h3 class="side-title"><i class="fas fa-layer-group"></i> Groups</h3>
                    <div class="list">
                        <span class="pill"><i class="fas fa-laptop-code"></i> BSc Computer Science</span>
                        <span class="pill"><i class="fas fa-code-branch"></i> Software Engineering</span>
                        <span class="pill"><i class="fas fa-robot"></i> ML in Education</span>
                    </div>
                </section>
                <section class="side-block">
                    <h3 class="side-title"><i class="fas fa-users"></i> Online Members</h3>
                    <div class="stat-row"><span>Students</span><strong>31</strong></div>
                    <div class="stat-row"><span>Lecturers</span><strong>4</strong></div>
                    <div class="stat-row"><span>Admins</span><strong>1</strong></div>
                </section>
                <section class="side-block">
                    <h3 class="side-title"><i class="fas fa-chart-simple"></i> My Activity</h3>
                    <div class="stat-row"><span>Posts</span><strong>23</strong></div>
                    <div class="stat-row"><span>Replies</span><strong>18</strong></div>
                    <div class="stat-row"><span>Score</span><strong>92%</strong></div>
                </section>
            </aside>

            <main class="dash-content">
                <div class="dash-title">
                    <div>
                        <h2>Laravel <span style="color:var(--gold);">Best Practices</span></h2>
                        <p><i class="fas fa-clock"></i> Last activity: 2 min ago · 142 views</p>
                    </div>
                    <div class="ap-nav">
                        <button class="ap-btn primary"><i class="fas fa-file-pdf"></i> Export</button>
                        <button class="ap-btn light"><i class="fas fa-sync"></i> Sync</button>
                    </div>
                </div>

                <div class="grid">
                    <section class="ap-card panel">
                        <h3><i class="fas fa-comments"></i> Discussion Thread</h3>
                        <div class="list">
                            <article class="list-item" style="display:block;">
                                <strong>Bernadette Wanyana</strong> <span class="pill">10:42 AM</span>
                                <p style="margin-top:8px;color:var(--muted);line-height:1.6;">Has anyone tried using Laravel Reverb for real-time chat in our forum module?</p>
                            </article>
                            <article class="list-item" style="display:block;">
                                <strong>Gilbert Aloro</strong> <span class="pill">11:05 AM</span>
                                <p style="margin-top:8px;color:var(--muted);line-height:1.6;">Reverb works well with Laravel Echo and presence channels.</p>
                            </article>
                        </div>
                    </section>

                    <section class="ap-card panel">
                        <h3><i class="fas fa-pencil-alt"></i> Compose Message</h3>
                        <form>
                            <div class="field">
                                <textarea placeholder="Write a reply or ask a question..."></textarea>
                            </div>
                            <div class="form-row">
                                <label><input type="checkbox"> Exclude selected members</label>
                                <span class="pill"><i class="fas fa-check-circle" style="color:var(--success);"></i> Online</span>
                            </div>
                            <button class="ap-btn primary" type="button"><i class="fas fa-paper-plane"></i> Send</button>
                        </form>
                    </section>
                </div>

                <section class="ap-card panel">
                    <h3><i class="fas fa-graduation-cap"></i> Upcoming Quiz</h3>
                    <div class="list-item">
                        <span>Laravel Middleware · 15 minutes · Starts 2:30 PM</span>
                        <button class="ap-btn primary"><i class="fas fa-door-open"></i> Enter Quiz</button>
                    </div>
                </section>
            </main>
        </div>
    </div>
</x-layouts.academic-pulse>
