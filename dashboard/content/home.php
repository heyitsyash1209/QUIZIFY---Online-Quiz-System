<?php
include($_SERVER['DOCUMENT_ROOT'].'/quiz_system/config.php');

$user_id = $_SESSION['user_id'] ?? 0;

$total_quiz = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM quizzes"))['total'];

$total_attempts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM result WHERE User_id='$user_id'"))['total'];

$avg_score = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(Score) as avg FROM result WHERE User_id='$user_id'"))['avg'];

$last_score = mysqli_fetch_assoc(mysqli_query($conn, "SELECT Score FROM result WHERE User_id='$user_id' ORDER BY Id DESC LIMIT 1"))['Score'] ?? 0;
?>

<style>
.cards{
display:grid;
grid-template-columns:repeat(4,1fr);
gap:15px;
}
.card{
background:white;
padding:20px;
border-radius:10px;
text-align:center;
box-shadow:0 2px 10px rgba(0,0,0,0.1);
}
</style>

<div class="cards">

<div class="card"><h3>Total Quiz</h3><h2><?php echo $total_quiz; ?></h2></div>
<div class="card"><h3>Attempted</h3><h2><?php echo $total_attempts; ?></h2></div>
<div class="card"><h3>Average</h3><h2><?php echo round($avg_score,2) ?? 0; ?></h2></div>
<div class="card"><h3>Last Score</h3><h2><?php echo $last_score; ?></h2></div>

</div>