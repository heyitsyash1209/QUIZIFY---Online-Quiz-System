<?php
include($_SERVER['DOCUMENT_ROOT'].'/quiz_system/config.php');

$query = mysqli_query($conn, "
SELECT r.*, u.Username, q.Title
FROM result r
LEFT JOIN users u ON r.User_id = u.id
LEFT JOIN quizzes q ON r.Quiz_id = q.ID
ORDER BY r.Id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Results</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    font-family:Segoe UI;
    background:#f5f7fb;
}

.container{
    max-width:1200px;
}

/* SEARCH */
.search-box{
    margin:20px 0;
}

/* CARD GRID */
.result-card{
    background:white;
    border-radius:14px;
    padding:15px;
    box-shadow:0 4px 12px rgba(0,0,0,0.06);
    transition:0.2s;
    height:100%;
}

.result-card:hover{
    transform:translateY(-3px);
}

/* TEXT */
.user{
    font-weight:600;
    color:#2563eb;
    font-size:15px;
}

.quiz{
    color:#475569;
    font-size:13px;
}

.level{
    font-size:13px;
    color:#64748b;
}

.score{
    color:#16a34a;
    font-weight:600;
    margin-top:8px;
}

.percent{
    color:#0ea5e9;
    font-weight:500;
}

.date{
    font-size:12px;
    color:#94a3b8;
}

/* BADGE */
.badge{
    font-size:11px;
}

</style>

</head>

<body>

<div class="container">

<h3 class="text-center mt-3">📊 Quiz Results Dashboard</h3>

<!-- SEARCH -->
<input type="text" id="search" class="form-control search-box" placeholder="Search by user or quiz...">

<div class="row" id="resultContainer">

<?php while($row = mysqli_fetch_assoc($query)){

$quiz_id = $row['Quiz_id'];
$score = $row['Score'];
$level = $row['Level'];

$total_query = mysqli_query($conn, "
SELECT COUNT(*) as total
FROM quiz_questions qz
JOIN questions q ON qz.Question_id = q.Id
WHERE qz.Quiz_id = '$quiz_id'
AND q.Difficulty = '$level'
");

$total_row = mysqli_fetch_assoc($total_query);
$total = $total_row['total'];

$percentage = ($total > 0) ? ($score / $total) * 100 : 0;

?>

<div class="col-md-4 mb-3 result-item">

<div class="result-card">

<div class="user">👤 <?= $row['Username'] ?></div>
<div class="quiz">📘 <?= $row['Title'] ?></div>

<div class="level">Level: <b><?= $level ?></b></div>

<div class="score">Score: <?= $score ?> / <?= $total ?></div>

<div class="percent">📊 <?= round($percentage,2) ?>%</div>

<div class="date">📅 <?= $row['Date'] ?></div>

</div>

</div>

<?php } ?>

</div>

</div>

<script>

/* SEARCH FILTER */
document.getElementById("search").addEventListener("keyup", function(){

let value = this.value.toLowerCase();
let items = document.querySelectorAll(".result-item");

items.forEach(item => {
    let text = item.innerText.toLowerCase();
    item.style.display = text.includes(value) ? "block" : "none";
});

});

</script>

</body>
</html>