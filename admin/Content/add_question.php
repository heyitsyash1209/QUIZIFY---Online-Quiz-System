<?php
include($_SERVER['DOCUMENT_ROOT'].'/quiz_system/config.php');

// ADD QUESTION
if(isset($_POST['add_question'])){

    $quiz_id = $_POST['quiz_id'];
    $question = $_POST['question'];
    $op1 = $_POST['op1'];
    $op2 = $_POST['op2'];
    $op3 = $_POST['op3'];
    $op4 = $_POST['op4'];
    $answer_key = $_POST['answer'];
    $difficulty = $_POST['difficulty'];

    // CORRECT ANSWER TEXT
    $correct_answer = "";

    if($answer_key == "op1"){
        $correct_answer = $op1;
    } elseif($answer_key == "op2"){
        $correct_answer = $op2;
    } elseif($answer_key == "op3"){
        $correct_answer = $op3;
    } elseif($answer_key == "op4"){
        $correct_answer = $op4;
    }

    // INSERT QUESTION
    $q1 = "INSERT INTO questions
(Question, Option1, Option2, Option3, Option4, Answer, Difficulty, correct_answer, quiz_id)
VALUES
('$question','$op1','$op2','$op3','$op4','$answer_key','$difficulty','$correct_answer','$quiz_id')";
    if(mysqli_query($conn, $q1)){

        $question_id = mysqli_insert_id($conn);

        // LINK WITH QUIZ
        $q2 = "INSERT INTO quiz_questions (Quiz_id, Question_id)
               VALUES ('$quiz_id','$question_id')";

        if(mysqli_query($conn, $q2)){
            $msg = "✅ Question Added Successfully (ID: $question_id)";
        } else {
            $msg = "❌ Error in quiz_questions: " . mysqli_error($conn);
        }

    } else {
        $msg = "❌ Error in questions: " . mysqli_error($conn);
    }
}

// Fetch quizzes
$quiz_data = mysqli_query($conn, "SELECT * FROM quizzes");
?>

<!DOCTYPE html>
<html>
<head>
<title>Add Question</title>
</head>

<body style="margin:0; font-family:Segoe UI; background:#f4f6f9; display:flex; flex-direction:column; min-height:100vh;">

<div style="display:flex; flex:1;">

<div style="flex:1; padding:30px;">

<h2 style="text-align:center; margin-bottom:15px; color:#1f2937;">
➕ Add Question
</h2>

<!-- MESSAGE -->
<?php if(isset($msg)){ ?>
<div style="
max-width:520px;
margin:0 auto 15px;
padding:10px;
background:#fff;
border-left:5px solid #22c55e;
border-radius:8px;
font-weight:600;
color:#333;
">
<?php echo $msg; ?>
</div>
<?php } ?>

<div style="
background:white;
padding:30px;
border-radius:15px;
width:520px;
margin:auto;
box-shadow:0 10px 25px rgba(0,0,0,0.08);
">

<form method="POST">

<label style="font-weight:600;">Select Quiz</label>
<select name="quiz_id" required style="width:100%; padding:12px; margin:8px 0 15px; border:1px solid #ddd; border-radius:8px;">
<option value="">-- Select Quiz --</option>

<?php while($q = mysqli_fetch_assoc($quiz_data)){ ?>
<option value="<?php echo $q['id']; ?>">
<?php echo $q['title']; ?>
</option>
<?php } ?>

</select>

<label style="font-weight:600;">Question</label>
<input type="text" name="question" required
style="width:100%; padding:12px; margin:8px 0 15px; border:1px solid #ddd; border-radius:8px;">

<div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">

<input type="text" name="op1" placeholder="Option 1" required
style="padding:12px; border:1px solid #ddd; border-radius:8px;">

<input type="text" name="op2" placeholder="Option 2" required
style="padding:12px; border:1px solid #ddd; border-radius:8px;">

<input type="text" name="op3" placeholder="Option 3" required
style="padding:12px; border:1px solid #ddd; border-radius:8px;">

<input type="text" name="op4" placeholder="Option 4" required
style="padding:12px; border:1px solid #ddd; border-radius:8px;">

</div>

<label style="font-weight:600; display:block; margin-top:15px;">Correct Answer</label>
<select name="answer" required
style="width:100%; padding:12px; margin:8px 0 15px; border:1px solid #ddd; border-radius:8px;">
<option value="">-- Select Correct Answer --</option>
<option value="op1">Option 1</option>
<option value="op2">Option 2</option>
<option value="op3">Option 3</option>
<option value="op4">Option 4</option>
</select>

<label style="font-weight:600;">Difficulty</label>
<select name="difficulty" required
style="width:100%; padding:12px; margin:8px 0 20px; border:1px solid #ddd; border-radius:8px;">
<option value="Easy">Easy</option>
<option value="Moderate">Moderate</option>
<option value="Hard">Hard</option>
</select>

<button type="submit" name="add_question"
style="
width:100%;
padding:12px;
background:linear-gradient(135deg,#22c55e,#16a34a);
color:white;
border:none;
border-radius:10px;
font-size:15px;
font-weight:bold;
cursor:pointer;
">
➕ Add Question
</button>

</form>

</div>

</div>

</div>

<!-- ✅ FIXED FOOTER (ONLY CHANGE) -->
<div style="margin-top:auto;">

</div>

</body>
</html>