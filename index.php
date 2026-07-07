<?php
// Start secure session management
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect checking based on Authentication Flow specifications
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard/dashboard.php");
    exit();
}

if (isset($_SESSION['admin_id'])) {
    header("Location: admin/dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Quizify - Premium online quiz management system for students, teachers, and educational institutions.">
    <title>Quizify – Online Quiz Management System</title>
    
    <style>
        /* Base Reset & Variables */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }

        :root {
            --bg-color: #0f172a;
            --primary-blue: #4f46e5;
            --secondary-purple: #7c3aed;
            --teacher-accent: #dd2476;
            --text-white: #ffffff;
            --text-secondary: #94a3b8;
            --glass-bg: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.08);
            --glass-glow: rgba(124, 58, 237, 0.15);
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-white);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            position: relative;
        }

        /* Ambient Animated Background Background Elements */
        .bg-glow-container {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            top: 0;
            left: 0;
            z-index: 0;
            pointer-events: none;
        }

        .glow-circle {
            position: absolute;
            border-radius: 50%;
            filter: blur(120px);
            opacity: 0.25;
            animation: pulseGlow 12s infinite alternate ease-in-out;
        }

        .glow-1 {
            width: 400px;
            height: 400px;
            background: var(--primary-blue);
            top: -10%, left: 10%;
        }

        .glow-2 {
            width: 500px;
            height: 500px;
            background: var(--secondary-purple);
            bottom: 10%;
            right: -5%;
            animation-delay: -4s;
        }

        /* Floating Particles Simulation via CSS */
        .particles {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
        }

        .particle {
            position: absolute;
            display: block;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            bottom: -50px;
            animation: floatUp 15s infinite linear;
        }

        /* Production Split Screen Wrapper Layout */
        .main-wrapper {
            display: flex;
            flex: 1;
            width: 100%;
            max-width: 1440px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
            padding: 20px;
            gap: 20px;
        }

        /* LEFT PANEL: 60% Width Structural Config */
        .left-panel {
            width: 60%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 30px;
        }

        /* Header Branding UI */
        .branding-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 40px;
        }

        .logo-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-purple));
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 24px;
            font-weight: 800;
            color: var(--text-white);
            box-shadow: 0 0 20px rgba(124, 58, 237, 0.4);
        }

        .branding-text {
            display: flex;
            flex-direction: column;
        }

        .branding-text h2 {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: 2px;
            background: linear-gradient(to right, #fff, #cbd5e1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .branding-text span {
            font-size: 12px;
            color: var(--primary-blue);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Hero Content Styles */
        .hero-section {
            margin-bottom: 40px;
            animation: fadeIn 1s ease-out;
        }

        .hero-title {
            font-size: 2.8rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 15px;
        }

        .typing-text {
            color: transparent;
            background: linear-gradient(to right, #6157ff, #b37eff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            border-right: 3px solid var(--secondary-purple);
            padding-right: 5px;
            white-space: nowrap;
            animation: blink 0.75s step-end infinite;
        }

        .hero-subheading {
            font-size: 1.2rem;
            color: #cbd5e1;
            margin-bottom: 15px;
            font-weight: 500;
        }

        .hero-desc {
            font-size: 0.95rem;
            color: var(--text-secondary);
            line-height: 1.6;
        }

        /* Glassmorphism Feature Grid Layout */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 40px;
        }

        .feature-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            padding: 16px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.05), transparent);
            transition: 0.5s;
        }

        .feature-card:hover::before {
            left: 100%;
        }

        .feature-card:hover {
            transform: translateY(-4px);
            border-color: rgba(124, 58, 237, 0.3);
            box-shadow: 0 10px 20px var(--glass-glow);
            background: rgba(255, 255, 255, 0.05);
        }

        /* Statistics Layout Component */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 40px;
            background: rgba(255, 255, 255, 0.01);
            padding: 15px;
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.03);
        }

        .stat-box {
            text-align: center;
        }

        .stat-number {
            font-size: 1.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, #fff, #94a3b8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-label {
            font-size: 0.75rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 4px;
        }

        /* Motivational Quotes Carousel Config */
        .quotes-container {
            background: linear-gradient(90deg, rgba(79, 70, 229, 0.05), rgba(124, 58, 237, 0.05));
            border-left: 4px solid var(--secondary-purple);
            padding: 15px 20px;
            border-radius: 0 12px 12px 0;
            min-height: 70px;
            display: flex;
            align-items: center;
        }

        .quote-text {
            font-style: italic;
            font-size: 0.95rem;
            color: #e2e8f0;
            transition: opacity 0.5s ease-in-out;
            width: 100%;
        }

        /* RIGHT PANEL: 40% Width Structural Config (Auth Card) */
        .right-panel {
            width: 40%;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .auth-glass-card {
            background: rgba(15, 23, 42, 0.4);
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            width: 100%;
            max-width: 440px;
            padding: 40px 35px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            position: relative;
            transition: border-color 0.4s;
        }

        .auth-glass-card:hover {
            border-color: rgba(79, 70, 229, 0.3);
        }

        .auth-card-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .auth-card-header .logo-circle {
            margin: 0 auto 15px auto;
        }

        .auth-card-header h3 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 5px;
            letter-spacing: 0.5px;
        }

        .auth-card-header p {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        /* Module Portal Actions UI Block */
        .portal-section {
            margin-bottom: 25px;
            padding: 20px;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.04);
            transition: all 0.3s;
        }

        .portal-section.student-zone:hover {
            border-color: rgba(79, 70, 229, 0.2);
            background: rgba(79, 70, 229, 0.02);
        }

        .portal-section.teacher-zone:hover {
            border-color: rgba(221, 36, 118, 0.2);
            background: rgba(221, 36, 118, 0.02);
        }

        .portal-title {
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .student-zone .portal-title { color: #818cf8; }
        .teacher-zone .portal-title { color: #f43f5e; }

        .action-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        /* Production Button Styling Layout */
        .auth-btn {
            display: block;
            width: 100%;
            padding: 12px 20px;
            border-radius: 10px;
            text-align: center;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            border: none;
        }

        /* Student Action Button Config */
        .btn-student-login {
            background: var(--primary-blue);
            color: var(--text-white);
            box-shadow: 0 4px 15px rgba(79, 70, 233, 0.3);
        }

        .btn-student-login:hover {
            background: #4338ca;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(79, 70, 233, 0.45);
        }

        .btn-student-reg {
            background: transparent;
            color: #a5b4fc;
            border: 1px solid rgba(79, 70, 233, 0.4);
        }

        .btn-student-reg:hover {
            background: rgba(79, 70, 233, 0.1);
            color: var(--text-white);
            border-color: var(--primary-blue);
        }

        /* Teacher Action Button Config */
        .btn-teacher-login {
            background: var(--teacher-accent);
            color: var(--text-white);
            box-shadow: 0 4px 15px rgba(221, 36, 118, 0.3);
        }

        .btn-teacher-login:hover {
            background: #be123c;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(221, 36, 118, 0.45);
        }

        .btn-teacher-reg {
            background: transparent;
            color: #fda4af;
            border: 1px solid rgba(221, 36, 118, 0.4);
        }

        .btn-teacher-reg:hover {
            background: rgba(221, 36, 118, 0.1);
            color: var(--text-white);
            border-color: var(--teacher-accent);
        }

        /* FOOTER STRUCTURE Layout component */
        footer {
            background: rgba(15, 23, 42, 0.8);
            border-top: 1px solid var(--glass-border);
            backdrop-filter: blur(10px);
            margin-top: auto;
            position: relative;
            z-index: 1;
            padding: 20px 40px;
        }

        .footer-content {
            max-width: 1440px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .footer-info p {
            font-size: 0.85rem;
            color: var(--text-white);
            font-weight: 600;
        }

        .footer-info span {
            font-size: 0.8rem;
            color: var(--text-secondary);
            display: block;
            margin-top: 2px;
        }

        .footer-links {
            display: flex;
            gap: 20px;
        }

        .footer-links a {
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.85rem;
            transition: color 0.2s;
        }

        .footer-links a:hover {
            color: var(--primary-blue);
        }

        /* Embedded Essential Keyframe Animations Layout */
        @keyframes pulseGlow {
            0% { transform: scale(1) translate(0, 0); opacity: 0.2; }
            100% { transform: scale(1.1) translate(30px, 20px); opacity: 0.3; }
        }

        @keyframes floatUp {
            0% { transform: translateY(0) scale(1); opacity: 0; }
            10% { opacity: 0.4; }
            90% { opacity: 0.4; }
            100% { transform: translateY(-110vh) scale(0.4); opacity: 0; }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes blink {
            from, to { border-color: transparent; }
            50% { border-color: var(--secondary-purple); }
        }

        /* ADVANCED RESPONSIVE GRID OVERRIDES (Mobile Friendly) */
        @media (max-width: 1024px) {
            .features-grid { grid-template-columns: repeat(2, 1fr); }
            .hero-title { font-size: 2.3rem; }
        }

        @media (max-width: 868px) {
            body { overflow-y: auto; }
            .main-wrapper { flex-direction: column; padding: 15px; }
            .left-panel, .right-panel { width: 100%; padding: 15px; }
            .features-grid { grid-template-columns: repeat(2, 1fr); }
            .stats-row { grid-template-columns: repeat(2, 1fr); gap: 10px; }
            footer { padding: 20px 15px; }
            .footer-content { flex-direction: column; text-align: center; }
            .footer-links { justify-content: center; flex-wrap: wrap; }
        }

        @media (max-width: 480px) {
            .features-grid { grid-template-columns: 1fr; }
            .hero-title { font-size: 1.9rem; }
            .auth-glass-card { padding: 25px 20px; }
        }
    </style>
</head>
<body>

    <div class="bg-glow-container">
        <div class="glow-circle glow-1"></div>
        <div class="glow-circle glow-2"></div>
        <div class="particles" id="particle-canvas"></div>
    </div>

    <main class="main-wrapper">
        
        <section class="left-panel">
            
            <div class="branding-header">
                <div class="logo-circle">Q</div>
                <div class="branding-text">
                    <h2>QUIZIFY</h2>
                    <span>Smart Quiz Platform</span>
                </div>
            </div>

            <div class="hero-section">
                <h1 class="hero-title">
                    Learn, Compete & Achieve<br>With <span class="typing-text" id="typing-engine">Quizify</span>
                </h1>
                <h3 class="hero-subheading">An Advanced Online Quiz Platform Designed For Students, Learners, Teachers And Educational Institutions.</h3>
                <p class="hero-desc">
                    Quizify helps learners improve their knowledge through smart quizzes, certificates, analytics, rankings, and AI-powered assessments. Teachers can create, manage and evaluate quizzes with an intuitive dashboard.
                </p>
            </div>

            <div class="features-grid">
                <div class="feature-card"><span>🎯</span> Practice Quizzes</div>
                <div class="feature-card"><span>🏆</span> Earn Certificates</div>
                <div class="feature-card"><span>📊</span> Analytics</div>
                <div class="feature-card"><span>🤖</span> AI Generated</div>
                <div class="feature-card"><span>📚</span> Daily Learning</div>
                <div class="feature-card"><span>🚀</span> Skill Dev</div>
                <div class="feature-card"><span>🎖</span> Leaderboards</div>
                <div class="feature-card"><span>⏱</span> Timed Tests</div>
            </div>

            <div class="stats-row">
                <div class="stat-box">
                    <div class="stat-number" data-target="50">0</div>
                    <div class="stat-label">Students</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number" data-target="5">0</div>
                    <div class="stat-label">Teachers</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number" data-target="50">0</div>
                    <div class="stat-label">Quiz Attempts</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number" data-target="95">0</div>
                    <div class="stat-label">Success Rate</div>
                </div>
            </div>

            <div class="quotes-container">
                <p class="quote-text" id="quote-render-engine">"Success is the sum of small efforts repeated day in and day out."</p>
            </div>

        </section>

        <section class="right-panel">
            <div class="auth-glass-card">
                
                <div class="auth-card-header">
                    <div class="logo-circle">Q</div>
                    <h3>Get Started</h3>
                    <p>Choose Your Portal</p>
                </div>

                <div class="portal-section student-zone">
                    <div class="portal-title">
                        <span>🎓</span> Student Zone
                    </div>
                    <div class="action-group">
                        <a href="dashboard/login.php" class="auth-btn btn-student-login">Student Login</a>
                        <a href="dashboard/studentregister.php" class="auth-btn btn-student-reg">Student Registration</a>
                    </div>
                </div>

                <div class="portal-section teacher-zone">
                    <div class="portal-title">
                        <span>👨‍🏫</span> Institutional Faculty
                    </div>
                    <div class="action-group">
                        <a href="admin/adminlogin.php" class="auth-btn btn-teacher-login">Teacher Login</a>
                        <a href="admin/adminregister.php" class="auth-btn btn-teacher-reg">Teacher Registration</a>
                    </div>
                </div>

            </div>
        </section>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-info">
                <p>Quizify © 2026</p>
                <span>Made For Smart Learning & Assessment</span>
            </div>
            <nav class="footer-links" aria-label="Footer Navigation">
                <a href="dashboard/login.php">Student Login</a>
                <a href="dashboard/studentregister.php">Student Registration</a>
                <a href="admin/adminlogin.php">Teacher Login</a>
                <a href="admin/adminregister.php">Teacher Registration</a>
            </nav>
        </div>
    </footer>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            
            // 1. DYNAMIC PARTICLE SIMULATOR GENERATION
            const particleContainer = document.getElementById('particle-canvas');
            const particleCount = 12;
            
            for(let i = 0; i < particleCount; i++) {
                const span = document.createElement('span');
                span.classList.add('particle');
                
                // Randomized dimensional properties sizing configuration metrics
                const size = Math.floor(Math.random() * 35) + 10;
                const leftPos = Math.floor(Math.random() * 90) + 5;
                const delay = Math.floor(Math.random() * 8);
                const duration = Math.floor(Math.random() * 10) + 10;
                
                span.style.width = `${size}px`;
                span.style.height = `${size}px`;
                span.style.left = `${leftPos}%`;
                span.style.animationDelay = `${delay}s`;
                span.style.animationDuration = `${duration}s`;
                
                particleContainer.appendChild(span);
            }

            // 2. HERO SECTION LOGICAL TYPING ANIMATION MACHINE
            const words = ["Quizify", "Knowledge", "Success"];
            let wordIndex = 0;
            let charIndex = 0;
            let isDeleting = false;
            const typingTarget = document.getElementById("typing-engine");

            function typeEffect() {
                const currentWord = words[wordIndex];
                if (isDeleting) {
                    typingTarget.textContent = currentWord.substring(0, charIndex - 1);
                    charIndex--;
                } else {
                    typingTarget.textContent = currentWord.substring(0, charIndex + 1);
                    charIndex++;
                }

                let typingSpeed = isDeleting ? 60 : 120;

                if (!isDeleting && charIndex === currentWord.length) {
                    typingSpeed = 2000; // Hold string view display sequence timing metrics
                    isDeleting = true;
                } else if (isDeleting && charIndex === 0) {
                    isDeleting = false;
                    wordIndex = (wordIndex + 1) % words.length;
                    typingSpeed = 400; // Delay transition to subsequent lexical arrays
                }

                setTimeout(typeEffect, typingSpeed);
            }
            typeEffect();

            // 3. RUNTIME METRIC STEPPED INCREMENT COUNTER MOTOR
            const counters = document.querySelectorAll('.stat-number');
            
            counters.forEach(counter => {
                const target = parseInt(counter.getAttribute('data-target'));
                const isPercent = counter.getAttribute('data-target') === "95";
                const suffix = isPercent ? "%" : "+";
                
                // Execution step calculations
                const duration = 2000; 
                const stepTime = Math.max(Math.floor(duration / target), 15);
                let current = 0;
                
                const incrementer = setInterval(() => {
                    // Compute adaptive intervals for accelerated step sequencing
                    let increment = Math.ceil(target / 40);
                    current += increment;
                    
                    if (current >= target) {
                        counter.textContent = target + suffix;
                        clearInterval(incrementer);
                    } else {
                        counter.textContent = current + suffix;
                    }
                }, stepTime);
            });

            // 4. MOTIVATIONAL QUOTES INTERACTIVE TIMED CAROUSEL
            const quotes = [
                "\"Success is the sum of small efforts repeated day in and day out.\"",
                "\"Learning never exhausts the mind.\"",
                "\"Knowledge grows when shared.\"",
                "\"The beautiful thing about learning is nobody can take it away from you.\""
            ];
            let quoteIndex = 0;
            const quoteElement = document.getElementById("quote-render-engine");

            setInterval(() => {
                quoteElement.style.opacity = 0;
                setTimeout(() => {
                    quoteIndex = (quoteIndex + 1) % quotes.length;
                    quoteElement.textContent = quotes[quoteIndex];
                    quoteElement.style.opacity = 1;
                }, 500); // Transition buffer syncing
            }, 5000); // Sequence lifecycle cadence
        });
    </script>
</body>
</html>