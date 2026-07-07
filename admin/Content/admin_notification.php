<?php

include($_SERVER['DOCUMENT_ROOT'].'/quiz_system/config.php');

/* ================= SEND NOTIFICATION ================= */

if(isset($_POST['send'])){

    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    $type = $_POST['type'];
    $user_id = !empty($_POST['user_id']) ? intval($_POST['user_id']) : NULL;

    $query = "
        INSERT INTO notifications (title, message, type, user_id)
        VALUES (
            '$title',
            '$message',
            '$type',
            ".($user_id ? $user_id : "NULL")."
        )
    ";

    mysqli_query($conn, $query);

    $success = "Notification sent successfully!";
}

/* ================= GET USERS (for dropdown) ================= */

$users = mysqli_query($conn,"SELECT id, Username FROM users ORDER BY id DESC LIMIT 50");

?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Notifications</title>

<style>

body{
    font-family:Segoe UI;
    background:#f5f7fb;
}

.container{
    width:80%;
    margin:auto;
}

.card{
    background:#fff;
    padding:20px;
    border-radius:14px;
    box-shadow:0 4px 14px rgba(0,0,0,0.08);
    margin-top:20px;
}

h2{
    color:#2563eb;
}

input, textarea, select{
    width:100%;
    padding:12px;
    margin:8px 0;
    border-radius:8px;
    border:1px solid #ddd;
    outline:none;
}

button{
    background:#2563eb;
    color:#fff;
    padding:12px;
    border:none;
    border-radius:8px;
    cursor:pointer;
}

button:hover{
    background:#1d4ed8;
}

.success{
    background:#dcfce7;
    color:#166534;
    padding:10px;
    border-radius:8px;
    margin-bottom:10px;
}

</style>
</head>

<body>

<div class="container">

<h2>🔔 Admin Notification Panel</h2>

<div class="card">

<?php if(isset($success)){ ?>
<div class="success"><?= $success ?></div>
<?php } ?>

<form method="POST">

<input type="text" name="title" placeholder="Notification Title" required>

<textarea name="message" placeholder="Notification Message" required></textarea>

<select name="type">
    <option value="quiz">Quiz</option>
    <option value="result">Result</option>
    <option value="payment">Paid Quiz</option>
    <option value="certificate">Certificate</option>
    <option value="system">System</option>
</select>

<!-- OPTIONAL USER SELECTION -->
<select name="user_id">
    <option value="">Send to All Users</option>

    <?php while($u = mysqli_fetch_assoc($users)){ ?>
        <option value="<?= $u['id'] ?>">
            <?= $u['Username'] ?>
        </option>
    <?php } ?>

</select>

<button type="submit" name="send">Send Notification</button>

</form>

</div>

</div>

</body>
</html>