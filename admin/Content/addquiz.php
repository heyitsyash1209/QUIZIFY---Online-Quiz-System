<?php
include($_SERVER['DOCUMENT_ROOT'].'/quiz_system/config.php');

if(isset($_POST['add_quiz'])){

    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $time  = (int)$_POST['time'];
    $quiz_type = mysqli_real_escape_string($conn, $_POST['quiz_type']);

    $certificate_enabled = isset($_POST['certificate_enabled']) ? (int)$_POST['certificate_enabled'] : 0;
    $passing_percentage = isset($_POST['passing_percentage']) ? (int)$_POST['passing_percentage'] : 50;
    $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
    $result_mode = mysqli_real_escape_string($conn, $_POST['result_mode']);

    $exam_date = !empty($_POST['exam_date']) ? $_POST['exam_date'] : NULL;
    $exam_time = !empty($_POST['exam_time']) ? $_POST['exam_time'] : NULL;

    $query = "INSERT INTO quizzes
    (
        Title,
        `Time-limit`,
        quiz_type,
        price,
        passing_percentage,
        certificate_enabled,
        result_mode,
        exam_date,
        exam_time
    )
    VALUES
    (
        '$title',
        '$time',
        '$quiz_type',
        '$price',
        '$passing_percentage',
        '$certificate_enabled',
        '$result_mode',
        ".($exam_date ? "'$exam_date'" : "NULL").",
        ".($exam_time ? "'$exam_time'" : "NULL")."
    )";

    if(mysqli_query($conn, $query)){
        echo "<div class='msg success'>Quiz Added Successfully</div>";
    } else {
        echo "<div class='msg error'>Error: ".mysqli_error($conn)."</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Add Quiz</title>

<style>
/* ONLY CENTER FORM - NO DASHBOARD CHANGE */

.quiz-box{
    width:520px;
    max-width:90%;
    background:#fff;
    padding:20px;
    border-radius:15px;
    box-shadow:0 10px 25px rgba(0,0,0,0.15);

    /* center ONLY this box */
    position: relative;
    margin: 40px auto;
}

/* make it look balanced */
body{
    background:#f2f2f2;
}

/* optional clean look */
h2{
    text-align:center;
}

/* inputs clean */
input, select{
    width:100%;
    padding:8px;
    margin:6px 0 10px;
    border:1px solid #ccc;
    border-radius:6px;
}

.section{
    background:#f7f7f7;
    padding:10px;
    border-radius:10px;
    margin:10px 0;
}

button{
    width:100%;
    padding:10px;
    background:#007bff;
    color:white;
    border:none;
    border-radius:6px;
    cursor:pointer;
}
</style>

</head>

<body>

<div class="quiz-box">

<h2>Add New Quiz</h2>

<form method="POST">

<label>Quiz Title</label>
<input type="text" name="title" required>

<label>Time Limit (Minutes)</label>
<input type="number" name="time" required>

<div class="section">
    <label>Quiz Type</label>
    <select name="quiz_type" required>
        <option value="">Select</option>
        <option value="practice">Practice</option>
        <option value="free_certificate">Free Certificate</option>
        <option value="paid_certificate">Paid Certificate</option>
    </select>
</div>

<label>Certificate Enabled</label>
<select name="certificate_enabled">
    <option value="0">No</option>
    <option value="1">Yes</option>
</select>

<label>Passing Percentage</label>
<input type="number" name="passing_percentage" value="50">

<label>Price</label>
<input type="number" name="price" value="0">

<label>Result Mode</label>
<select name="result_mode">
    <option value="instant">Instant</option>
    <option value="manual">Manual</option>
</select>

<label>Exam Date</label>
<input type="date" name="exam_date">

<label>Exam Time</label>
<input type="time" name="exam_time">

<button type="submit" name="add_quiz">
➕ Add Quiz
</button>

</form>

</div>

</body>
</html>