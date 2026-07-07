<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include($_SERVER['DOCUMENT_ROOT'].'/quiz_system/config.php');

/* ================= TOP STUDENTS ================= */

$top = mysqli_query($conn,"
SELECT 
u.id,
u.fullname,
COUNT(r.Id) as attempts,
AVG(r.Score) as avg_score
FROM result r
JOIN users u ON u.id = r.User_id
GROUP BY r.User_id
ORDER BY avg_score DESC
LIMIT 10
");

/* ================= USER DETAILS ================= */

$user = null;
$history = [];
$subjectData = [];

if(isset($_GET['user_id'])){

$uid = intval($_GET['user_id']);

$user = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT * FROM users WHERE id=$uid
"));

$res = mysqli_query($conn,"
SELECT q.Title, r.Score, r.Date
FROM result r
JOIN quizzes q ON q.ID = r.Quiz_id
WHERE r.User_id=$uid
ORDER BY r.Id DESC
");

while($r = mysqli_fetch_assoc($res)){
    $history[] = $r;

    $subject = $r['Title'];
    if(!isset($subjectData[$subject])){
        $subjectData[$subject] = 0;
    }
    $subjectData[$subject] += $r['Score'];
}
}

/* ================= BADGE ================= */

function badge($s){
    if($s >= 80) return "success";
    if($s >= 60) return "primary";
    if($s >= 40) return "warning";
    return "danger";
}

/* ================= AVG ================= */

$avg = 0;
foreach($history as $h){
    $avg += $h['Score'];
}
$avg = count($history) ? $avg / count($history) : 0;

/* ================= TREND ================= */

$trend = "Stable";
if(count($history) >= 2){
    $first = end($history)['Score'];
    $last = $history[0]['Score'];

    if($last > $first) $trend = "Improving 📈";
    elseif($last < $first) $trend = "Declining 📉";
}

/* ================= WEAK TOPICS ================= */

$weakTopics = [];
foreach($subjectData as $t => $s){
    if($s / max(1,count($history)) < 50){
        $weakTopics[] = $t;
    }
}

/* ================= AI RANK ================= */

if($avg >= 75){
    $rank = "🏆 TOP PERFORMER";
    $rankColor = "success";
}
elseif($avg >= 50){
    $rank = "👍 AVERAGE PERFORMER";
    $rankColor = "primary";
}
else{
    $rank = "⚠ Growth Needed";
    $rankColor = "danger";
}

/* ================= AI PREDICTION ================= */

$trendScore = 0;
$predictedScore = round($avg);

if(count($history) >= 2){
    $first = end($history)['Score'];
    $last = $history[0]['Score'];

    $trendScore = ($last - $first);

    $predictedScore = min(100, max(0, round($avg + ($trendScore * 0.5))));
}

/* ================= AI ALERT ================= */

$alert = "";

if($avg < 40){
    $alert = "🔴 Critical Alert: Student performance is very weak. Immediate improvement needed.";
}
elseif($avg < 60){
    $alert = "🟠 Warning: Performance below average. Focus required.";
}
elseif($trendScore < 0){
    $alert = "⚠ Declining Trend Detected. Performance is dropping.";
}
else{
    $alert = "🟢 Stable Performance. Keep practicing!";
}
?>

<!DOCTYPE html>
<html>
<head>

<title>User Analytics</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>

body{
    background:#eef2ff;
    font-family:Segoe UI;
    color:#0f172a;
}

.card{
    background:white;
    border:none;
    border-radius:18px;
    padding:18px;
    box-shadow:0 6px 20px rgba(0,0,0,0.06);
}

h3{
    font-weight:700;
    color:#1d4ed8;
}

.small{
    font-size:12px;
    color:#64748b;
}

.chart-box{
    height:300px;
    padding:10px;
}

.badge{
    font-size:11px;
}

</style>

</head>

<body>

<div class="container mt-3">

<h3>📊 AI Student Analytics Dashboard</h3>

<!-- TOP STUDENTS -->
<div class="card mt-3">

<h6 class="small">🏆 Top Performers</h6>

<table class="table table-hover mt-2">
<tr>
<th>Name</th>
<th>Attempts</th>
<th>Avg Score</th>
<th>Badge</th>
<th></th>
</tr>

<?php while($r = mysqli_fetch_assoc($top)){ ?>
<tr>
<td><?= $r['fullname'] ?></td>
<td><?= $r['attempts'] ?></td>
<td><?= round($r['avg_score'],1) ?>%</td>
<td>
<span class="badge bg-<?= badge($r['avg_score']) ?>">
<?= round($r['avg_score']) ?>%
</span>
</td>
<td>
<a class="btn btn-sm btn-primary"
href="dashboard.php?page=user_analytics&user_id=<?= $r['id'] ?>">
View
</a>
</td>
</tr>
<?php } ?>

</table>
</div>

<?php if($user){ ?>

<!-- USER CARD -->
<div class="row mt-3">

<div class="col-md-4">
<div class="card">

<h5><?= $user['fullname'] ?></h5>

<p class="small">Performance Overview</p>

<h2><?= round($avg,1) ?>%</h2>

<span class="badge bg-<?= badge($avg) ?>">
<?= ($avg >= 60 ? "PASS" : "FAIL") ?>
</span>

<hr>

<p class="small">📊 Trend: <b><?= $trend ?></b></p>
<p class="small">Attempts: <?= count($history) ?></p>

<hr>

<p><b>🏆 Rank:</b> 
<span class="badge bg-<?= $rankColor ?>"><?= $rank ?></span>
</p>

<p><b>🔮 Prediction:</b> 
<span class="badge bg-info"><?= $predictedScore ?>%</span>
</p>

<p class="small text-danger"><?= $alert ?></p>

</div>
</div>

<!-- CHART -->
<div class="col-md-8">
<div class="card chart-box">
<canvas id="trendChart"></canvas>
</div>
</div>

</div>

<!-- SUBJECT CHART -->
<div class="card mt-3 chart-box">
<h6 class="small">📊 Subject Performance</h6>
<canvas id="subjectChart"></canvas>
</div>

<!-- WEAK TOPICS -->
<div class="card mt-3">
<h6 class="small">📉 Weak Topics</h6>

<?php if(count($weakTopics) > 0){ ?>
<p class="text-danger">
<?= implode(", ", $weakTopics) ?>
</p>
<?php } else { ?>
<p class="text-success">All Strong Subjects</p>
<?php } ?>

</div>

<!-- HISTORY -->
<div class="card mt-3">

<h6 class="small">📚 History</h6>

<table class="table table-hover">
<tr>
<th>Quiz</th>
<th>Score</th>
<th>Date</th>
<th>Status</th>
</tr>

<?php foreach($history as $h){ ?>
<tr>
<td><?= $h['Title'] ?></td>
<td><?= $h['Score'] ?>%</td>
<td><?= $h['Date'] ?></td>
<td>
<span class="badge bg-<?= badge($h['Score']) ?>">
<?= ($h['Score'] >= 60 ? "PASS" : "FAIL") ?>
</span>
</td>
</tr>
<?php } ?>

</table>

</div>

<?php } ?>

</div>

<script>

<?php if($user){ ?>

new Chart(document.getElementById("trendChart"),{
type:"line",
data:{
labels:<?= json_encode(array_column($history,'Title')) ?>,
datasets:[{
label:"Score Trend",
data:<?= json_encode(array_column($history,'Score')) ?>,
borderColor:"#2563eb",
backgroundColor:"rgba(37,99,235,0.15)",
fill:true,
tension:0.4,
pointBackgroundColor:"#60a5fa"
}]
},
options:{
responsive:true,
maintainAspectRatio:false,
scales:{y:{beginAtZero:true,max:100}}
}
});

new Chart(document.getElementById("subjectChart"),{
type:"bar",
data:{
labels:<?= json_encode(array_keys($subjectData)) ?>,
datasets:[{
label:"Subject Score",
data:<?= json_encode(array_values($subjectData)) ?>,
backgroundColor:["#60a5fa","#34d399","#fbbf24","#f87171","#a78bfa"],
borderRadius:8
}]
},
options:{
responsive:true,
maintainAspectRatio:false,
scales:{y:{beginAtZero:true,max:500}}
}
});

<?php } ?>

</script>

</body>
</html>