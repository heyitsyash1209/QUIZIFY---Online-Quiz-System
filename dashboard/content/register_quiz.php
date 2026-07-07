<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/quiz_system/config.php');

if(!isset($_SESSION['user_id'])){
    die("Please Login First");
}

$user_id = $_SESSION['user_id'];

if(!isset($_GET['quiz_id'])){
    die("Quiz ID Missing");
}

$quiz_id = (int)$_GET['quiz_id'];

/* Quiz Exists Check */
$q = mysqli_query($conn, "SELECT * FROM quizzes WHERE id='$quiz_id'");
if(mysqli_num_rows($q) == 0){
    die("Quiz Not Found");
}

$quiz = mysqli_fetch_assoc($q);

/* Already Registered Check */
$check = mysqli_query($conn, "
SELECT * FROM quiz_registrations 
WHERE user_id='$user_id' 
AND quiz_id='$quiz_id'
");

if(mysqli_num_rows($check) > 0){

    $row = mysqli_fetch_assoc($check);

    if($row['payment_status'] == 'paid'){
        die("Already Registered & Paid");
    }

    echo "
    <div style='max-width:600px;margin:50px auto;padding:20px;background:#fff3cd;text-align:center;font-family:Segoe UI;border-radius:10px'>
        <h2>⚠ Already Registered</h2>
        <p>Payment Pending</p>

        <a href='payment.php?reg_id=".$row['id']."&quiz_id=$quiz_id'
           style='padding:10px 20px;background:green;color:white;text-decoration:none;border-radius:5px'>
           Pay Now 💳
        </a>
    </div>";
    exit();
}

/* New Registration */
$insert = mysqli_query($conn, "
INSERT INTO quiz_registrations
(user_id, quiz_id, payment_status, attempt_status, registered_at)
VALUES
('$user_id','$quiz_id','pending','not_attempted',NOW())
");

if($insert){

    $reg_id = mysqli_insert_id($conn);

    echo "
    <div style='max-width:600px;margin:50px auto;padding:20px;background:#d1ecf1;text-align:center;font-family:Segoe UI;border-radius:10px'>
        <h2>✅ Registered Successfully</h2>
        <h3>".$quiz['title']."</h3>

        <p><b>Payment Status:</b> Pending</p>

        <a href='payment.php?reg_id=$reg_id&quiz_id=$quiz_id'
           style='padding:10px 20px;background:#28a745;color:white;text-decoration:none;border-radius:5px'>
           Pay Now 💳
        </a>

        <br><br>
        <a href='../dashboard.php?page=quizzes'>Back</a>
    </div>";
}
else{
    echo "Error: ".mysqli_error($conn);
}
?>