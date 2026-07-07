<?php

/* MAIN LEADERBOARD */

$query = mysqli_query($conn, "
SELECT
users.id,
users.fullname,
users.profile_pic,
users.points,
users.badge,

AVG(
CASE
WHEN result.total_questions > 0
THEN (result.correct_answers/result.total_questions)*100
ELSE 0
END
) AS accuracy,

MAX(result.Date) AS last_activity

FROM users

LEFT JOIN result
ON users.id = result.User_id

GROUP BY users.id

ORDER BY users.points DESC

LIMIT 10
");

/* TOP USERS */

$topQuery = mysqli_query($conn, "
SELECT fullname, profile_pic, points
FROM users
ORDER BY points DESC
LIMIT 3
");

$topUsers = [];

while($top = mysqli_fetch_assoc($topQuery)){
    $topUsers[] = $top;
}

?>

<!DOCTYPE html>
<html>

<head>

<title>Quizify Leaderboard</title>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    background:#081028;
    font-family:Arial, sans-serif;
    color:white;
}

.container{
    width:96%;
    margin:auto;
    padding:18px 10px;
}

/* TITLE */

.title{
    text-align:center;
    margin-bottom:22px;
    font-size:42px;
    font-weight:bold;
}

/* TOP PLAYERS */

.top-players{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:18px;
    margin-bottom:20px;
}

.player-card{
    background:#16213e;
    padding:18px;
    border-radius:20px;
    text-align:center;
    box-shadow:0 0 15px rgba(0,0,0,0.3);
    transition:0.3s;
}

.player-card:hover{
    transform:translateY(-5px);
}

.player-rank{
    font-size:32px;
    margin-bottom:10px;
}

.player-card img{
    width:75px;
    height:75px;
    border-radius:50%;
    object-fit:cover;
    border:3px solid #2563eb;
    margin-bottom:10px;
}

.player-card h3{
    margin-bottom:6px;
    font-size:22px;
}

.player-card p{
    color:#38bdf8;
    font-weight:bold;
    font-size:18px;
}

/* INFO CARDS */

.top-card{
    display:flex;
    gap:18px;
    margin-bottom:22px;
    flex-wrap:wrap;
}

.card{
    flex:1;
    min-width:250px;
    background:#16213e;
    padding:18px;
    border-radius:20px;
    text-align:center;
    box-shadow:0 0 15px rgba(0,0,0,0.3);
}

.card h2{
    margin-bottom:8px;
    font-size:24px;
}

.card p{
    color:#cbd5e1;
    font-size:15px;
}

/* TABLE */

.leaderboard{
    background:#16213e;
    border-radius:20px;
    overflow-x:auto;
    box-shadow:0 0 20px rgba(0,0,0,0.4);
}

table{
    width:100%;
    border-collapse:collapse;
    min-width:850px;
}

th{
    background:#2563eb;
    padding:15px;
    text-align:left;
    font-size:15px;
}

td{
    padding:14px;
    border-bottom:1px solid rgba(255,255,255,0.08);
}

tr:hover{
    background:#22304d;
    transition:0.3s;
}

.profile{
    width:48px;
    height:48px;
    border-radius:50%;
    object-fit:cover;
    border:2px solid #2563eb;
}

.badge{
    padding:7px 14px;
    border-radius:30px;
    font-size:12px;
    font-weight:bold;
}

.Beginner{
    background:#475569;
}

.Learner{
    background:#2563eb;
}

.Advanced{
    background:#7c3aed;
}

.quizmaster{
    background:#f59e0b;
    color:black;
}

.elite{
    background:#22c55e;
    color:black;
}

/* CHARTS */

.charts{
    display:grid;
    grid-template-columns:1fr 1fr 1fr;
    gap:18px;
    margin-top:22px;
}

.chart-box{
    background:#16213e;
    padding:18px;
    border-radius:20px;
    box-shadow:0 0 15px rgba(0,0,0,0.3);
}

.chart-box h2{
    margin-bottom:15px;
    text-align:center;
    font-size:20px;
}

/* MOBILE */

@media(max-width:1000px){

    .charts{
        grid-template-columns:1fr;
    }

    .top-players{
        grid-template-columns:1fr;
    }

}

</style>

</head>

<body>

<div class="container">

<h1 class="title">🏆 Quizify Leaderboard</h1>

<!-- TOP PLAYERS -->

<div class="top-players">

<?php

$icons = ['🥇','🥈','🥉'];
$index = 0;

foreach($topUsers as $user){

$img = !empty($user['profile_pic'])
? '/quiz_system/uploads/'.$user['profile_pic']
: 'https://via.placeholder.com/80';

?>

<div class="player-card">

<div class="player-rank">
<?php echo $icons[$index]; ?>
</div>

<img src="<?php echo $img; ?>">

<h3><?php echo $user['fullname']; ?></h3>

<p><?php echo $user['points']; ?> XP</p>

</div>

<?php

$index++;

}

?>

</div>

<!-- INFO CARDS -->

<div class="top-card">

<div class="card">
<h2>🎁 Rewards</h2>
<p>20,000 XP unlocks Quizify Gift Voucher.</p>
</div>

<div class="card">
<h2>🔥 Competition</h2>
<p>Compete with students and climb rankings.</p>
</div>

<div class="card">
<h2>📈 Analytics</h2>
<p>Track performance using smart charts.</p>
</div>

</div>

<!-- TABLE -->

<div class="leaderboard">

<table>

<tr>
<th>Rank</th>
<th>Profile</th>
<th>Name</th>
<th>XP</th>
<th>Accuracy</th>
<th>Badge</th>
<th>Last Activity</th>
</tr>

<?php

$rank = 1;

while($row = mysqli_fetch_assoc($query)){

$profile = !empty($row['profile_pic'])
? "/quiz_system/uploads/".$row['profile_pic']
: "https://via.placeholder.com/50";

$badgeClass = "Beginner";

if($row['badge'] == 'Learner'){
    $badgeClass = "Learner";
}
elseif($row['badge'] == 'Advanced'){
    $badgeClass = "Advanced";
}
elseif($row['badge'] == 'Quiz Master'){
    $badgeClass = "quizmaster";
}
elseif($row['badge'] == 'Elite Champion'){
    $badgeClass = "elite";
}

?>

<tr>

<td><b>#<?php echo $rank++; ?></b></td>

<td>
<img src="<?php echo $profile; ?>" class="profile">
</td>

<td>
<?php echo htmlspecialchars($row['fullname']); ?>
</td>

<td>
<b><?php echo $row['points']; ?></b>
</td>

<td>
<?php echo round($row['accuracy']); ?>%
</td>

<td>
<span class="badge <?php echo $badgeClass; ?>">
<?php echo $row['badge']; ?>
</span>
</td>

<td>

<?php

if($row['last_activity']){
    echo date("d M Y", strtotime($row['last_activity']));
}else{
    echo "No Activity";
}

?>

</td>

</tr>

<?php } ?>

</table>

</div>

<!-- CHARTS -->

<div class="charts">

<div class="chart-box">
<h2>📊 Top XP</h2>
<canvas id="barChart"></canvas>
</div>

<div class="chart-box">
<h2>📈 Growth</h2>
<canvas id="lineChart"></canvas>
</div>

<div class="chart-box">
<h2>🎯 Performance</h2>
<canvas id="doughnutChart"></canvas>
</div>

</div>

</div>

<?php

$chartQuery = mysqli_query($conn, "
SELECT fullname, points
FROM users
ORDER BY points DESC
LIMIT 5
");

$names = [];
$points = [];

while($data = mysqli_fetch_assoc($chartQuery)){

$names[] = $data['fullname'];
$points[] = $data['points'];

}

?>

<script>

const labels = <?php echo json_encode($names); ?>;
const points = <?php echo json_encode($points); ?>;

/* BAR CHART */

new Chart(document.getElementById('barChart'), {

type: 'bar',

data: {

labels: labels,

datasets: [{
label: 'XP',
data: points,
backgroundColor: '#2563eb',
borderRadius: 10
}]
},

options:{
responsive:true
}

});

/* LINE CHART */

new Chart(document.getElementById('lineChart'), {

type: 'line',

data: {

labels: labels,

datasets: [{
label: 'Growth',
data: points,
borderColor: '#22c55e',
backgroundColor:'rgba(34,197,94,0.2)',
fill:true,
tension:0.4
}]
},

options:{
responsive:true
}

});

/* DOUGHNUT CHART */

new Chart(document.getElementById('doughnutChart'), {

type: 'doughnut',

data: {

labels: labels,

datasets: [{
data: points,
backgroundColor:[
'#2563eb',
'#22c55e',
'#f59e0b',
'#ef4444',
'#7c3aed'
]
}]
},

options:{
responsive:true
}

});

</script>

</body>
</html>