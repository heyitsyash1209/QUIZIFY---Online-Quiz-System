<?php
include($_SERVER['DOCUMENT_ROOT'].'/quiz_system/config.php');

/* ================= DELETE QUIZ ================= */
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM quizzes WHERE ID=$id");
}

/* ================= TOGGLE TRENDING ================= */
if(isset($_GET['toggle'])){
    $id = $_GET['toggle'];

    // current value nikal
    $q = mysqli_query($conn, "SELECT is_trending FROM quizzes WHERE ID=$id");
    $row = mysqli_fetch_assoc($q);

    $new = ($row['is_trending'] == 1) ? 0 : 1;

    mysqli_query($conn, "UPDATE quizzes SET is_trending=$new WHERE ID=$id");
}

/* ================= FETCH QUIZ ================= */
$result = mysqli_query($conn, "SELECT * FROM quizzes");
?>

<h2 style="margin-bottom:15px;">🎯 Manage Quizzes</h2>

<div style="background:#f8fafc; padding:20px; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.05);">

<table style="width:100%; border-collapse:collapse; text-align:center;">

<tr style="background:#e2e8f0;">
<th style="padding:12px;">ID</th>
<th>Title</th>
<th>Time</th>
<th>Trending</th>
<th>Action</th>
</tr>

<?php while($row = mysqli_fetch_assoc($result)){ ?>

<tr style="border-bottom:1px solid #ddd;">

<td style="padding:10px;"><?php echo $row['id']; ?></td>

<td><?php echo $row['title']; ?></td>

<td><?php echo $row['Time-limit']; ?> min</td>

<!-- 🔥 TRENDING BUTTON -->
<td>
<a href="dashboard.php?page=manage_quiz&toggle=<?php echo $row['id']; ?>"
style="
padding:6px 12px;
border-radius:20px;
text-decoration:none;
font-size:13px;
background:<?php echo ($row['is_trending']==1) ? '#22c55e' : '#cbd5f5'; ?>;
color:white;
">
<?php echo ($row['is_trending']==1) ? 'ON' : 'OFF'; ?>
</a>
</td>

<!-- DELETE -->
<td>
<a href="dashboard.php?page=manage_quiz&delete=<?php echo $row['id']; ?>" 
style="
background:#ef4444; 
color:white; 
padding:6px 12px; 
border-radius:6px; 
text-decoration:none;
">
Delete
</a>
</td>

</tr>

<?php } ?>

</table>

</div>