<x-layouts.academic-pulse title="Quiz Management">
    <div class="pulse-page">
        <div class="pulse-app">
            <aside class="pulse-sidebar">
                <a class="pulse-logo" href="{{ route('home') }}">
                    <i class="fas fa-graduation-cap"></i>
                    <span>Academic<span>Pulse Forum</span></span>
                </a>

                <nav class="pulse-menu" aria-label="Admin navigation">
                    <a href="#"><i class="fas fa-house"></i> Dashboard</a>
                    <a href="#"><i class="fas fa-chalkboard-user"></i> Lectures</a>
                    <a class="active" href="#"><i class="fas fa-square-check"></i> Quizzes</a>
                    <a href="#"><i class="fas fa-users"></i> Students</a>
                    <a href="#"><i class="fas fa-calendar-days"></i> Schedule</a>
                    <a href="#"><i class="fas fa-message"></i> Messages</a>
                    <a href="#"><i class="fas fa-chart-line"></i> Analytics</a>
                    <a href="#"><i class="fas fa-folder-open"></i> Resources</a>
                    <a href="#"><i class="fas fa-gear"></i> Settings</a>
                </nav>

                <div class="pulse-user">
                    <span class="pulse-avatar">AC</span>
                    <span><strong>Dr. Alex Carter</strong><span>Computer Science</span></span>
                </div>
            </aside>

            <main class="pulse-main">
                <header class="pulse-topbar">
                    <div class="pulse-title">
                        <h1>Quiz Settings & Management</h1>
                        <p>Create, manage and analyze quizzes for your students.</p>
                    </div>
                    <div class="pulse-tools">
                        <button class="pulse-btn"><i class="fas fa-plus"></i> Create Quiz</button>
                    </div>
                </header>

                <section class="pulse-grid pulse-stats">
                    <article class="pulse-card pulse-stat">
                        <span class="pulse-stat-icon"><i class="fas fa-clipboard-list"></i></span>
                        <span><small>Total Quizzes</small><b>24</b><span class="pulse-trend">+15% this month</span></span>
                    </article>
                    <article class="pulse-card pulse-stat">
                        <span class="pulse-stat-icon green"><i class="fas fa-calendar-check"></i></span>
                        <span><small>Published Quizzes</small><b>18</b><span class="pulse-muted">75% of total</span></span>
                    </article>
                    <article class="pulse-card pulse-stat">
                        <span class="pulse-stat-icon purple"><i class="fas fa-layer-group"></i></span>
                        <span><small>Total Attempts</small><b>1,248</b><span class="pulse-trend">+15% this month</span></span>
                    </article>
                    <article class="pulse-card pulse-stat">
                        <span class="pulse-stat-icon green"><i class="fas fa-bullseye"></i></span>
                        <span><small>Average Score</small><b>72%</b><span class="pulse-trend">+6% this month</span></span>
                    </article>
                </section>

                <section class="pulse-card pulse-pad" style="margin-top:22px;">
                    <div class="pulse-section-head">
                        <div class="pulse-tools" style="gap:6px;">
                            <span class="pulse-tag">All Quizzes</span>
                            <span class="pulse-tag gray">Published</span>
                            <span class="pulse-tag gray">Drafts</span>
                            <span class="pulse-tag gray">Scheduled</span>
                        </div>
                        <div class="pulse-tools">
                            <label class="pulse-search">
                                <i class="fas fa-magnifying-glass"></i>
                                <input type="search" placeholder="Search quizzes...">
                            </label>
                            <button class="pulse-btn light"><i class="fas fa-filter"></i> Filters</button>
                        </div>
                    </div>

                    <div style="overflow:auto;">
                        <table class="pulse-table">
                            <thead>
                                <tr>
                                    <th>Quiz Title</th>
                                    <th>Lecture</th>
                                    <th>Questions</th>
                                    <th>Duration</th>
                                    <th>Attempts</th>
                                    <th>Avg. Score</th>
                                    <th>Status</th>
                                    <th style="text-align:right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Data Structures Quiz 1</strong></td>
                                    <td>Data Structures</td>
                                    <td>20</td>
                                    <td>60 mins</td>
                                    <td>245</td>
                                    <td>75%</td>
                                    <td><span class="pulse-tag green">Published</span></td>
                                    <td><div class="pulse-actions"><span class="pulse-icon-btn" style="width:32px;height:32px;"><i class="fas fa-ellipsis"></i></span></div></td>
                                </tr>
                                <tr>
                                    <td><strong>Algorithms Quiz 2</strong></td>
                                    <td>Algorithms</td>
                                    <td>25</td>
                                    <td>60 mins</td>
                                    <td>210</td>
                                    <td>68%</td>
                                    <td><span class="pulse-tag green">Published</span></td>
                                    <td><div class="pulse-actions"><span class="pulse-icon-btn" style="width:32px;height:32px;"><i class="fas fa-ellipsis"></i></span></div></td>
                                </tr>
                                <tr>
                                    <td><strong>Database Systems Quiz 1</strong></td>
                                    <td>Database Systems</td>
                                    <td>15</td>
                                    <td>45 mins</td>
                                    <td>198</td>
                                    <td>82%</td>
                                    <td><span class="pulse-tag green">Published</span></td>
                                    <td><div class="pulse-actions"><span class="pulse-icon-btn" style="width:32px;height:32px;"><i class="fas fa-ellipsis"></i></span></div></td>
                                </tr>
                                <tr>
                                    <td><strong>Computer Networks Quiz 1</strong></td>
                                    <td>Computer Networks</td>
                                    <td>20</td>
                                    <td>60 mins</td>
                                    <td>195</td>
                                    <td>70%</td>
                                    <td><span class="pulse-tag orange">Scheduled</span></td>
                                    <td><div class="pulse-actions"><span class="pulse-icon-btn" style="width:32px;height:32px;"><i class="fas fa-ellipsis"></i></span></div></td>
                                </tr>
                                <tr>
                                    <td><strong>Algorithms Quiz 3</strong></td>
                                    <td>Algorithms</td>
                                    <td>30</td>
                                    <td>60 mins</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td><span class="pulse-tag gray">Draft</span></td>
                                    <td><div class="pulse-actions"><span class="pulse-icon-btn" style="width:32px;height:32px;"><i class="fas fa-ellipsis"></i></span></div></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="pulse-section-head" style="margin:18px 0 0;">
                        <span class="pulse-muted">Showing 1 to 5 of 24 quizzes</span>
                        <div class="pulse-actions">
                            <span class="pulse-icon-btn" style="width:34px;height:34px;"><i class="fas fa-chevron-left"></i></span>
                            <span class="pulse-tag">1</span>
                            <span class="pulse-tag gray">2</span>
                            <span class="pulse-tag gray">3</span>
                            <span class="pulse-tag gray">5</span>
                            <span class="pulse-icon-btn" style="width:34px;height:34px;"><i class="fas fa-chevron-right"></i></span>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>
</x-layouts.academic-pulse>
