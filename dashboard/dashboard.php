<?php
session_start();

$isLoggedIn = isset($_SESSION['user_id']);
$userId = $isLoggedIn ? (int)$_SESSION['user_id'] : 0;

$username = $isLoggedIn
    ? ($_SESSION['user_name'] ?? ($_SESSION['Username'] ?? "Student"))
    : "Guest";

include($_SERVER['DOCUMENT_ROOT'].'/quiz_system/config.php');

$page = $_GET['page'] ?? 'home';

// ==========================================
// DATA ACCUMULATION & METRIC PROCESSING
// ==========================================
$totalQuizzesCount = 0;
$attemptedCount = 0;
$averageScoreText = "0%";
$lastScoreValue = 0;

$userPoints = 0;
$userBadge = "Beginner";

// 1. Total Quizzes Count (Global Context)
$totalQuizQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM quizzes");
if ($totalQuizQuery) {
    $totalQuizRow = mysqli_fetch_assoc($totalQuizQuery);
    $totalQuizzesCount = (int)($totalQuizRow['total'] ?? 0);
}

if ($isLoggedIn && $userId > 0) {
    // 2. Attempted Quizzes for logged-in individual
    $attemptedQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM result WHERE User_id = $userId");
    if ($attemptedQuery) {
        $attemptedRow = mysqli_fetch_assoc($attemptedQuery);
        $attemptedCount = (int)($attemptedRow['total'] ?? 0);
    }

    // 3. Average Score computation loop logic
    $scoreQuery = mysqli_query($conn, "SELECT total_questions, correct_answers FROM result WHERE User_id = $userId");
    if ($scoreQuery && mysqli_num_rows($scoreQuery) > 0) {
        $runningTotalQuestions = 0;
        $runningCorrectAnswers = 0;
        while ($scoreRow = mysqli_fetch_assoc($scoreQuery)) {
            $runningTotalQuestions += (int)($scoreRow['total_questions'] ?? 0);
            $runningCorrectAnswers += (int)($scoreRow['correct_answers'] ?? 0);
        }
        if ($runningTotalQuestions > 0) {
            $averageScoreText = round(($runningCorrectAnswers / $runningTotalQuestions) * 100) . "%";
        }
    }

    // 4. Last Score value selector configuration
    $lastScoreQuery = mysqli_query($conn, "SELECT Score FROM result WHERE User_id = $userId ORDER BY Id DESC LIMIT 1");
    if ($lastScoreQuery && mysqli_num_rows($lastScoreQuery) > 0) {
        $lastScoreRow = mysqli_fetch_assoc($lastScoreQuery);
        $lastScoreValue = (int)($lastScoreRow['Score'] ?? 0);
    }

    // 5. Fetch User Profile metrics for Performance Card
    $userPerfQuery = mysqli_query($conn, "SELECT points, badge FROM users WHERE id = $userId");
    if ($userPerfQuery && mysqli_num_rows($userPerfQuery) > 0) {
        $userPerfRow = mysqli_fetch_assoc($userPerfQuery);
        $userPoints = (int)($userPerfRow['points'] ?? 0);
        $userBadge = htmlspecialchars($userPerfRow['badge'] ?? 'Bronze', ENT_QUOTES, 'UTF-8');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
/* RESET */
*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Inter', sans-serif;
}

body{
background:#0f172a;
color:#e2e8f0;
overflow-x:hidden;
}

/* LAYOUT */
.layout{
display:flex;
min-height:100vh;
}

/* SIDEBAR */
.sidebar{
width:260px;
background:linear-gradient(180deg, #0b1324, #111e36);
padding:25px 20px;
position:sticky;
top:0;
height:100vh;
border-right:1px solid rgba(255,255,255,0.05);
display:flex;
flex-direction:column;
z-index:100;
}

.sidebar h2{
color:white;
font-size:22px;
font-weight:700;
letter-spacing:-0.5px;
margin-bottom:2px;
background:linear-gradient(135deg, #fff 30%, #94a3b8 100%);
-webkit-background-clip:text;
-webkit-text-fill-color:transparent;
}

.sidebar p{
color:#64748b;
font-size:13px;
margin-bottom:25px;
font-weight:500;
padding-left:2px;
}

.sidebar a{
display:flex;
align-items:center;
gap:12px;
padding:12px 14px;
margin:4px 0;
border-radius:12px;
color:#94a3b8;
text-decoration:none;
transition:all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
font-size:14px;
font-weight:500;
}

.sidebar a:hover{
background:rgba(255, 255, 255, 0.04);
color:#fff;
transform:translateX(4px);
}

.sidebar a.active-menu {
background:linear-gradient(135deg, #6366f1, #4f46e5);
color:#ffffff;
box-shadow:0 4px 15px rgba(99, 102, 241, 0.35);
font-weight:600;
}

.sidebar a.active-menu:hover {
transform:translateX(0px);
background:linear-gradient(135deg, #6366f1, #4f46e5);
box-shadow:0 6px 20px rgba(99, 102, 241, 0.45);
}

/* MAIN */
.main{
flex:1;
padding:30px;
max-width:calc(100% - 260px);
}

/* PREMIUM HEADER STYLE WITH GRADIENT & GLOW */
.header{
display:flex;
justify-content:space-between;
align-items:center;
padding:30px;
background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%);
border-radius:20px;
box-shadow: 0 20px 40px rgba(0,0,0,0.4), inset 0 1px 1px rgba(255,255,255,0.1);
margin-bottom:25px;
position:relative;
overflow:hidden;
border:1px solid rgba(99, 102, 241, 0.15);
}

.header::before {
content:'';
position:absolute;
top:-50%;
left:-50%;
width:200%;
height:200%;
background:radial-gradient(circle, rgba(99,102,241,0.15) 0%, transparent 70%);
pointer-events:none;
animation: pulseGlow 8s ease-in-out infinite alternate;
}

@keyframes pulseGlow {
0% { transform: scale(0.95); opacity: 0.8; }
100% { transform: scale(1.05); opacity: 1; }
}

.ai-header-title-block {
display: flex;
align-items: center;
gap: 20px;
position: relative;
z-index: 2;
}

/* Pure CSS Animated Core Node Icon */
.ai-icon-css {
width: 54px;
height: 54px;
background: radial-gradient(circle, #818cf8 0%, #4f46e5 100%);
border-radius: 16px;
position: relative;
display: flex;
align-items: center;
justify-content: center;
box-shadow: 0 0 20px rgba(99, 102, 241, 0.6);
animation: floatIcon 4s ease-in-out infinite;
}

.ai-icon-css::after {
content: '';
width: 18px;
height: 18px;
background: #ffffff;
border-radius: 50%;
box-shadow: 0 0 12px #ffffff;
animation: pulseCore 2s ease-in-out infinite alternate;
}

@keyframes floatIcon {
0%, 100% { transform: translateY(0) rotate(0deg); }
50% { transform: translateY(-6px) rotate(3deg); }
}

@keyframes pulseCore {
0% { transform: scale(0.8); opacity: 0.7; }
100% { transform: scale(1.2); opacity: 1; }
}

.header h2{
font-size:26px;
font-weight:700;
letter-spacing:-0.5px;
color:#fff;
margin-bottom:4px;
}

.header small{
color:#94a3b8;
font-size:14px;
font-weight:400;
}

.profile{
background:rgba(56, 189, 248, 0.1);
padding:9px 16px;
border-radius:12px;
color:#38bdf8;
margin-right:12px;
font-weight:600;
font-size:14px;
border:1px solid rgba(56, 189, 248, 0.2);
display:inline-block;
position: relative;
z-index: 2;
}

.logout{
background:#ef4444;
padding:9px 18px;
border-radius:12px;
color:white;
text-decoration:none;
font-size:14px;
font-weight:600;
transition:all 0.3s ease;
box-shadow:0 4px 12px rgba(239, 68, 68, 0.2);
display:inline-block;
position: relative;
z-index: 2;
}

.logout:hover{
background:#dc2626;
transform:translateY(-2px);
box-shadow:0 6px 15px rgba(239, 68, 68, 0.3);
}

/* SEARCH */
.search{
margin:25px 0;
}

.search input{
width:100%;
padding:16px 20px;
border-radius:16px;
border:1px solid rgba(255,255,255,0.05);
outline:none;
background:#1e293b;
color:white;
font-size:15px;
transition:all 0.3s ease;
box-shadow:0 4px 10px rgba(0,0,0,0.1);
}

.search input:focus{
border-color:#6366f1;
background:#111c33;
box-shadow:0 0 0 4px rgba(99, 102, 241, 0.15), 0 10px 20px rgba(0,0,0,0.2);
}

/* STATS CARDS GRID */
.stats{
display:grid;
grid-template-columns:repeat(4,1fr);
gap:20px;
margin-top:25px;
}

.card{
background:#111c33;
padding:22px;
border-radius:18px;
box-shadow:0 10px 25px rgba(0,0,0,0.25);
border:1px solid rgba(255,255,255,0.03);
transition:all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
position:relative;
display:flex;
flex-direction:column;
justify-content:space-between;
overflow:hidden;
min-height:140px;
}

.card:hover{
transform:translateY(-6px);
box-shadow:0 15px 35px rgba(0,0,0,0.4), 0 0 15px rgba(99, 102, 241, 0.1);
border-color:rgba(255,255,255,0.08);
}

.card-info {
position:relative;
z-index:2;
}

.card h3{
font-size:13px;
color:#94a3b8;
text-transform:uppercase;
letter-spacing:0.5px;
font-weight:600;
}

.card p{
font-size:28px;
margin-top:6px;
font-weight:700;
color:#fff;
letter-spacing:-0.5px;
}

.sparkline-container {
width:100%;
height:45px;
margin-top:auto;
position:relative;
bottom:-5px;
left:0;
z-index:1;
}

/* USER PERFORMANCE DISPLAY SECTION */
.user-performance-wrapper {
margin-top: 35px;
background: #111c33;
border: 1px solid rgba(255,255,255,0.04);
border-radius: 20px;
padding: 25px;
box-shadow: 0 12px 30px rgba(0,0,0,0.2);
}

.user-performance-wrapper h2 {
font-size: 20px;
font-weight: 700;
margin-bottom: 20px;
color: #fff;
letter-spacing: -0.5px;
}

.perf-grid {
display: grid;
grid-template-columns: repeat(4, 1fr);
gap: 20px;
}

.perf-subcard {
background: rgba(30, 41, 59, 0.4);
border: 1px solid rgba(255,255,255,0.03);
padding: 20px;
border-radius: 14px;
text-align: center;
transition: background 0.2s ease;
}

.perf-subcard:hover {
background: rgba(30, 41, 59, 0.7);
}

.perf-subcard label {
display: block;
font-size: 12px;
font-weight: 600;
color: #64748b;
text-transform: uppercase;
margin-bottom: 8px;
letter-spacing: 0.5px;
}

.perf-subcard span {
font-size: 22px;
font-weight: 700;
color: #fff;
}

.perf-subcard .highlight-blue { color: #38bdf8; }
.perf-subcard .highlight-amber { color: #fbbf24; }
.perf-subcard .highlight-purple { color: #a855f7; }
.perf-subcard .highlight-emerald { color: #34d399; }

/* TRENDING SECTION & RE-STYLED GLASSMOPHISM CARDS */
.trending{
margin-top:40px;
}

.trending h2{
font-size:22px;
font-weight:700;
margin-bottom:20px;
color:#fff;
letter-spacing:-0.5px;
}

.trend-list{
display:grid;
grid-template-columns:repeat(3,1fr);
gap:25px;
}

.trend-card{
background: rgba(30, 41, 59, 0.4);
backdrop-filter: blur(12px);
-webkit-backdrop-filter: blur(12px);
border-radius:18px;
overflow:hidden;
transition:all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
border:1px solid rgba(255,255,255,0.06);
box-shadow:0 10px 30px rgba(0,0,0,0.2);
display:flex;
flex-direction:column;
min-height: 240px;
}

/* Glassmorphism Dynamic CSS Presentation Area */
.glass-banner-area {
width: 100%;
height: 110px;
position: relative;
overflow: hidden;
display: flex;
align-items: center;
justify-content: center;
border-bottom: 1px solid rgba(255,255,255,0.04);
}

.glass-banner-area::before {
content: '';
position: absolute;
width: 130%;
height: 130%;
background: linear-gradient(135deg, #312e81 0%, #1e1b4b 50%, #4c1d95 100%);
opacity: 0.85;
transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.trend-card:nth-child(2n) .glass-banner-area::before {
background: linear-gradient(135deg, #065f46 0%, #111c33 60%, #0369a1 100%);
}

.trend-card:nth-child(3n) .glass-banner-area::before {
background: linear-gradient(135deg, #1e3a8a 0%, #1e1b4b 50%, #881337 100%);
}

.trend-card:hover .glass-banner-area::before {
transform: scale(1.12) rotate(2deg);
}

/* CSS Inner Glow Particle Overlay */
.glass-glow-particle {
position: absolute;
width: 60px;
height: 60px;
background: radial-gradient(circle, rgba(99,102,241,0.4) 0%, transparent 70%);
top: 20px;
left: 20px;
pointer-events: none;
animation: particleFloat 4s ease-in-out infinite alternate;
}

@keyframes particleFloat {
0% { transform: translate(0, 0); }
100% { transform: translate(30px, 15px); }
}

.trend-card:hover{
transform:translateY(-8px);
box-shadow:0 20px 40px rgba(0,0,0,0.45), 0 0 25px rgba(99, 102, 241, 0.15);
border-color:rgba(255,255,255,0.15);
}

.trend-content{
padding:20px;
display:flex;
flex-direction:column;
flex-grow:1;
justify-content:space-between;
background: rgba(15, 23, 42, 0.2);
}

.trend-content h3 {
font-size:16px;
font-weight:600;
color:#fff;
margin-bottom:16px;
line-height:1.4;
text-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.trend-content small{
display:none;
}

.play-btn{
display:inline-block;
text-align:center;
padding:11px 16px;
background:linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
color:white;
border-radius:10px;
text-decoration:none;
font-weight:600;
font-size:14px;
transition:all 0.3s ease;
box-shadow:0 4px 15px rgba(99, 102, 241, 0.25);
border:none;
cursor:pointer;
}

.play-btn:hover{
background:linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
transform:translateY(-2px);
box-shadow:0 6px 20px rgba(99, 102, 241, 0.4);
}

/* RESPONSIVE RE-STYLES */
@media(max-width:1100px){
.stats, .perf-grid{
grid-template-columns:repeat(2,1fr);
}
.trend-list{
grid-template-columns:repeat(2,1fr);
}
}

@media(max-width:900px){
.layout {
flex-direction:column;
}
.sidebar {
width:100%;
height:auto;
position:relative;
border-right:none;
border-bottom:1px solid rgba(255,255,255,0.05);
padding:20px;
}
.main {
width:100%;
max-width:100%;
padding:20px;
}
.trend-list{
grid-template-columns:1fr;
}
}

@media(max-width:550px){
.stats, .perf-grid{
grid-template-columns:1fr;
}
.header {
flex-direction:column;
align-items:flex-start;
gap:20px;
}
.ai-header-title-block {
width: 100%;
}
.header div:last-child {
width:100%;
display:flex;
justify-content:space-between;
align-items:center;
}
}

/* FINAL CHANGE 2: NEW AI TUTOR FLOATING BUTTON STYLES */
.ai-tutor-launcher {
position: fixed;
bottom: 25px;
right: 25px;
width: 70px;
height: 70px;
background: linear-gradient(135deg, #a855f7 0%, #6366f1 100%);
border-radius: 50%;
box-shadow: 0 0 25px rgba(168, 85, 247, 0.6);
cursor: pointer;
display: flex;
flex-direction: column;
justify-content: center;
align-items: center;
z-index: 99999;
transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
border: 1px solid rgba(255,255,255,0.2);
color: #ffffff;
font-weight: 700;
font-size: 11px;
text-transform: uppercase;
letter-spacing: 0.5px;
line-height: 1.2;
text-align: center;
animation: floatButton 3s ease-in-out infinite;
}

.ai-tutor-launcher:hover {
transform: scale(1.08) translateY(-3px);
box-shadow: 0 0 35px rgba(168, 85, 247, 0.8);
background: linear-gradient(135deg, #b55fe6 0%, #4f46e5 100%);
}

@keyframes floatButton {
0%, 100% { transform: translateY(0); }
50% { transform: translateY(-6px); }
}

/* AI CHAT TUTOR OVERLAY PANEL STYLES */
.ai-tutor-wrapper {
position: fixed;
top: 0;
left: 0;
width: 100vw;
height: 100vh;
background: rgba(15, 23, 42, 0.98);
display: flex;
flex-direction: column;
overflow: hidden;
z-index: 999999;
opacity: 0;
pointer-events: none;
transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
backdrop-filter: blur(8px);
}

.ai-tutor-wrapper.active-box {
opacity: 1;
pointer-events: auto;
}

.ai-tutor-header {
padding: 20px 40px;
background: #0b1324;
border-bottom: 1px solid rgba(255,255,255,0.06);
display: flex;
justify-content: space-between;
align-items: center;
}

.ai-tutor-header h3 {
font-size: 20px;
font-weight: 700;
color: white;
display: flex;
align-items: center;
gap: 12px;
letter-spacing: -0.5px;
}

.ai-tutor-close {
cursor: pointer;
color: #94a3b8;
font-size: 28px;
line-height: 1;
transition: transform 0.2s, color 0.2s;
padding: 5px;
}

.ai-tutor-close:hover {
color: #ef4444;
transform: scale(1.1);
}

.ai-tutor-chat {
flex: 1;
padding: 40px;
overflow-y: auto;
background: #0b1222;
display: flex;
flex-direction: column;
gap: 20px;
max-width: 1000px;
width: 100%;
margin: 0 auto;
}

.ai-tutor-chat::-webkit-scrollbar {
width: 6px;
}
.ai-tutor-chat::-webkit-scrollbar-thumb {
background: rgba(255,255,255,0.12);
border-radius: 10px;
}

.ai-msg {
max-width: 80%;
padding: 16px 22px;
border-radius: 16px;
font-size: 15px;
line-height: 1.6;
word-wrap: break-word;
white-space: pre-wrap;
box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.ai-msg.student {
background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
color: white;
align-self: flex-end;
border-bottom-right-radius: 2px;
}

.ai-msg.tutor {
background: #1e293b;
color: #e2e8f0;
align-self: flex-start;
border-bottom-left-radius: 2px;
border: 1px solid rgba(255,255,255,0.05);
}

.ai-msg pre, .ai-msg code {
background: #0f172a;
padding: 4px 8px;
border-radius: 6px;
font-family: 'Courier New', Courier, monospace;
color: #38bdf8;
font-size: 14px;
display: inline-block;
margin: 4px 0;
}

.ai-msg pre {
display: block;
padding: 15px;
overflow-x: auto;
width: 100%;
line-height: 1.4;
color: #e2e8f0;
border: 1px solid rgba(255,255,255,0.05);
}

.ai-tutor-footer {
padding: 25px 40px;
background: #0b1324;
border-top: 1px solid rgba(255,255,255,0.06);
}

.ai-tutor-inner-footer {
max-width: 1000px;
width: 100%;
margin: 0 auto;
}

.ai-tutor-input-box {
display: flex;
background: #1e293b;
border-radius: 14px;
padding: 8px 10px 8px 18px;
border: 1px solid rgba(255,255,255,0.06);
align-items: center;
gap: 10px;
}

.ai-tutor-input-box input {
flex: 1;
background: transparent;
border: none;
outline: none;
color: white;
font-size: 15px;
padding: 8px 0;
}

.ai-tutor-send {
background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
border: none;
color: white;
padding: 10px 24px;
border-radius: 10px;
font-size: 14px;
font-weight: 600;
cursor: pointer;
transition: all 0.2s;
box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
}

.ai-tutor-send:hover {
transform: translateY(-1px);
box-shadow: 0 6px 15px rgba(99, 102, 241, 0.3);
}

.ai-typing {
display: flex;
gap: 4px;
padding: 6px 0;
align-items: center;
}
.ai-typing span {
width: 8px;
height: 8px;
background: #94a3b8;
border-radius: 50%;
animation: aiBounce 1.4s infinite ease-in-out both;
}
.ai-typing span:nth-child(1) { animation-delay: -0.32s; }
.ai-typing span:nth-child(2) { animation-delay: -0.16s; }

@keyframes aiBounce {
0%, 80%, 100% { transform: scale(0); }
40% { transform: scale(1.0); }
}

@media(max-width: 768px) {
    .ai-tutor-header { padding: 15px 20px; }
    .ai-tutor-chat { padding: 20px; }
    .ai-tutor-footer { padding: 15px 20px; }
    .ai-msg { max-width: 90%; }
}
</style>
</head>

<body>
<div class="layout">

<div class="sidebar">
    <h2>Quiz System</h2>
    <p><?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></p>

    <a href="dashboard.php?page=home" class="<?php echo $page == 'home' ? 'active-menu' : ''; ?>">🏠 Dashboard</a>
    <a href="dashboard.php?page=quizzes" class="<?php echo $page == 'quizzes' ? 'active-menu' : ''; ?>">📚 Available Quiz</a>
    <a href="dashboard.php?page=result" class="<?php echo $page == 'result' ? 'active-menu' : ''; ?>">📊 My Attempts</a>

    <hr style="border:none; border-top:1px solid rgba(255,255,255,0.05); margin:10px 0;">

    <a href="dashboard.php?page=performance" class="<?php echo $page == 'performance' ? 'active-menu' : ''; ?>">📈 Performance</a>
    <a href="dashboard.php?page=leaderboard" class="<?php echo $page == 'leaderboard' ? 'active-menu' : ''; ?>">🏆 Leaderboard</a>

    <hr style="border:none; border-top:1px solid rgba(255,255,255,0.05); margin:10px 0;">

    <a href="dashboard.php?page=my_certificates" class="<?php echo $page == 'my_certificates' ? 'active-menu' : ''; ?>">🎓 Certificate</a>
    <a href="dashboard.php?page=notifications" class="<?php echo $page == 'notifications' ? 'active-menu' : ''; ?>">🔔 Notifications</a>
  

    <hr style="border:none; border-top:1px solid rgba(255,255,255,0.05); margin:10px 0;">

    <a href="dashboard.php?page=settings" class="<?php echo $page == 'settings' ? 'active-menu' : ''; ?>">⚙️ Settings</a>
    <a href="../logout.php">🚪 Logout</a>
</div>

<div class="main">

    <div class="header">
        <div class="ai-header-title-block">
            <div class="ai-icon-css" title="AI Core Engine Active"></div>
            <div>
                <h2><?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?> Dashboard</h2>
                <small>Welcome back! Let's master your course targets today.</small>
            </div>
        </div>

        <div>
            <?php if($isLoggedIn){ ?>
                <span class="profile"><?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></span>
                <a href="../logout.php" class="logout">Logout</a>
            <?php } else { ?>
                <a href="login.php" class="logout" style="background:#2563eb; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);">Login</a>
                <a href="studentregister.php" class="logout" style="background:#06b6d4; box-shadow: 0 4px 12px rgba(6, 182, 212, 0.25);">Register</a>
            <?php } ?>
        </div>
    </div>

<?php if($page == 'home'){ ?>
    <div class="search">
        <input type="text" id="quizSearchInput" placeholder="Search quizzes, topics like PHP, JavaScript...">
    </div>

    <div class="stats">
        <div class="card">
            <div class="card-info">
                <h3>Total Quiz</h3>
                <p><?php echo $totalQuizzesCount; ?></p>
            </div>
            <div class="sparkline-container">
                <canvas id="sparklineTotalQuiz"></canvas>
            </div>
        </div>

        <div class="card">
            <div class="card-info">
                <h3>Attempted</h3>
                <p><?php echo $attemptedCount; ?></p>
            </div>
            <div class="sparkline-container">
                <canvas id="sparklineAttempted"></canvas>
            </div>
        </div>

        <div class="card">
            <div class="card-info">
                <h3>Average Score</h3>
                <p><?php echo $averageScoreText; ?></p>
            </div>
            <div class="sparkline-container">
                <canvas id="sparklineAvgScore"></canvas>
            </div>
        </div>

        <div class="card">
            <div class="card-info">
                <h3>Last Score</h3>
                <p><?php echo $lastScoreValue; ?></p>
            </div>
            <div class="sparkline-container">
                <canvas id="sparklineLastScore"></canvas>
            </div>
        </div>
    </div>

    <div class="user-performance-wrapper">
        <h2>📊 My Performance Insights</h2>
        <div class="perf-grid">
            <div class="perf-subcard">
                <label>Total Points</label>
                <span class="highlight-blue"><?php echo $userPoints; ?> XP</span>
            </div>
            <div class="perf-subcard">
                <label>Current Badge</label>
                <span class="highlight-amber">🏆 <?php echo $userBadge; ?></span>
            </div>
            <div class="perf-subcard">
                <label>Quizzes Completed</label>
                <span class="highlight-purple"><?php echo $attemptedCount; ?></span>
            </div>
            <div class="perf-subcard">
                <label>Success Threshold</label>
                <span class="highlight-emerald"><?php echo $averageScoreText; ?> Avg</span>
            </div>
        </div>
    </div>

    <div class="trending">
        <h2>🔥 Practice Workspaces</h2>
        <div class="trend-list" id="quizContainer">

        <?php
        $q = mysqli_query($conn, "SELECT * FROM quizzes WHERE quiz_type='practice';");
        if ($q && mysqli_num_rows($q) > 0) {
            while($row = mysqli_fetch_assoc($q)){
                $quizTitleSafe = htmlspecialchars($row['title'] ?? 'Untitled Quiz', ENT_QUOTES, 'UTF-8');
                $quizIdSafe = (int)($row['id'] ?? 0);
        ?>
            <div class="trend-card" data-title="<?php echo strtolower($quizTitleSafe); ?>">
                <div class="glass-banner-area">
                    <div class="glass-glow-particle"></div>
                </div>
                <div class="trend-content">
                    <h3><?php echo $quizTitleSafe; ?></h3>
                    <a class="play-btn" href="/quiz_system/dashboard/level.php?quiz_id=<?php echo $quizIdSafe; ?>">
                        ▶ Start Quiz
                    </a>
                </div>
            </div>
        <?php 
            }
        } else {
            echo "<p style='grid-column: 1/-1; color:#94a3b8;'>No practice quizzes available at the moment.</p>";
        }
        ?>
        </div>
    </div>

<script>
function createSparkline(elementId, dataset, lineColor, fillColor) {
    const canvasObj = document.getElementById(elementId);
    if(!canvasObj) return;
    const ctx = canvasObj.getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: new Array(dataset.length).fill(''),
            datasets: [{
                data: dataset,
                borderColor: lineColor,
                borderWidth: 2,
                pointRadius: 0,
                fill: true,
                backgroundColor: fillColor,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { enabled: false } },
            scales: { x: { display: false }, y: { display: false } }
        }
    });
}

document.addEventListener("DOMContentLoaded", function() {
    createSparkline('sparklineTotalQuiz', [8, 9, 9, 10, 10, 11, <?php echo $totalQuizzesCount; ?>], '#6366f1', 'rgba(99, 102, 241, 0.05)');
    createSparkline('sparklineAttempted', [0, 1, 1, 2, 2, 3, <?php echo $attemptedCount; ?>], '#38bdf8', 'rgba(56, 189, 248, 0.05)');
    createSparkline('sparklineAvgScore', [40, 50, 55, 60, 58, 62, <?php echo (int)$averageScoreText; ?>], '#34d399', 'rgba(52, 211, 153, 0.05)');
    createSparkline('sparklineLastScore', [5, 20, 45, 30, 75, 60, <?php echo $lastScoreValue; ?>], '#fbbf24', 'rgba(251, 191, 36, 0.05)');

    const searchInput = document.getElementById('quizSearchInput');
    const trendCards = document.querySelectorAll('#quizContainer .trend-card');

    if(searchInput) {
        searchInput.addEventListener('input', function() {
            const filterValue = this.value.toLowerCase().trim();
            trendCards.forEach(card => {
                const quizTitle = card.getAttribute('data-title') || '';
                if (quizTitle.includes(filterValue)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
});
</script>

<?php } else {
    if($page == 'quizzes') include('content/quizzes.php');
    elseif($page == 'result') include('content/result.php');
    elseif($page == 'performance') include('content/performance.php');
    elseif($page == 'leaderboard') include('content/leaderboard.php');
    elseif($page == 'my_certificates') include('content/my_certificates.php');
    elseif($page == 'notifications') include('content/user_notification.php');
    elseif($page == 'my_registrations') include('content/my_registrations.php');
    elseif($page == 'settings'){
        $path = __DIR__ . "/content/usersetting.php";
        if(file_exists($path)) include($path);
        else echo "<div style='padding:20px;color:red;'>Settings file not found!</div>";
    }
} ?>

</div>
</div>

<?php include('../footer.php'); ?>

<div id="registerPopup" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.7);z-index:9999;">
    <div style="background:#1e293b;width:400px;padding:30px;border-radius:20px;position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;box-shadow: 0 20px 50px rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.05);">
        <h2 style="color:#fff; margin-bottom: 10px;">🎓 Join Quizify</h2>
        <p style="color:#94a3b8; margin-bottom: 25px;">Register/Login to access quizzes</p>
        <a href="studentregister.php" style="padding:12px 22px;background:#2563eb;color:white;border-radius:10px;text-decoration:none;font-weight:600;margin-right:10px;display:inline-block;box-shadow: 0 4px 15px rgba(37, 99, 235, 0.25);">Register</a>
        <a href="login.php" style="padding:12px 22px;background:#06b6d4;color:white;border-radius:10px;text-decoration:none;font-weight:600;display:inline-block;box-shadow: 0 4px 15px rgba(6, 182, 212, 0.25);">Login</a>
    </div>
</div>

<script>
function showRegisterPopup(){
    document.getElementById('registerPopup').style.display='block';
}
</script>

<div class="ai-tutor-launcher" id="aiTutorLauncher" title="Ask AI Tutor">
    <span>AI</span>
    <span>Tutor</span>
</div>

<div class="ai-tutor-wrapper" id="aiTutorWrapper">
    <div class="ai-tutor-header">
        <h3>🤖 AI College Tutor Workspace</h3>
        <div class="ai-tutor-close" id="aiTutorClose">&times;</div>
    </div>
    <div class="ai-tutor-chat" id="aiTutorChat">
        <div class="ai-msg tutor">Koi coding ya theory concept me doubt hai? Pucho, mai explain kar dunga! 💻🚀</div>
    </div>
    <div class="ai-tutor-footer">
        <div class="ai-tutor-inner-footer">
            <div class="ai-tutor-input-box">
                <input type="text" id="aiTutorInput" placeholder="Apna coding sawal yahan pucho..." autocomplete="off">
                <button class="ai-tutor-send" id="aiTutorSendBtn">Send Message</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const launcher = document.getElementById('aiTutorLauncher');
    const wrapper = document.getElementById('aiTutorWrapper');
    const closeBtn = document.getElementById('aiTutorClose');
    const chatArea = document.getElementById('aiTutorChat');
    const inputField = document.getElementById('aiTutorInput');
    const sendBtn = document.getElementById('aiTutorSendBtn');

    const resolvedPath = 'content/chat_api.php';

    if(launcher && wrapper && closeBtn) {
        launcher.addEventListener('click', () => {
            wrapper.classList.add('active-box');
            document.body.style.overflow = 'hidden';
        });
        
        closeBtn.addEventListener('click', () => {
            wrapper.classList.remove('active-box');
            document.body.style.overflow = '';
        });
    }

    function appendMsg(text, type) {
        if(!chatArea) return;
        const msg = document.createElement('div');
        msg.classList.add('ai-msg', type);
        
        if (type === 'tutor') {
            let formattedText = text.replace(/`([^`]+)`/g, '<code>$1</code>');
            if (formattedText.includes('```')) {
                formattedText = formattedText.replace(/```([\s\S]+?)```/g, '<pre>$1</pre>');
            }
            msg.innerHTML = formattedText;
        } else {
            msg.textContent = text;
        }
        
        chatArea.appendChild(msg);
        chatArea.scrollTop = chatArea.scrollHeight;
    }

    function showTyping() {
        if(!chatArea) return;
        const typing = document.createElement('div');
        typing.classList.add('ai-msg', 'tutor');
        typing.id = 'aiTutorTyping';
        typing.innerHTML = '<div class="ai-typing"><span></span><span></span><span></span></div>';
        chatArea.appendChild(typing);
        chatArea.scrollTop = chatArea.scrollHeight;
    }

    function removeTyping() {
        const item = document.getElementById('aiTutorTyping');
        if(item) item.remove();
    }

    async function sendMsgToAI() {
        if(!inputField) return;
        const query = inputField.value.trim();
        if(!query) return;

        appendMsg(query, 'student');
        inputField.value = '';
        showTyping();

        try {
            const response = await fetch(resolvedPath, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: query })
            });
            
            if (!response.ok) throw new Error("HTTP error");
            const data = await response.json();
            removeTyping();

            if(data && data.success) {
                appendMsg(data.reply, 'tutor');
            } else {
                appendMsg(data.reply || "Server temporarily unavailable. Ek baar firse poochiye.", 'tutor');
            }
        } catch(e) {
            removeTyping();
            appendMsg("Connection issues encountered ya API key config missing hai backend me. Please review configurations.", 'tutor');
        }
    }

    if(sendBtn) sendBtn.addEventListener('click', sendMsgToAI);
    if(inputField) inputField.addEventListener('keydown', (e) => { if(e.key === 'Enter') sendMsgToAI(); });
});
</script>
</body>
</html>