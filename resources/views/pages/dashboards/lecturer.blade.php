<x-layouts.academic-pulse title="Lecturer Dashboard">
    <div class="pulse-page">
        <div class="pulse-app">
            <aside class="pulse-sidebar">
                <a class="pulse-logo" href="{{ route('home') }}">
                    <i class="fas fa-graduation-cap"></i>
                    <span>Academic<span>Pulse Forum</span></span>
                </a>

                <nav class="pulse-menu" aria-label="Lecturer navigation">
                    <a class="active" href="#"><i class="fas fa-house"></i> Dashboard</a>
                    <a href="#"><i class="fas fa-chalkboard-user"></i> Lectures</a>
                    <a href="#"><i class="fas fa-calendar-days"></i> Schedule</a>
                    <a href="#"><i class="fas fa-message"></i> Messages</a>
                    <a href="#"><i class="fas fa-users"></i> Students</a>
                    <a href="#"><i class="fas fa-chart-line"></i> Analytics</a>
                    <a href="#"><i class="fas fa-gear"></i> Settings</a>
                </nav>

                <div class="pulse-sidebar-footer">
                    <div class="pulse-user">
                        <span class="pulse-avatar">AC</span>
                        <span><strong>Dr. Alan Carter</strong><span>Computer Science</span></span>
                    </div>
                    <div class="pulse-theme-panel" role="group" aria-label="Theme selector">
                        <button type="button" class="pulse-theme-btn active" data-theme="light"><i class="fas fa-sun"></i> Light</button>
                        <button type="button" class="pulse-theme-btn" data-theme="dark"><i class="fas fa-moon"></i> Dark</button>
                    </div>
                </div>
            </aside>

            <main class="pulse-main">
                <header class="pulse-topbar">
                    <div class="pulse-title">
                        <h1>Good morning, Dr. Carter</h1>
                        <p>Here is what is happening with your lectures today.</p>
                    </div>
                    <div class="pulse-tools">
                        <label class="pulse-search">
                            <i class="fas fa-magnifying-glass"></i>
                            <input type="search" placeholder="Search anything...">
                        </label>
                        <span class="pulse-icon-btn"><i class="fas fa-bell"></i></span>
                        <span class="pulse-avatar">AC</span>
                    </div>
                </header>

                <section class="pulse-grid pulse-stats">
                    <article class="pulse-card pulse-stat">
                        <span class="pulse-stat-icon green"><i class="fas fa-clipboard-check"></i></span>
                        <span><small>Quizzes</small><b>24</b><span class="pulse-trend">+15% this month</span></span>
                    </article>
                    <article class="pulse-card pulse-stat">
                        <span class="pulse-stat-icon"><i class="fas fa-book-open"></i></span>
                        <span><small>Total Lectures</small><b>128</b><span class="pulse-trend">+12% this month</span></span>
                    </article>
                    <article class="pulse-card pulse-stat">
                        <span class="pulse-stat-icon purple"><i class="fas fa-users"></i></span>
                        <span><small>Students</small><b>842</b><span class="pulse-trend">+8% this month</span></span>
                    </article>
                    <article class="pulse-card pulse-stat">
                        <span class="pulse-stat-icon orange"><i class="fas fa-calendar"></i></span>
                        <span><small>Upcoming Classes</small><b>6</b><span class="pulse-trend">Today</span></span>
                    </article>
                </section>

                <section class="pulse-grid" style="margin-top:18px; grid-template-columns: repeat(2, minmax(0, 1fr));">
                    <article class="pulse-card pulse-pad">
                        <div class="pulse-section-head">
                            <h2>Quiz Management</h2>
                            <a href="{{ route('quizzes.index') }}">Open</a>
                        </div>
                        <div class="pulse-list">
                            <div class="pulse-row"><span class="pulse-soft-icon"><i class="fas fa-clipboard-question"></i></span><span><strong>24 active quizzes</strong><p>4 published this week</p></span><span class="pulse-tag green">Ready</span></div>
                            <div class="pulse-row"><a href="{{ route('quizzes.create') }}" style="display: contents;"><span class="pulse-soft-icon"><i class="fas fa-plus"></i></span><span><strong>Create new quiz</strong><p>Bulk import from templates</p></span><span class="pulse-tag">New</span></a></div>
                        </div>
                    </article>

                    <article class="pulse-card pulse-pad">
                        <div class="pulse-section-head">
                            <h2>Submissions</h2>
                            <a href="#">Review</a>
                        </div>
                        <div class="pulse-list">
                            <div class="pulse-row"><span class="pulse-soft-icon"><i class="fas fa-file-lines"></i></span><span><strong>186 submissions</strong><p>42 pending review</p></span><span class="pulse-tag orange">Pending</span></div>
                            <div class="pulse-row"><span class="pulse-soft-icon"><i class="fas fa-check-double"></i></span><span><strong>82% reviewed</strong><p>Average turnaround 2h</p></span><span class="pulse-tag green">On track</span></div>
                        </div>
                    </article>

                    <article class="pulse-card pulse-pad">
                        <div class="pulse-section-head">
                            <h2>Weekly Chart Activity</h2>
                            <a href="#">View</a>
                        </div>
                        <div class="pulse-chart">
                            <svg viewBox="0 0 520 230" role="img" aria-label="Weekly activity chart">
                                <path d="M18 176 C70 142 98 126 138 150 C178 176 206 174 246 128 C286 82 326 66 362 96 C402 130 436 136 464 104 C488 78 506 76 514 84" fill="none" stroke="#315cff" stroke-width="5" stroke-linecap="round" />
                                <circle cx="138" cy="150" r="6" fill="#315cff" />
                                <circle cx="246" cy="128" r="6" fill="#315cff" />
                                <circle cx="362" cy="96" r="6" fill="#315cff" />
                                <circle cx="464" cy="104" r="6" fill="#315cff" />
                            </svg>
                        </div>
                    </article>

                    <article class="pulse-card pulse-pad">
                        <div class="pulse-section-head">
                            <h2>Schedule</h2>
                            <a href="#">Plan</a>
                        </div>
                        <div class="pulse-list">
                            <div class="pulse-row"><span class="pulse-time">09:00<br>AM</span><span><strong>Data Structures</strong><p>Room 204 · Live session</p></span><span class="pulse-dot"></span></div>
                            <div class="pulse-row"><span class="pulse-time">11:00<br>AM</span><span><strong>Algorithms Lab</strong><p>Room 301 · Practical</p></span><span class="pulse-dot" style="background:var(--pulse-purple);"></span></div>
                        </div>
                    </article>

                    <article class="pulse-card pulse-pad">
                        <div class="pulse-section-head">
                            <h2>Performance</h2>
                            <a href="#">Details</a>
                        </div>
                        <div class="pulse-list">
                            <div class="pulse-row"><span class="pulse-soft-icon"><i class="fas fa-award"></i></span><span><strong>Avg. score 78%</strong><p>Up 6% from last week</p></span><span class="pulse-tag green">Strong</span></div>
                            <div class="pulse-row"><span class="pulse-soft-icon"><i class="fas fa-chart-line"></i></span><span><strong>Attendance 91%</strong><p>Steady engagement</p></span><span class="pulse-tag">Stable</span></div>
                        </div>
                    </article>

                    <article class="pulse-card pulse-pad">
                        <div class="pulse-section-head">
                            <h2>Discussions</h2>
                            <a href="#">Open</a>
                        </div>
                        <div class="pulse-list">
                            <div class="pulse-row"><span class="pulse-soft-icon"><i class="fas fa-comments"></i></span><span><strong>12 new threads</strong><p>6 unresolved questions</p></span><span class="pulse-tag orange">Active</span></div>
                            <div class="pulse-row"><span class="pulse-soft-icon"><i class="fas fa-user-group"></i></span><span><strong>48 participants</strong><p>Top topic: assignment tips</p></span><span class="pulse-tag">Hot</span></div>
                        </div>
                    </article>
                </section>

                <section class="pulse-card pulse-pad" style="margin-top:18px;">
                    <div class="pulse-section-head">
                        <h2>Upcoming Quizzes</h2>
                        <a href="#">View all</a>
                    </div>
                    <div class="pulse-list">
                        <div class="pulse-row"><span class="pulse-time">10:00<br>AM</span><span><strong>Algorithms Quiz 3</strong><p>20 questions · 6 days left</p></span><span class="pulse-tag orange">Due soon</span></div>
                        <div class="pulse-row"><span class="pulse-time">01:00<br>PM</span><span><strong>Data Structures Quiz 2</strong><p>15 questions · 9 days left</p></span><span class="pulse-tag">Scheduled</span></div>
                        <div class="pulse-row"><span class="pulse-time">03:30<br>PM</span><span><strong>Database Systems Quiz 1</strong><p>12 questions · 12 days left</p></span><span class="pulse-tag green">Planned</span></div>
                    </div>
                </section>
            </main>
        </div>
    </div>
</x-layouts.academic-pulse>
