<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include($_SERVER['DOCUMENT_ROOT'].'/quiz_system/config.php');

/* ================= BASIC STATS ================= */

$totalQuiz = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM quizzes"))['c'] ?? 0;

$totalUsers = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM users"))['c'] ?? 0;

$totalAttempts = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM result"))['c'] ?? 0;

$avgScore = mysqli_fetch_assoc(mysqli_query($conn,"SELECT AVG(Score) as a FROM result"))['a'] ?? 0;

/* ================= PASS FAIL ================= */

$pass = 0;
$fail = 0;

$q = mysqli_query($conn,"
SELECT r.Score, q.passing_percentage 
FROM result r
JOIN quizzes q ON q.ID = r.Quiz_id
");

while($row = mysqli_fetch_assoc($q)){
    if($row['Score'] >= $row['passing_percentage']){
        $pass++;
    } else {
        $fail++;
    }
}

/* ================= CHART DATA ================= */

$typeLabels = [];
$typeData = [];

$t = mysqli_query($conn,"
SELECT quiz_type, COUNT(*) as total 
FROM quizzes 
GROUP BY quiz_type
");

while($r = mysqli_fetch_assoc($t)){
    $typeLabels[] = $r['quiz_type'];
    $typeData[] = $r['total'];
}

$quizLabels = [];
$quizData = [];

$top = mysqli_query($conn,"
SELECT q.Title, COUNT(r.Id) as total
FROM result r
JOIN quizzes q ON q.ID = r.Quiz_id
GROUP BY r.Quiz_id
ORDER BY total DESC
LIMIT 5
");

while($r = mysqli_fetch_assoc($top)){
    $quizLabels[] = $r['Title'];
    $quizData[] = $r['total'];
}

/* SCORE RANGE */

$ranges = [
"0-20"=>0,
"21-40"=>0,
"41-60"=>0,
"61-80"=>0,
"81-100"=>0
];

$res = mysqli_query($conn,"SELECT Score FROM result");

while($r = mysqli_fetch_assoc($res)){
    $s = $r['Score'];

    if($s <= 20) $ranges["0-20"]++;
    elseif($s <= 40) $ranges["21-40"]++;
    elseif($s <= 60) $ranges["41-60"]++;
    elseif($s <= 80) $ranges["61-80"]++;
    else $ranges["81-100"]++;
}

/* AI INSIGHT */

$avg = round($avgScore);

if($avg < 40){
    $insight = "⚠ Students need basic revision and more practice.";
}
elseif($avg < 70){
    $insight = "📊 Average performance. Encourage consistency.";
}
else{
    $insight = "🔥 Excellent performance! System is healthy.";
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Analytics Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>

/* BACKGROUND (LIGHT GRADIENT PROFESSIONAL) */
body{
    font-family:Segoe UI;
    background: linear-gradient(135deg,#eef2ff,#f0f9ff,#ecfeff);
}

/* HEADER */
h2{
    font-weight:700;
    color:#1e3a8a;
}

/* STATS CARDS (COLORFUL) */
.card{
    border:none;
    border-radius:16px;
    padding:16px;
    box-shadow:0 6px 18px rgba(0,0,0,0.08);
    transition:0.2s;
}

.card:hover{
    transform:translateY(-3px);
}

/* COLORS FOR CARDS */
.card:nth-child(1){background:#dbeafe;}
.card:nth-child(2){background:#dcfce7;}
.card:nth-child(3){background:#fef9c3;}
.card:nth-child(4){background:#ffe4e6;}

/* TEXT */
h6{
    font-size:12px;
    color:#334155;
}

h3{
    font-size:22px;
    font-weight:700;
    color:#0f172a;
}

/* CHART BOX */
.chart-box{
    background:white;
    border-radius:16px;
    padding:15px;
    box-shadow:0 6px 18px rgba(0,0,0,0.06);
    height:260px;
}

/* INSIGHT BOX */
.insight{
    background:linear-gradient(135deg,#60a5fa,#a78bfa);
    color:white;
    padding:18px;
    border-radius:16px;
    box-shadow:0 6px 18px rgba(0,0,0,0.1);
}

/* PASS FAIL */
.pass-box{
    background:#ecfdf5;
    border-radius:16px;
    padding:18px;
    box-shadow:0 6px 18px rgba(0,0,0,0.06);
    text-align:center;
}

/* RESPONSIVE */
@media(max-width:768px){
    .chart-box{
        height:220px;
    }
}

</style>

</head>

<body>

<div class="container mt-4">

<h2>📊 Analytics Dashboard</h2>

<!-- STATS -->
<div class="row g-3 mt-2">

<div class="col-md-3 col-6">
<div class="card">
<h6>Quizzes</h6>
<h3><?= $totalQuiz ?></h3>
</div>
</div>

<div class="col-md-3 col-6">
<div class="card">
<h6>Users</h6>
<h3><?= $totalUsers ?></h3>
</div>
</div>

<div class="col-md-3 col-6">
<div class="card">
<h6>Attempts</h6>
<h3><?= $totalAttempts ?></h3>
</div>
</div>

<div class="col-md-3 col-6">
<div class="card">
<h6>Avg Score</h6>
<h3><?= round($avgScore,1) ?>%</h3>
</div>
</div>

</div>

<!-- CHARTS -->
<div class="row mt-4 g-3">

<div class="col-md-6">
<div class="chart-box">
<canvas id="pieChart"></canvas>
</div>
</div>

<div class="col-md-6">
<div class="chart-box">
<canvas id="barChart"></canvas>
</div>
</div>

</div>

<!-- LINE -->
<div class="chart-box mt-3">
<canvas id="lineChart"></canvas>
</div>

<!-- PASS + INSIGHT -->
<div class="row mt-3 g-3">

<div class="col-md-4">
<div class="pass-box">
<h6>Pass / Fail</h6>
<h3>✅ <?= $pass ?> | ❌ <?= $fail ?></h3>
</div>
</div>

<div class="col-md-8">
<div class="insight">
🤖 <b>AI Insight</b><br><br>
<?= $insight ?>
</div>
</div>

</div>

</div>

<script>

/* PIE */
new Chart(document.getElementById("pieChart"),{
type:"pie",
data:{
labels:<?= json_encode($typeLabels) ?>,
datasets:[{
data:<?= json_encode($typeData) ?>,
backgroundColor:["#3b82f6","#22c55e","#facc15","#f97316","#ef4444"]
}]
},
options:{responsive:true, maintainAspectRatio:false}
});

/* BAR */
new Chart(document.getElementById("barChart"),{
type:"bar",
data:{
labels:<?= json_encode($quizLabels) ?>,
datasets:[{
label:"Top Quizzes",
data:<?= json_encode($quizData) ?>,
backgroundColor:"#6366f1"
}]
},
options:{responsive:true, maintainAspectRatio:false}
});

/* LINE */
new Chart(document.getElementById("lineChart"),{
type:"line",
data:{
labels:<?= json_encode(array_keys($ranges)) ?>,
datasets:[{
label:"Score Distribution",
data:<?= json_encode(array_values($ranges)) ?>,
borderColor:"#10b981",
backgroundColor:"rgba(16,185,129,0.2)",
fill:true,
tension:0.4
}]
},
options:{responsive:true, maintainAspectRatio:false}
});

</script>

</body>
</html>