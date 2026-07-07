<?php
include($_SERVER['DOCUMENT_ROOT'].'/quiz_system/config.php');

$sql = "SELECT * FROM quizzes
        WHERE quiz_type IN ('free_certificate','paid_certificate')
        ORDER BY ID DESC";

$result = mysqli_query($conn, $sql);
?>

<style>
.quiz-container{
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(300px,1fr));
    gap:20px;
}

.quiz-card{
    background:white;
    border-radius:12px;
    padding:20px;
    box-shadow:0 2px 10px rgba(0,0,0,0.1);
}

.badge{
    display:inline-block;
    padding:5px 12px;
    border-radius:20px;
    color:white;
    font-size:12px;
    margin-bottom:10px;
}

.free{
    background:green;
}

.paid{
    background:orange;
}

.quiz-title{
    font-size:20px;
    font-weight:bold;
    margin-bottom:10px;
}

.quiz-info{
    margin:8px 0;
}

.btn{
    display:inline-block;
    text-decoration:none;
    padding:10px 15px;
    border-radius:6px;
    color:white;
    margin-top:10px;
}

.btn-start{
    background:#28a745;
}

.btn-register{
    background:#007bff;
}
</style>

<h2>Available Quizzes</h2>

<div class="quiz-container">

<?php while($row = mysqli_fetch_assoc($result)) { ?>

<div class="quiz-card">

<?php if($row['quiz_type'] == 'free_certificate') { ?>
    <span class="badge free">FREE CERTIFICATION</span>
<?php } else { ?>
    <span class="badge paid">
        PAID ₹<?php echo $row['price']; ?>
    </span>
<?php } ?>

<div class="quiz-title">
    <?php echo htmlspecialchars($row['Title']); ?>
</div>

<div class="quiz-info">
    ⏱ Time Limit:
    <?php echo $row['Time-limit']; ?> Minutes
</div>

<div class="quiz-info">
    🎯 Passing:
    <?php echo $row['passing_percentage']; ?>%
</div>

<div class="quiz-info">
    📜 Certificate:
    <?php echo ($row['certificate_enabled']) ? 'Yes' : 'No'; ?>
</div>

<?php if($row['quiz_type'] == 'paid_certificate') { ?>

<div class="quiz-info">
    📅 Exam Date:
    <?php echo $row['exam_date']; ?>
</div>

<div class="quiz-info">
    🕒 Exam Time:
    <?php echo $row['exam_time']; ?>
</div>

<a href="index.php?page=quiz_details&id=<?php echo $row['ID']; ?>"
class="btn btn-register">
Register Now
</a>

<?php } else { ?>

<a href="index.php?page=start_quiz&id=<?php echo $row['ID']; ?>"
class="btn btn-start">
Start Quiz
</a>

<?php } ?>

</div>

<?php } ?>

</div>