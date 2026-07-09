<x-layouts.academic-pulse title="Student Dashboard">
    <div class="pulse-page">
        <div class="pulse-app">
            <aside class="pulse-sidebar">
                <a class="pulse-logo" href="{{ route('home') }}">
                    <i class="fas fa-graduation-cap"></i>
                    <span>Academic<span>Pulse Forum</span></span>
                </a>

                <nav class="pulse-menu" aria-label="Student navigation">
                    <a class="active" href="#"><i class="fas fa-house"></i> Dashboard</a>
                    <a href="#"><i class="fas fa-book-open"></i> My Lectures</a>
                    <a href="#"><i class="fas fa-square-check"></i> Quizzes</a>
                    <a href="#"><i class="fas fa-calendar"></i> Schedule</a>
                    <a href="#"><i class="fas fa-message"></i> Messages</a>
                    <a href="#"><i class="fas fa-chart-simple"></i> Grades</a>
                    <a href="#"><i class="fas fa-folder-open"></i> Resources</a>
                    <a href="#"><i class="fas fa-user"></i> Profile</a>
                    <a href="#"><i class="fas fa-gear"></i> Settings</a>
                </nav>

                <div class="pulse-user">
                    <span class="pulse-avatar">AJ</span>
                    <span><strong>Alex Johnson</strong><span>Computer Science</span></span>
                </div>
            </aside>

            <main class="pulse-main">
                <header class="pulse-topbar">
                    <div class="pulse-title">
                        <h1>Good morning, Alex</h1>
                        <p>Here is what is happening with your learning today.</p>
                    </div>
                    <div class="pulse-tools">
                        <label class="pulse-search">
                            <i class="fas fa-magnifying-glass"></i>
                            <input type="search" placeholder="Search anything...">
                        </label>
                        <span class="pulse-icon-btn"><i class="fas fa-bell"></i></span>
                        <span class="pulse-avatar">AJ</span>
                    </div>
                </header>

                <section class="pulse-grid pulse-stats">
                    <article class="pulse-card pulse-stat">
                        <span class="pulse-stat-icon"><i class="fas fa-book-open"></i></span>
                        <span><small>Enrolled Lectures</small><b>6</b><span class="pulse-trend">2 new this week</span></span>
                    </article>
                    <article class="pulse-card pulse-stat">
                        <span class="pulse-stat-icon purple"><i class="fas fa-clipboard-question"></i></span>
                        <span><small>Quizzes</small><b>8</b><span class="pulse-trend">3 upcoming</span></span>
                    </article>
                    <article class="pulse-card pulse-stat">
                        <span class="pulse-stat-icon green"><i class="fas fa-calendar-check"></i></span>
                        <span><small>Upcoming Classes</small><b>4</b><span class="pulse-trend">Today</span></span>
                    </article>
                    <article class="pulse-card pulse-stat">
                        <span class="pulse-stat-icon orange"><i class="fas fa-award"></i></span>
                        <span><small>Average Grade</small><b>A-</b><span class="pulse-trend">88.5%</span></span>
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
                            <div class="pulse-row"><span class="pulse-time">03:30<br>PM</span><span><strong>Computer Networks</strong><p>Lecture 7 - Room 205</p></span><span class="pulse-dot" style="background:var(--pulse-orange);"></span></div>
                        </div>
                    </article>

                    <article class="pulse-card pulse-pad">
                        <div class="pulse-section-head">
                            <h2>Upcoming Quizzes</h2>
                            <a href="#">View all</a>
                        </div>
                        <div class="pulse-list">
                            <div class="pulse-row"><span class="pulse-soft-icon"><i class="fas fa-clipboard-question"></i></span><span><strong>Data Structures Quiz 2</strong><p>May 20, 2024 - 60 mins</p></span><span class="pulse-tag">5 days left</span></div>
                            <div class="pulse-row"><span class="pulse-soft-icon"><i class="fas fa-code"></i></span><span><strong>Algorithms Quiz 2</strong><p>May 24, 2024 - 60 mins</p></span><span class="pulse-tag">9 days left</span></div>
                            <div class="pulse-row"><span class="pulse-soft-icon"><i class="fas fa-database"></i></span><span><strong>Database Systems Quiz 1</strong><p>May 27, 2024 - 45 mins</p></span><span class="pulse-tag">12 days left</span></div>
                            <div class="pulse-row"><span class="pulse-soft-icon"><i class="fas fa-network-wired"></i></span><span><strong>Computer Networks Quiz 1</strong><p>May 31, 2024 - 60 mins</p></span><span class="pulse-tag">16 days left</span></div>
                        </div>
                    </article>
                </section>

                <section class="pulse-grid pulse-three" style="margin-top:18px;">
                    <article class="pulse-card pulse-pad">
                        <div class="pulse-section-head">
                            <h2>My Lectures</h2>
                            <a href="#">View all</a>
                        </div>
                        <div class="pulse-list">
                            <div class="pulse-mini"><strong>Data Structures</strong><span class="pulse-muted">Dr. Alan Carter</span><div class="pulse-progress"><span style="width:70%;"></span></div></div>
                            <div class="pulse-mini"><strong>Algorithms</strong><span class="pulse-muted">Dr. Sarah Johnson</span><div class="pulse-progress"><span style="width:60%;"></span></div></div>
                            <div class="pulse-mini"><strong>Database Systems</strong><span class="pulse-muted">Dr. Michael Lee</span><div class="pulse-progress"><span style="width:85%;"></span></div></div>
                            <div class="pulse-mini"><strong>Computer Networks</strong><span class="pulse-muted">Dr. James Wilson</span><div class="pulse-progress"><span style="width:45%;"></span></div></div>
                        </div>
                    </article>

                    <article class="pulse-card pulse-pad">
                        <div class="pulse-section-head">
                            <h2>Recent Announcements</h2>
                            <a href="#">View all</a>
                        </div>
                        <div class="pulse-list">
                            <div class="pulse-row"><span class="pulse-soft-icon"><i class="fas fa-bullhorn"></i></span><span><strong>Midterm Exam Schedule</strong><p>The midterm exams will be held during May 20 - May 24.</p></span><span class="pulse-muted">2h</span></div>
                            <div class="pulse-row"><span class="pulse-soft-icon"><i class="fas fa-calendar-plus"></i></span><span><strong>Project Deadline Extended</strong><p>The final project submission deadline has been extended.</p></span><span class="pulse-muted">1d</span></div>
                            <div class="pulse-row"><span class="pulse-soft-icon"><i class="fas fa-user-tie"></i></span><span><strong>Guest Lecture on ML</strong><p>Guest lecture on Machine Learning by Dr. Sarah Johnson.</p></span><span class="pulse-muted">2d</span></div>
                        </div>
                    </article>

                    <article class="pulse-card pulse-pad">
                        <div class="pulse-section-head">
                            <h2>Quick Resources</h2>
                        </div>
                        <div class="pulse-list">
                            <a class="pulse-resource" href="#"><span class="pulse-stat-icon green"><i class="fas fa-file-lines"></i></span><span><strong>Lecture Notes</strong><span class="pulse-muted">Access materials</span></span></a>
                            <a class="pulse-resource" href="#"><span class="pulse-stat-icon purple"><i class="fas fa-clipboard-list"></i></span><span><strong>Past Quizzes</strong><span class="pulse-muted">Review quizzes</span></span></a>
                            <a class="pulse-resource" href="#"><span class="pulse-stat-icon orange"><i class="fas fa-comments"></i></span><span><strong>Discussion Forum</strong><span class="pulse-muted">Join discussions</span></span></a>
                            <a class="pulse-resource" href="#"><span class="pulse-stat-icon cyan"><i class="fas fa-calendar-days"></i></span><span><strong>Academic Calendar</strong><span class="pulse-muted">View events</span></span></a>
                        </div>
                    </article>
                </section>
            </main>
        </div>
    </div>
</x-layouts.academic-pulse>
