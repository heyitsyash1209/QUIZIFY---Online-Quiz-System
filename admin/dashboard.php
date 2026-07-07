<?php
session_start();
include('../config.php');
// Login check
if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit();
}


$page = $_GET['page'] ?? 'home';

function getCount($conn, $table){

    if(!$conn) return 0;

    $check = mysqli_query($conn, "SHOW TABLES LIKE '$table'");

    if($check && mysqli_num_rows($check) > 0){

        $q = mysqli_query($conn, "SELECT COUNT(*) as total FROM $table");

        if($q){
            return mysqli_fetch_assoc($q)['total'];
        }
    }

    return 0;
}

$quiz_count = getCount($conn, "quizzes");
$question_count = getCount($conn, "quiz_questions");
$user_count = getCount($conn, "users");
$result_count = getCount($conn, "result");

/* 🔥 Most Active User */
$active_user = mysqli_query($conn,"
SELECT u.fullname, COUNT(r.Id) AS attempts
FROM result r
JOIN users u ON r.User_id = u.id
GROUP BY r.User_id
ORDER BY attempts DESC
LIMIT 1
");
$active_user = $active_user ? mysqli_fetch_assoc($active_user) : [];

/* 🏆 Highest Score */
$highest_score = mysqli_query($conn,"
SELECT q.Title, r.Score
FROM result r
JOIN quizzes q ON r.Quiz_id = q.ID
ORDER BY r.Score DESC
LIMIT 1
");
$highest_score = $highest_score ? mysqli_fetch_assoc($highest_score) : [];

/* 📚 Most Attempted Quiz */
$popular_quiz = mysqli_query($conn,"
SELECT q.Title, COUNT(r.Id) AS attempts
FROM result r
JOIN quizzes q ON r.Quiz_id = q.ID
GROUP BY r.Quiz_id
ORDER BY attempts DESC
LIMIT 1
");
$popular_quiz = $popular_quiz ? mysqli_fetch_assoc($popular_quiz) : [];

/* ⭐ Top Category */
$top_category = mysqli_query($conn,"
SELECT topic, COUNT(*) AS total
FROM questions
GROUP BY topic
ORDER BY total DESC
LIMIT 1
");
$top_category = $top_category ? mysqli_fetch_assoc($top_category) : [];

?>

<!DOCTYPE html>
<html>
<head>

<title>Admin Dashboard</title>

<style>

body{
    margin:0;
    font-family:Segoe UI;
    background:#f4f6f9;
}

/* LAYOUT */
.layout{
    display:flex;
    min-height:100vh;
}

/* MAIN */
.main{
    flex:1;
}

/* CONTENT */
.content{
    padding:25px;
}

/* MAIN CARDS */
.cards{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:20px;
}

.card{
    background:white;
    padding:25px;
    border-radius:12px;
    text-align:center;
    box-shadow:0 3px 10px rgba(0,0,0,0.1);
    transition:0.3s;
}

.card:hover{
    transform:translateY(-5px);
}

.card h3{
    margin:0;
    color:#444;
}

.card span{
    font-size:30px;
    font-weight:bold;
    color:#007bff;
}

/* ⭐ INFO CARDS (NEW FEATURE) */
.info-cards{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:20px;
    margin-top:25px;
}

.info-card{
    background:white;
    padding:25px;
    border-radius:12px;
    text-align:center;
    box-shadow:0 3px 10px rgba(0,0,0,0.1);
    transition:0.3s;
}

.info-card:hover{
    transform:translateY(-5px);
}

.info-card h3{
    margin:0;
    color:#444;
    font-size:16px;
}

.info-card .value{
    font-size:22px;
    font-weight:bold;
    color:#007bff;
    margin-top:10px;
}

.info-card .sub{
    color:#666;
    margin-top:8px;
    font-size:14px;
}

/* MOBILE */
@media(max-width:900px){
    .cards{
        grid-template-columns:1fr;
    }

    .info-cards{
        grid-template-columns:1fr;
    }
}

</style>

</head>

<body>

<div class="layout">

<?php include('sidebar.php'); ?>

<div class="main">

<?php include('header.php'); ?>

<div class="content">

<?php if($page == 'home'){ ?>

<h2>📊 Admin Dashboard</h2>

<!-- MAIN STATS -->
<div class="cards">

    <div class="card">
        <h3>Total Quizzes</h3>
        <span><?= $quiz_count ?></span>
    </div>

    <div class="card">
        <h3>Total Questions</h3>
        <span><?= $question_count ?></span>
    </div>

    <div class="card">
        <h3>Total Users</h3>
        <span><?= $user_count ?></span>
    </div>

    <div class="card">
        <h3>Total Results</h3>
        <span><?= $result_count ?></span>
    </div>

</div>

<!-- ⭐ NEW INFO CARDS -->
<div class="info-cards">

    <div class="info-card">
        <h3>🔥 Most Active User</h3>
        <div class="value"><?= $active_user['fullname'] ?? 'N/A' ?></div>
        <div class="sub">(<?= $active_user['attempts'] ?? 0 ?> Quiz Attempts)</div>
    </div>

    <div class="info-card">
        <h3>🏆 Highest Score</h3>
        <div class="value"><?= $highest_score['Score'] ?? 0 ?>%</div>
        <div class="sub"><?= $highest_score['Title'] ?? 'N/A' ?></div>
    </div>

    <div class="info-card">
        <h3>📚 Most Attempted Quiz</h3>
        <div class="value"><?= $popular_quiz['Title'] ?? 'N/A' ?></div>
        <div class="sub">(<?= $popular_quiz['attempts'] ?? 0 ?> Attempts)</div>
    </div>

    <div class="info-card">
        <h3>⭐ Top Category</h3>
        <div class="value"><?= $top_category['topic'] ?? 'N/A' ?></div>
        <div class="sub">(<?= $top_category['total'] ?? 0 ?> Questions)</div>
    </div>

</div>

<?php }

elseif($page == 'addquiz'){
    include("content/addquiz.php");
}
elseif($page == 'manage_quiz'){
    include("content/manage_quiz.php");
}
elseif($page == 'add_question'){
    include("content/add_question.php");
}
elseif($page == 'upload_questions'){
    include("content/upload_questions.php");
}
elseif($page == 'manage_question'){
    include("content/manage_question.php");
}
elseif($page == 'user'){
    include("content/user.php");
}
elseif($page == 'result'){
    include("content/result.php");
}
elseif($page == 'analytics'){
    include("content/analytics.php");
}

elseif($page == 'user_analytics'){
    include("Content/user_analytics.php");
}

elseif($page == 'settings'){
    include("Content/settings.php");
}

elseif($page == 'admin_notification'){
    include("Content/admin_notification.php");
}

?>

</div>

</div>

<style>
.ai-fab {
    position: fixed;
    bottom: 25px;
    right: 25px;
    background: linear-gradient(135deg, #22c55e, #16a34a);
    color: white;
    padding: 15px 18px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: bold;
    box-shadow: 0 8px 20px rgba(0,0,0,0.3);
    display: flex;
    align-items: center;
    gap: 8px;
    transition: 0.3s;
    z-index: 9999;
}

.ai-fab:hover {
    transform: scale(1.05);
}
</style>

<a href="content/ai_quiz_generator.php" class="ai-fab">
    🤖 AI Quiz Generator
</a>

</div>

<?php include('../footer.php'); ?>

</body>
</html>