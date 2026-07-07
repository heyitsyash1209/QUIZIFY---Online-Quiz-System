<?php
// verify_access.php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['quiz_id'])) {
    die("Quiz Parameter Missing.");
}

$quiz_id = intval($_GET['quiz_id']);
$user_id = $_SESSION['user_id'];
$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_code'])) {
    $access_code = trim($_POST['access_code']);

    if (empty($access_code)) {
        $error_msg = "Please enter the access code.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM payments WHERE user_id = ? AND quiz_id = ? AND status = 'paid' AND access_code = ?");
        $stmt->bind_param("iiss", $user_id, $quiz_id, $access_code);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $_SESSION['quiz_access_' . $quiz_id] = true;
            header("Location: quiz_start.php?quiz_id=" . $quiz_id . "&level=Hard");
            exit();
        } else {
            $error_msg = "Invalid Access Code";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Quiz Access</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; padding: 20px; }
        .verify-container { max-width: 400px; background: #fff; margin: 80px auto; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); text-align: center; }
        h2 { margin-top: 0; color: #333; }
        .form-group { margin: 20px 0; text-align: left; }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #555; }
        input[type="text"] { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 16px; letter-spacing: 2px; text-align: center; }
        .btn-verify { width: 100%; background: #007bff; color: white; border: none; padding: 12px; font-size: 16px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn-verify:hover { background: #0056b3; }
        .error-msg { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; border: 1px solid #f5c6cb; font-weight: bold; }
        .back-link { display: inline-block; margin-top: 15px; color: #6c757d; text-decoration: none; font-size: 14px; }
        .back-link:hover { color: #333; }
    </style>
</head>
<body>

<div class="verify-container">
    <h2>Enter Access Code</h2>
    <p style="color: #666; font-size: 14px;">Please enter the verification access code sent to your email or generated on your payment slip.</p>
    
    <?php if (!empty($error_msg)): ?>
        <div class="error-msg"><?php echo htmlspecialchars($error_msg); ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="form-group">
            <input type="text" name="access_code" placeholder="e.g. QZ8472" required autocomplete="off">
        </div>
        <button type="submit" name="verify_code" class="btn-verify">Verify & Start Quiz</button>
    </form>
    
    <a href="my_registrations.php" class="back-link">← Back to My Registrations</a>
</div>

</body>
</html>