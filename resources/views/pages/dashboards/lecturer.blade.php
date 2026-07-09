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
                    <a href="#"><i class="fas fa-square-check"></i> Quizzes</a>
                    <a href="#"><i class="fas fa-users"></i> Students</a>
                    <a href="#"><i class="fas fa-calendar-days"></i> Schedule</a>
                    <a href="#"><i class="fas fa-message"></i> Messages</a>
                    <a href="#"><i class="fas fa-chart-line"></i> Analytics</a>
                    <a href="#"><i class="fas fa-folder-open"></i> Resources</a>
                    <a href="#"><i class="fas fa-gear"></i> Settings</a>
                </nav>

                <div class="pulse-user">
                    <span class="pulse-avatar">AC</span>
                    <span><strong>Dr. Alan Carter</strong><span>Computer Science</span></span>
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
                        <span class="pulse-stat-icon"><i class="fas fa-book-open"></i></span>
                        <span><small>Total Lectures</small><b>128</b><span class="pulse-trend">+12% this month</span></span>
                    </article>
                    <article class="pulse-card pulse-stat">
                        <span class="pulse-stat-icon purple"><i class="fas fa-users"></i></span>
                        <span><small>Students</small><b>842</b><span class="pulse-trend">+8% this month</span></span>
                    </article>
                    <article class="pulse-card pulse-stat">
                        <span class="pulse-stat-icon green"><i class="fas fa-clipboard-check"></i></span>
                        <span><small>Quizzes</small><b>24</b><span class="pulse-trend">+15% this month</span></span>
                    </article>
                    <article class="pulse-card pulse-stat">
                        <span class="pulse-stat-icon orange"><i class="fas fa-calendar"></i></span>
                        <span><small>Upcoming Classes</small><b>6</b><span class="pulse-trend">Today</span></span>
                    </article>
                </section>

                <section class="pulse-grid pulse-two" style="margin-top:18px;">
                    <article class="pulse-card pulse-pad">
                        <div class="pulse-section-head">
                            <h2>Today's Schedule</h2>
                            <a href="#">View all</a>
                        </div>
                        <div class="pulse-list">
                            <div class="pulse-row"><span class="pulse-time">09:00<br>AM</span><span><strong>Data Structures</strong><p>Lecture 12 - Room 204</p></span><span class="pulse-dot"></span></div>
                            <div class="pulse-row"><span class="pulse-time">11:00<br>AM</span><span><strong>Algorithms</strong><p>Lecture 8 - Room 301</p></span><span class="pulse-dot" style="background:var(--pulse-purple);"></span></div>
                            <div class="pulse-row"><span class="pulse-time">01:30<br>PM</span><span><strong>Database Systems</strong><p>Lecture 10 - Room 204</p></span><span class="pulse-dot" style="background:var(--pulse-green);"></span></div>
                            <div class="pulse-row"><span class="pulse-time">03:30<br>PM</span><span><strong>Office Hours</strong><p>Room 204</p></span><span class="pulse-dot" style="background:var(--pulse-orange);"></span></div>
                        </div>
                    </article>

                    <article class="pulse-card pulse-pad">
                        <div class="pulse-section-head">
                            <h2>Lecture Overview</h2>
                            <span class="pulse-tag gray">This month</span>
                        </div>
                        <div class="pulse-chart">
                            <svg viewBox="0 0 520 230" role="img" aria-label="Lecture attendance chart">
                                <defs>
                                    <linearGradient id="lectureFill" x1="0" x2="0" y1="0" y2="1">
                                        <stop offset="0%" stop-color="#315cff" stop-opacity=".18" />
                                        <stop offset="100%" stop-color="#315cff" stop-opacity="0" />
                                    </linearGradient>
                                </defs>
                                <path d="M18 190 C70 145 98 132 138 158 C178 184 210 146 246 106 C286 62 326 78 362 114 C402 154 430 162 464 110 C488 72 506 76 514 90 L514 220 L18 220 Z" fill="url(#lectureFill)" />
                                <path d="M18 190 C70 145 98 132 138 158 C178 184 210 146 246 106 C286 62 326 78 362 114 C402 154 430 162 464 110 C488 72 506 76 514 90" fill="none" stroke="#315cff" stroke-width="5" stroke-linecap="round" />
                                <g fill="#315cff"><circle cx="362" cy="114" r="6" /><circle cx="514" cy="90" r="6" /></g>
                            </svg>
                        </div>
                    </article>
                </section>

                <section class="pulse-grid pulse-two" style="margin-top:18px;">
                    <article class="pulse-card pulse-pad">
                        <div class="pulse-section-head">
                            <h2>Recent Lectures</h2>
                            <a href="#">View all</a>
                        </div>
                        <div class="pulse-list">
                            <div class="pulse-row"><span class="pulse-soft-icon"><i class="fas fa-book"></i></span><span><strong>Data Structures - Linked Lists</strong><p>Lecture 11 - May 13, 2024</p></span><span class="pulse-tag green">Published</span></div>
                            <div class="pulse-row"><span class="pulse-soft-icon"><i class="fas fa-diagram-project"></i></span><span><strong>Algorithms - Sorting Techniques</strong><p>Lecture 7 - May 10, 2024</p></span><span class="pulse-tag green">Published</span></div>
                            <div class="pulse-row"><span class="pulse-soft-icon"><i class="fas fa-database"></i></span><span><strong>Database Systems - Normalization</strong><p>Lecture 9 - May 8, 2024</p></span><span class="pulse-tag green">Published</span></div>
                        </div>
                    </article>

                    <article class="pulse-card pulse-pad">
                        <div class="pulse-section-head">
                            <h2>Recent Quizzes</h2>
                            <a href="#">View all</a>
                        </div>
                        <div class="pulse-list">
                            <div class="pulse-row"><span class="pulse-soft-icon"><i class="fas fa-clipboard-question"></i></span><span><strong>Data Structures Quiz 1</strong><p>20 questions - May 14, 2024</p></span><span class="pulse-tag green">75%</span></div>
                            <div class="pulse-row"><span class="pulse-soft-icon"><i class="fas fa-code"></i></span><span><strong>Algorithms Quiz 2</strong><p>25 questions - May 9, 2024</p></span><span class="pulse-tag green">68%</span></div>
                            <div class="pulse-row"><span class="pulse-soft-icon"><i class="fas fa-server"></i></span><span><strong>Database Systems Quiz 1</strong><p>15 questions - May 3, 2024</p></span><span class="pulse-tag green">82%</span></div>
                        </div>
                    </article>
                </section>

                <section class="pulse-card pulse-pad" style="margin-top:18px;">
                    <div class="pulse-section-head">
                        <h2>Upcoming Quizzes</h2>
                        <a href="#">View all</a>
                    </div>
                    <div class="pulse-grid pulse-three">
                        <div class="pulse-resource"><span class="pulse-stat-icon purple"><i class="fas fa-clipboard-question"></i></span><span><strong>Algorithms Quiz 3</strong><span class="pulse-muted">20 questions - 6 days left</span></span></div>
                        <div class="pulse-resource"><span class="pulse-stat-icon cyan"><i class="fas fa-file-lines"></i></span><span><strong>Data Structures Quiz 2</strong><span class="pulse-muted">20 questions - 9 days left</span></span></div>
                        <div class="pulse-resource"><span class="pulse-stat-icon green"><i class="fas fa-database"></i></span><span><strong>Database Systems Quiz 1</strong><span class="pulse-muted">20 questions - 12 days left</span></span></div>
                    </div>
                </section>
            </main>
        </div>
    </div>
</x-layouts.academic-pulse>
