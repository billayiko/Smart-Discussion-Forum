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
                <a href="#pricing">Pricing</a>
                <a href="#contact">Contact</a>
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
                <p>Your central hub for lectures, discussions, quizzes, and academic collaboration.</p>
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
                <span class="pulse-stat-icon"><i class="fas fa-lightbulb"></i></span>
                <h2>Smart Learning</h2>
                <p>Access lectures and resources anytime, anywhere.</p>
            </article>
            <article class="pulse-card pulse-feature">
                <span class="pulse-stat-icon purple"><i class="fas fa-calendar-check"></i></span>
                <h2>Engage & Discuss</h2>
                <p>Join meaningful discussions and share ideas.</p>
            </article>
            <article class="pulse-card pulse-feature">
                <span class="pulse-stat-icon"><i class="fas fa-shield-halved"></i></span>
                <h2>Take Quizzes</h2>
                <p>Assess your knowledge with interactive quizzes.</p>
            </article>
            <article class="pulse-card pulse-feature">
                <span class="pulse-stat-icon purple"><i class="fas fa-star"></i></span>
                <h2>Track Progress</h2>
                <p>Monitor learning and achievement effortlessly.</p>
            </article>
        </section>

        <section class="pulse-proof" id="why-us">
            <p>Trusted by students and educators worldwide</p>
            <div class="pulse-card pulse-proof-card">
                <div>
                    <span class="pulse-stat-icon"><i class="fas fa-users"></i></span>
                    <strong>1,200+</strong>
                    <span>Students</span>
                </div>
                <div>
                    <span class="pulse-stat-icon"><i class="fas fa-book-open"></i></span>
                    <strong>150+</strong>
                    <span>Lectures</span>
                </div>
                <div>
                    <span class="pulse-stat-icon"><i class="fas fa-comments"></i></span>
                    <strong>320+</strong>
                    <span>Discussions</span>
                </div>
                <div>
                    <span class="pulse-stat-icon"><i class="fas fa-star"></i></span>
                    <strong>98%</strong>
                    <span>Satisfaction</span>
                </div>
            </div>
        </section>
    </main>
</x-layouts.academic-pulse>
