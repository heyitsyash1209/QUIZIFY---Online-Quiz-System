<?php
include($_SERVER['DOCUMENT_ROOT'].'/quiz_system/config.php');

$base = "/quiz_system/admin/content/manage_question.php";

/* ================= DELETE QUIZ ================= */
if(isset($_GET['delete_quiz'])){

    $quiz_id = intval($_GET['delete_quiz']);

    mysqli_query($conn, "DELETE FROM quiz_questions WHERE Quiz_id=$quiz_id");

    mysqli_query($conn, "
        DELETE q FROM questions q
        WHERE q.quiz_id = $quiz_id
    ");

    mysqli_query($conn, "DELETE FROM quizzes WHERE ID=$quiz_id");

    echo "<script>
        alert('Quiz Deleted Successfully');
        window.location.href='$base';
    </script>";
    exit;
}

/* ================= DELETE QUESTION ================= */
if(isset($_GET['delete_q'])){

    $qid = intval($_GET['delete_q']);

    mysqli_query($conn,"DELETE FROM quiz_questions WHERE Question_id=$qid");
    mysqli_query($conn,"DELETE FROM questions WHERE Id=$qid");

    echo "<script>
        alert('Question Deleted');
        window.location.href=document.referrer;
    </script>";
    exit;
}

/* ================= BULK DELETE ================= */
if(isset($_POST['bulk_delete_selected'])){

    if(!empty($_POST['selected_questions'])){

        foreach($_POST['selected_questions'] as $qid){

            $qid = intval($qid);

            mysqli_query($conn,"DELETE FROM quiz_questions WHERE Question_id=$qid");
            mysqli_query($conn,"DELETE FROM questions WHERE Id=$qid");
        }

        echo "<script>
            alert('Selected Questions Deleted');
            window.location.href=document.referrer;
        </script>";
        exit;
    }
}

/* ================= FETCH QUIZZES ================= */
$search = $_GET['search'] ?? '';

$sql = "
SELECT q.ID, q.Title,
(SELECT COUNT(*) FROM quiz_questions qq WHERE qq.Quiz_id = q.ID) AS total_questions
FROM quizzes q
";

if($search != ''){
    $sql .= " WHERE q.Title LIKE '%$search%'";
}

$sql .= " ORDER BY q.ID DESC";

$quizzes = mysqli_query($conn, $sql);

/* ================= OPEN QUIZ ================= */
$selected_quiz = null;
$questions = [];

if(isset($_GET['quiz_id'])){

    $qid = intval($_GET['quiz_id']);

    $selected_quiz = mysqli_fetch_assoc(mysqli_query($conn,"
        SELECT * FROM quizzes WHERE ID=$qid
    "));

    $questions = mysqli_query($conn,"
        SELECT q.*
        FROM questions q
        JOIN quiz_questions qq ON qq.Question_id = q.Id
        WHERE qq.Quiz_id=$qid
        ORDER BY q.Id DESC
    ");
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Quiz Manager</title>

<style>
body{
    font-family:Segoe UI;
    background:#f4f6fb;
}

.container{
    width:90%;
    margin:auto;
}

.grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:15px;
}

.quiz-box{
    background:#fff;
    padding:15px;
    border-radius:12px;
    box-shadow:0 3px 10px rgba(0,0,0,0.08);
}

.btn{
    display:inline-block;
    padding:6px 10px;
    border-radius:6px;
    text-decoration:none;
    font-size:12px;
    margin-top:5px;
}

.btn-blue{background:#2563eb;color:white;}
.btn-red{background:#ef4444;color:white;}

.search{
    width:100%;
    padding:10px;
    margin:10px 0;
    border-radius:8px;
    border:1px solid #ccc;
}

.qbox{
    background:#fff;
    padding:12px;
    margin:10px 0;
    border-left:4px solid #2563eb;
    border-radius:8px;
    display:flex;
    gap:10px;
}
</style>

</head>

<body>

<div class="container">

<h2>📚 Quiz Manager</h2>

<form method="GET">
<input class="search" type="text" name="search" placeholder="Search Quiz..." value="<?= $search ?>">
</form>

<?php if($selected_quiz){ ?>
<a class="btn btn-blue" href="<?= $base ?>">⬅ Back</a>
<?php } ?>

<!-- QUIZ LIST -->
<?php if(!$selected_quiz){ ?>

<div class="grid">

<?php while($q = mysqli_fetch_assoc($quizzes)){ ?>

<div class="quiz-box">

<h3><?= $q['Title'] ?></h3>
<p>📌 Questions: <?= $q['total_questions'] ?></p>

<a class="btn btn-blue"
href="<?= $base ?>?quiz_id=<?= $q['ID'] ?>">
Open</a>

<a class="btn btn-red"
href="<?= $base ?>?delete_quiz=<?= $q['ID'] ?>"
onclick="return confirm('Delete quiz?')">
Delete</a>

</div>

<?php } ?>

</div>

<?php } ?>

<!-- QUESTIONS -->
<?php if($selected_quiz){ ?>

<h2>📘 <?= $selected_quiz['Title'] ?></h2>

<form method="POST">

<?php while($row = mysqli_fetch_assoc($questions)){ ?>

<div class="qbox">

<input type="checkbox" name="selected_questions[]" value="<?= $row['Id'] ?>">

<div>
<b><?= $row['Question'] ?></b>
<br><br>
✔ Answer: <?= $row['Answer'] ?>
<br><br>

<a class="btn btn-red"
href="<?= $base ?>?delete_q=<?= $row['Id'] ?>"
onclick="return confirm('Delete question?')">
Delete</a>

</div>

</div>

<?php } ?>

<button type="submit" name="bulk_delete_selected" class="btn btn-red"
onclick="return confirm('Delete selected questions?')">
🗑 Bulk Delete
</button>

</form>

<?php } ?>

</div>

</body>
</html>