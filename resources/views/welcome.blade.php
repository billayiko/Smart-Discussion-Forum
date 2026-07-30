<x-layouts.academic-pulse title="Welcome">
    <main class="pulse-page pulse-landing">
        <header class="pulse-landing-nav">
            <a class="pulse-logo" href="{{ route('home') }}">
                <i class="fas fa-graduation-cap"></i>
                <span>Academic<span>Pulse Forum</span></span>
            </a>

            <nav class="pulse-nav-links" aria-label="Primary navigation">
                <a href="#features">Features</a>
                <a href="#why-us">Why Us</a>
                <a href="#about">About</a>
                {{-- <a href="#pricing">Pricing</a>
                <a href="#contact">Contact</a> --}}
            </nav>

            <div class="pulse-tools">
                @if (Route::has('login'))
                    <a class="pulse-btn light" href="{{ url('/login') }}">Log in</a>
                @endif
                @if (Route::has('register'))
                    <a class="pulse-btn" href="{{ url('/register') }}">Get Started</a>
                @endif
            </div>
        </header>

        <section class="pulse-hero" id="about">
            <span class="pulse-float one"><i class="fas fa-comment-dots"></i></span>
            <span class="pulse-float two"><i class="fas fa-book-open"></i></span>
            <span class="pulse-float three"><i class="fas fa-user"></i></span>
            <span class="pulse-float four"><i class="fas fa-user-group"></i></span>

            <div>
                <h1>Welcome to<span>Academic Pulse Forum</span></h1>
                <p>Academic Pulse Forum brings your course discussions, quizzes, and messaging into
                    one place — organized by topic, and tailored to your role as a student, lecturer,
                    or admin.</p>
                <div class="pulse-hero-actions">
                    @if (Route::has('register'))
                        <a class="pulse-btn" href="{{ url('/register') }}">Get Started</a>
                    @endif
                    @if (Route::has('login'))
                        <a class="pulse-btn light" href="{{ url('/login') }}">Learn More</a>
                    @endif
                </div>
            </div>
        </section>

        <section class="pulse-feature-grid" id="features" aria-label="Platform features">
            <article class="pulse-card pulse-feature">
                <span class="pulse-stat-icon"><i class="fas fa-layer-group"></i></span>
                <h2>Topic-Scoped Discussions</h2>
                <p>Ask and answer questions inside the exact course topic they belong to — no
                    digging through unrelated threads.</p>
            </article>
            <article class="pulse-card pulse-feature">
                <span class="pulse-stat-icon purple"><i class="fas fa-gauge-high"></i></span>
                <h2>Role-Based Dashboards</h2>
                <p>Students, lecturers, and admins each get a dashboard built around what they
                    actually need to do.</p>
            </article>
            <article class="pulse-card pulse-feature">
                <span class="pulse-stat-icon green"><i class="fas fa-clipboard-question"></i></span>
                <h2>Quizzes & Assessments</h2>
                <p>Create, schedule, and take quizzes tied directly to a course topic, with instant
                    results and tracking.</p>
            </article>
            <article class="pulse-card pulse-feature">
                <span class="pulse-stat-icon cyan"><i class="fas fa-bolt"></i></span>
                <h2>Realtime Messaging</h2>
                <p>Direct and group chat delivered instantly, with automatic fallback so you're
                    never left waiting.</p>
            </article>
        </section>

        <section class="pulse-proof" id="why-us">
            <p>Why students and lecturers choose Academic Pulse Forum</p>
            <div class="pulse-card pulse-proof-card">
                <div>
                    <span class="pulse-stat-icon"><i class="fas fa-compass"></i></span>
                    <strong>One Place</strong>
                    <span>Lectures, discussions, quizzes, and messaging under a single login</span>
                </div>
                <div>
                    <span class="pulse-stat-icon purple"><i class="fas fa-bolt"></i></span>
                    <strong>Always in Sync</strong>
                    <span>WebSocket-powered updates, with automatic offline fallback</span>
                </div>
                <div>
                    <span class="pulse-stat-icon green"><i class="fas fa-shield-halved"></i></span>
                    <strong>Actively Moderated</strong>
                    <span>Automatic inactivity checks and admin-reviewed reports keep things civil</span>
                </div>
                <div>
                    <span class="pulse-stat-icon cyan"><i class="fas fa-desktop"></i></span>
                    <strong>Web & Desktop</strong>
                    <span>Use it in the browser or the native desktop client — same account, same data</span>
                </div>
            </div>
        </section>
    </main>
</x-layouts.academic-pulse>
