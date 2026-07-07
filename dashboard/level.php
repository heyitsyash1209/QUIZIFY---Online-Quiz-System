<?php
include($_SERVER['DOCUMENT_ROOT'].'/quiz_system/config.php');

$quiz_id = intval($_GET['quiz_id'] ?? 0);

// fetch quiz
$quiz = mysqli_fetch_assoc(mysqli_query($conn, "
SELECT * FROM quizzes WHERE ID='$quiz_id'
"));
?>

<!DOCTYPE html>
<html>
<head>
<title>Select Level</title>

<style>
body{
    font-family: Arial;
    background: linear-gradient(135deg, #667eea, #764ba2);
    margin: 0;
    padding: 0;
    color: white;
    text-align: center;
}

.container{
    margin-top: 80px;
}

.level-box{
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-top: 50px;
    flex-wrap: wrap;
}

.card{
    background: white;
    color: black;
    width: 200px;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    text-decoration: none;
    transition: 0.3s;
}

.card:hover{
    transform: translateY(-8px);
}

.easy{ border-top: 5px solid #28a745; }
.medium{ border-top: 5px solid #ffc107; }
.hard{ border-top: 5px solid #dc3545; }

h1{ margin-top: 40px; }
</style>

</head>

<body>

<div class="container">

<h1><?php echo $quiz['Title'] ?? "Quiz"; ?></h1>
<p>Select Difficulty Level</p>

<div class="level-box">

    <!-- EASY -->
    <a class="card easy"
       href="/quiz_system/dashboard/content/quiz.php?quiz_id=<?php echo $quiz_id; ?>&level=Easy">

        <h2>Easy</h2>
        <p>Basic Questions</p>
    </a>

    <!-- MODERATE -->
    <a class="card medium"
       href="/quiz_system/dashboard/content/quiz.php?quiz_id=<?php echo $quiz_id; ?>&level=Moderate">

        <h2>Moderate</h2>
        <p>Medium Questions</p>
    </a>

    <!-- ADVANCED -->
    <a class="card hard"
       href="/quiz_system/dashboard/content/quiz.php?quiz_id=<?php echo $quiz_id; ?>&level=Advanced">

        <h2>Advanced</h2>
        <p>Hard Questions</p>
    </a>

</div>

</div>

</body>
</html>