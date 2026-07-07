<?php
include($_SERVER['DOCUMENT_ROOT'].'/quiz_system/config.php');

$user_id = $_SESSION['user_id'];

/* =====================================
   TOTAL QUIZ
===================================== */

$total_quiz_query = mysqli_query($conn,"
SELECT COUNT(*) as total
FROM result
WHERE User_id='$user_id'
");

$total_quiz = mysqli_fetch_assoc($total_quiz_query)['total'];


/* =====================================
   HIGHEST SCORE
===================================== */

$highest_query = mysqli_query($conn,"
SELECT MAX(Score) as highest
FROM result
WHERE User_id='$user_id'
");

$highest_score = mysqli_fetch_assoc($highest_query)['highest'];


/* =====================================
   AVERAGE SCORE
===================================== */

$avg_query = mysqli_query($conn,"
SELECT AVG(Score) as average_score
FROM result
WHERE User_id='$user_id'
");

$average_score = round(mysqli_fetch_assoc($avg_query)['average_score'],2);


/* =====================================
   TOTAL CORRECT & QUESTIONS
===================================== */

$total_correct = 0;
$total_questions = 0;


/* =====================================
   QUIZ HISTORY
===================================== */

$result_query = $conn->prepare("
    SELECT r.*, q.Title
    FROM result r
    JOIN quizzes q ON r.Quiz_id = q.ID
    WHERE r.User_id = ?
    ORDER BY r.Id DESC
    LIMIT 5
");

$result_query->bind_param("i", $user_id);
$result_query->execute();

$result = $result_query->get_result();


/* =====================================
   CHART DATA
===================================== */

$chart_labels = [];
$chart_scores = [];


/* =====================================
   LEVEL PERFORMANCE
===================================== */

$easy_total = 0;
$medium_total = 0;
$hard_total = 0;

$easy_count = 0;
$medium_count = 0;
$hard_count = 0;


$temp_query = $conn->prepare("
    SELECT *
    FROM result
    WHERE User_id = ?
");

$temp_query->bind_param("i", $user_id);
$temp_query->execute();
$temp_result = $temp_query->get_result();

while($data = $temp_result->fetch_assoc()){

    $level = strtolower($data['Level']);
    $score = $data['Score'];

    $chart_labels[] = "Quiz ".$data['Quiz_id'];
    $chart_scores[] = $score;

    $total_correct += $score;

    $quiz_id = $data['Quiz_id'];

    $question_query = mysqli_query($conn,"
        SELECT COUNT(*) as total
        FROM quiz_questions
        WHERE Quiz_id='$quiz_id'
    ");

    $question_total = mysqli_fetch_assoc($question_query)['total'];

    $total_questions += $question_total;

    if($level == 'easy'){
        $easy_total += $score;
        $easy_count++;
    }
    elseif($level == 'medium'){
        $medium_total += $score;
        $medium_count++;
    }
    elseif($level == 'hard'){
        $hard_total += $score;
        $hard_count++;
    }
}


/* =====================================
   ACCURACY
===================================== */

if($total_questions > 0){
    $accuracy = round(($total_correct / $total_questions) * 100,2);
}else{
    $accuracy = 0;
}


/* =====================================
   LEVEL PERCENTAGES
===================================== */

$easy_avg = ($easy_count > 0)
? round($easy_total / $easy_count)
: 0;

$medium_avg = ($medium_count > 0)
? round($medium_total / $medium_count)
: 0;

$hard_avg = ($hard_count > 0)
? round($hard_total / $hard_count)
: 0;

?>

<!DOCTYPE html>
<html>
<head>

<title>Performance Dashboard</title>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:'Segoe UI',sans-serif;
    background:#081028;
    color:white;
}

.container{
    width:94%;
    margin:auto;
    padding:12px;
}

.top-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:30px;
}

.top-header h1{
    font-size:30px;
    color:white;
}

.top-header p{
    color:#cbd5e1;
    margin-top:5px;
}

.profile-box{
    background:#16213e;
    padding:12px 18px;
    border-radius:12px;
    box-shadow:0 2px 10px rgba(0,0,0,0.25);
    color:white;
}

.cards{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:20px;
    margin-bottom:30px;
}

.card{
    padding:18px;
    border-radius:18px;
    color:white;
    position:relative;
    overflow:hidden;
    transition:0.3s;
    box-shadow:0 0 15px rgba(0,0,0,0.3);
}

.card:hover{
    transform:translateY(-6px);
}

.card h3{
    font-size:18px;
    font-weight:500;
}

.card h1{
    margin-top:18px;
    font-size:28px;
}

.card i{
    position:absolute;
    right:20px;
    top:20px;
    font-size:35px;
    opacity:0.3;
}

.blue{
    background:linear-gradient(135deg,#2563eb,#3b82f6);
}

.purple{
    background:linear-gradient(135deg,#7c3aed,#9333ea);
}

.green{
    background:linear-gradient(135deg,#059669,#10b981);
}

.orange{
    background:linear-gradient(135deg,#ea580c,#f97316);
}

.grid-layout{
    display:grid;
    grid-template-columns:2fr 1fr;
    gap:20px;
    margin-bottom:30px;
}

.box{
    background:#16213e;
    border-radius:18px;
    padding:18px;
    box-shadow:0 0 15px rgba(0,0,0,0.3);
}

.box-title{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
}

.box-title h2{
    font-size:22px;
    color:white;
}

.level-item{
    margin-bottom:25px;
}

.level-head{
    display:flex;
    justify-content:space-between;
    margin-bottom:8px;
}

.progress-bar{
    width:100%;
    height:14px;
    background:#334155;
    border-radius:10px;
    overflow:hidden;
}

.progress-fill{
    height:100%;
    border-radius:10px;
}

.easy-fill{
    background:#22c55e;
}

.medium-fill{
    background:#f59e0b;
}

.hard-fill{
    background:#ef4444;
}

.second-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
    margin-bottom:30px;
}

.recent-card{
    background:#1e293b;
    border-left:5px solid #6366f1;
    padding:12px;
    border-radius:12px;
    margin-bottom:15px;
}

.recent-card h3{
    color:white;
    margin-bottom:8px;
}

.recent-card p{
    color:#cbd5e1;
    margin:5px 0;
}

.badge{
    display:inline-block;
    padding:6px 12px;
    border-radius:20px;
    font-size:12px;
    color:white;
    margin-top:8px;
}

.easy-badge{
    background:#22c55e;
}

.medium-badge{
    background:#f59e0b;
}

.hard-badge{
    background:#ef4444;
}

canvas{
    max-height:220px;
}

@media(max-width:900px){

    .grid-layout,
    .second-grid{
        grid-template-columns:1fr;
    }

    .top-header{
        flex-direction:column;
        gap:15px;
        align-items:flex-start;
    }
}

</style>

</head>

<body>

<div class="container">

<div class="top-header">

<div>
<h1>My Performance</h1>
<p>Track your quiz progress and improve daily 🚀</p>
</div>

<div class="profile-box">
👤 <?php echo $_SESSION['user_name']; ?>
</div>

</div>

<div class="cards">

<div class="card blue">
<i class="fa-solid fa-book"></i>
<h3>Total Quiz</h3>
<h1><?php echo $total_quiz; ?></h1>
</div>

<div class="card purple">
<i class="fa-solid fa-trophy"></i>
<h3>Highest Score</h3>
<h1><?php echo $highest_score; ?></h1>
</div>

<div class="card green">
<i class="fa-solid fa-chart-line"></i>
<h3>Accuracy</h3>
<h1><?php echo $accuracy; ?>%</h1>
</div>

<div class="card orange">
<i class="fa-solid fa-ranking-star"></i>
<h3>Rank</h3>
<h1>#5</h1>
</div>

</div>

<div class="grid-layout">

<div class="box">

<div class="box-title">
<h2>📈 Performance Trend</h2>
</div>

<canvas id="lineChart"></canvas>

</div>

<div class="box">

<div class="box-title">
<h2>📊 Level Analysis</h2>
</div>

<div class="level-item">

<div class="level-head">
<span>Easy</span>
<span><?php echo $easy_avg; ?>%</span>
</div>

<div class="progress-bar">
<div class="progress-fill easy-fill"
style="width:<?php echo $easy_avg; ?>%"></div>
</div>

</div>

<div class="level-item">

<div class="level-head">
<span>Medium</span>
<span><?php echo $medium_avg; ?>%</span>
</div>

<div class="progress-bar">
<div class="progress-fill medium-fill"
style="width:<?php echo $medium_avg; ?>%"></div>
</div>

</div>

<div class="level-item">

<div class="level-head">
<span>Hard</span>
<span><?php echo $hard_avg; ?>%</span>
</div>

<div class="progress-bar">
<div class="progress-fill hard-fill"
style="width:<?php echo $hard_avg; ?>%"></div>
</div>

</div>

</div>

</div>

<div class="second-grid">

<div class="box">

<div class="box-title">
<h2>🥧 Correct vs Wrong</h2>
</div>

<canvas id="pieChart"></canvas>

</div>

<div class="box">

<div class="box-title">
<h2>📋 Recent Attempts</h2>
</div>

<?php

$result_query2 = $conn->prepare("
SELECT r.*, q.Title
FROM result r
JOIN quizzes q ON r.Quiz_id = q.ID
WHERE r.User_id = ?
ORDER BY r.Id DESC
LIMIT 5
");

$result_query2->bind_param("i", $user_id);
$result_query2->execute();

$recent = $result_query2->get_result();

while($row = $recent->fetch_assoc()){

$level = strtolower($row['Level']);

if($level == 'easy'){
    $badge = 'easy-badge';
}
elseif($level == 'medium'){
    $badge = 'medium-badge';
}
else{
    $badge = 'hard-badge';
}

?>

<div class="recent-card">

<h3><?php echo $row['Title']; ?></h3>

<p>Score : <b><?php echo $row['Score']; ?></b></p>

<p>Date : <?php echo $row['Date']; ?></p>

<div class="badge <?php echo $badge; ?>">
<?php echo ucfirst($level); ?> Level
</div>

</div>

<?php } ?>

</div>

</div>

</div>

<script>

new Chart(document.getElementById('lineChart'), {

type: 'line',

data: {

labels: <?php echo json_encode($chart_labels); ?>,

datasets: [{

label: 'Quiz Score',

data: <?php echo json_encode($chart_scores); ?>,

borderColor: '#6366f1',
backgroundColor: 'rgba(99,102,241,0.2)',
fill:true,
tension:0.4,
borderWidth:3,
pointRadius:5

}]
}
});

new Chart(document.getElementById('pieChart'), {

type: 'doughnut',

data: {

labels:['Correct','Wrong'],

datasets:[{

data:[
<?php echo $total_correct; ?>,
<?php echo max(0,$total_questions - $total_correct); ?>
],

backgroundColor:[
'#22c55e',
'#ef4444'
]
}]
}
});

</script>

</body>
</html>