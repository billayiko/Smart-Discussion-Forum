<x-layouts.academic-pulse title="Lecturer Dashboard">
    <div class="ap-shell">
        <header class="ap-header">
            <a class="ap-brand" href="{{ route('home') }}">
                <span class="ap-logo"><i class="fas fa-chalkboard-teacher"></i></span>
                <span><h1>Academic<span>Pulse</span></h1><small>Lecturer Dashboard</small></span>
            </a>
            <div class="ap-nav">
                <span class="user-badge"><span class="avatar">RK</span> Dr. Robert Kiguli <span class="pill" style="color:var(--lecturer);">Lecturer</span></span>
                <a class="ap-btn danger" href="{{ route('login') }}"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </header>

        <div class="dashboard">
            <aside class="sidebar">
                <section class="side-block">
                    <h3 class="side-title"><i class="fas fa-chart-simple"></i> Class Stats</h3>
                    <div class="stat-row"><span>Students</span><strong>42</strong></div>
                    <div class="stat-row"><span>Active today</span><strong>31</strong></div>
                    <div class="stat-row"><span>Participation</span><strong>76%</strong></div>
                    <div class="stat-row"><span>Quiz average</span><strong>68%</strong></div>
                </section>
                <section class="side-block">
                    <h3 class="side-title"><i class="fas fa-gavel"></i> Moderation</h3>
                    <div class="stat-row"><span>Warnings</span><strong>2</strong></div>
                    <div class="stat-row"><span>Blacklisted</span><strong>1</strong></div>
                </section>
            </aside>

            <main class="dash-content">
                <div class="dash-title">
                    <div>
                        <h2><i class="fas fa-chalkboard-teacher" style="color:var(--gold);"></i> Lecturer Dashboard</h2>
                        <p>Manage quizzes, participation, and class activity.</p>
                    </div>
                </div>

                <div class="grid">
                    <section class="ap-card panel">
                        <h3><i class="fas fa-pencil-alt"></i> Configure Quiz</h3>
                        <div class="field"><label>Quiz Title</label><input style="padding-left:12px;" value="Laravel Middleware"></div>
                        <div class="field"><label>Target Group</label><select style="padding-left:12px;"><option>BSc Computer Science - Year 2</option><option>BSc Software Engineering - Year 3</option></select></div>
                        <div class="field"><label>Date & Time</label><input style="padding-left:12px;" type="datetime-local" value="2026-06-25T14:30"></div>
                        <div class="field"><label>Duration</label><input style="padding-left:12px;" type="number" value="15"></div>
                        <button class="ap-btn primary"><i class="fas fa-bullhorn"></i> Publish Quiz</button>
                    </section>

                    <section class="ap-card panel">
                        <h3><i class="fas fa-graduation-cap"></i> Participation Grading</h3>
                        <div class="metric-grid">
                            <div class="metric"><strong>3</strong><span>Min posts/week</span></div>
                            <div class="metric"><strong>2</strong><span>Min replies/week</span></div>
                            <div class="metric"><strong>20%</strong><span>Grade weight</span></div>
                            <div class="metric"><strong>54%</strong><span>At risk</span></div>
                        </div>
                        <button class="ap-btn light" style="margin-top:12px;"><i class="fas fa-star"></i> Award Marks</button>
                    </section>
                </div>

                <section class="ap-card panel">
                    <h3><i class="fas fa-users"></i> Student Participation</h3>
                    <div class="list">
                        <div class="list-item"><span>Sarah Johnson</span><strong>Posts: 23 · Score: 92%</strong></div>
                        <div class="list-item"><span>Michael Okello</span><strong>Posts: 18 · Score: 78%</strong></div>
                        <div class="list-item"><span>Grace Auma</span><strong>Posts: 15 · Score: 65%</strong></div>
                        <div class="list-item"><span>David Mwesigwa</span><strong>Posts: 8 · Score: 42%</strong></div>
                    </div>
                </section>
            </main>
        </div>
    </div>
</x-layouts.academic-pulse>
