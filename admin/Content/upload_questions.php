<?php
include($_SERVER['DOCUMENT_ROOT'].'/quiz_system/config.php');

$msg = "";

/* GET QUIZZES */
$quizzes = mysqli_query($conn, "SELECT * FROM quizzes");

if(isset($_POST['upload'])){

    $quiz_id = $_POST['quiz_id'] ?? '';
    $file = $_FILES['csv_file']['tmp_name'];

    if(empty($quiz_id)){
        $msg = "❌ Please select quiz";
    }
    else if(!$file){
        $msg = "❌ File not uploaded";
    }
    else {

        $handle = fopen($file, "r");

        if($handle){

            $success = 0;
            $failed = 0;

            // Skip header row
            fgetcsv($handle);

            while(($data = fgetcsv($handle, 1000, ",")) !== FALSE){

                if(count($data) < 8){
                    $failed++;
                    continue;
                }

                $question = mysqli_real_escape_string($conn, $data[0]);
                $op1 = mysqli_real_escape_string($conn, $data[1]);
                $op2 = mysqli_real_escape_string($conn, $data[2]);
                $op3 = mysqli_real_escape_string($conn, $data[3]);
                $op4 = mysqli_real_escape_string($conn, $data[4]);
                $answer = mysqli_real_escape_string($conn, $data[5]);
                $difficulty = mysqli_real_escape_string($conn, $data[6]);

                /* =========================
                   TEXT ONLY CORRECT ANSWER
                ============================*/
                $correct = mysqli_real_escape_string($conn, $data[7]);

                $sql = "INSERT INTO questions 
                (Question, Option1, Option2, Option3, Option4, Answer, Difficulty, correct_answer)
                VALUES
                ('$question','$op1','$op2','$op3','$op4','$answer','$difficulty','$correct')";

                if(mysqli_query($conn, $sql)){

                    $qid = mysqli_insert_id($conn);

                    mysqli_query($conn, "
                        INSERT INTO quiz_questions (Quiz_id, Question_id)
                        VALUES ('$quiz_id','$qid')
                    ");

                    $success++;

                } else {
                    $failed++;
                }
            }

            fclose($handle);

            $msg = "Upload Done ✔️ | Success: $success | Failed: $failed";

        } else {
            $msg = "❌ File could not be opened";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Upload Questions</title>

<style>
body{
    font-family: Arial;
    background: #f4f6f9;
    margin: 0;
}

.container{
    width: 420px;
    margin: 80px auto;
    background: white;
    padding: 20px;
    border: 1px solid #ddd;
}

h2{
    text-align: center;
}

input,select,button{
    width: 100%;
    padding: 10px;
    margin-top: 10px;
}

button{
    background: #333;
    color: white;
    border: none;
    cursor: pointer;
}

.msg{
    background: #eee;
    padding: 10px;
    margin-bottom: 10px;
    text-align: center;
}
</style>

</head>

<body>

<div class="container">

<h2>Upload Questions (TEXT MODE)</h2>

<?php if($msg!=""){ ?>
<div class="msg"><?php echo $msg; ?></div>
<?php } ?>

<form method="POST" enctype="multipart/form-data">

<select name="quiz_id" required>
    <option value="">-- Select Quiz --</option>

    <?php while($q = mysqli_fetch_assoc($quizzes)){ ?>
        <option value="<?php echo $q['ID']; ?>">
            <?php echo $q['Title']; ?>
        </option>
    <?php } ?>

</select>

<input type="file" name="csv_file" accept=".csv" required>

<button type="submit" name="upload">Upload</button>

</form>

</div>

</body>
</html>