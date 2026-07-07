<?php
// reviewpage.php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'].'/quiz_system/config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;

$feedback_submitted = false;
$error_msg = "";

// वर्तमान क्विज़ का प्रकार (quiz_type) डेटाबेस से प्राप्त करें
$quiz_type = 'practice'; // डिफ़ॉल्ट मान
$quiz_stmt = $conn->prepare("SELECT quiz_type FROM quizzes WHERE id = ?");
if ($quiz_stmt) {
    $quiz_stmt->bind_param("i", $quiz_id);
    $quiz_stmt->execute();
    $quiz_result = $quiz_stmt->get_result();
    if ($quiz_row = $quiz_result->fetch_assoc()) {
        $quiz_type = $quiz_row['quiz_type'];
    }
    $quiz_stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    $q1 = trim($_POST['q1'] ?? '');
    $q2 = trim($_POST['q2'] ?? '');
    $q3 = trim($_POST['q3'] ?? '');
    $q4 = trim($_POST['q4'] ?? '');
    $q5 = trim($_POST['q5'] ?? '');
    $q6 = trim($_POST['q6'] ?? '');
    $q7_rating = intval($_POST['q7_rating'] ?? 5);
    $q7_suggestions = trim($_POST['q7_suggestions'] ?? '');

    $stmt = $conn->prepare("INSERT INTO quiz_review_feedback 
(user_id, quiz_id, q1_understanding, q2_confusing_question, q3_difficulty, q4_time_sufficient, q5_options_confusing, q6_system_issue, q7_rating, q8_suggestion, created_at)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("iissssssis", $user_id, $quiz_id, $q1, $q2, $q3, $q4, $q5, $q6, $q7_rating, $q7_suggestions);
    
    if ($stmt->execute()) {
        $stmt->close();
        
        // आवश्यकताओं के अनुसार कंडीशनल रीडायरेक्शन लॉजिक
        if ($quiz_type === 'paid_certificate') {
            // Paid Certificate Quiz: रिज़ल्ट पेज छोड़ें, सीधे होम/डैशबोर्ड पर भेजें
            header("Location: /quiz_system/dashboard/dashboard.php?page=home");
            exit();
        } elseif ($quiz_type === 'free_certificate') {
            // Free Certificate Quiz: रिज़ल्ट पेज पर जाएँ और सर्टिफिकेशन ट्रिगर पास करें
            header("Location: /quiz_system/dashboard/dashboard.php?page=result&quiz_id=" . $quiz_id . "&cert_msg=1");
            exit();
        } else {
            // Practice Quiz: पुराना सामान्य फ्लो रखें
            header("Location: /quiz_system/dashboard/dashboard.php?page=result&quiz_id=" . $quiz_id);
            exit();
        }
    } else {
        $error_msg = "Something went wrong. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Completed</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }
        body {
            background: #0f172a;
            color: #e2e8f0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            width: 100%;
            max-width: 600px;
            background: #111c33;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.4), 0 0 15px rgba(99, 102, 241, 0.05);
            border: 1px solid rgba(255,255,255,0.03);
            padding: 35px;
            transition: all 0.4s ease;
        }
        .thankyou-screen {
            text-align: center;
            animation: fadeIn 0.6s ease-out forwards;
        }
        .icon-wrapper {
            width: 70px;
            height: 70px;
            background: rgba(52, 211, 153, 0.1);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 20px;
            border: 1px solid rgba(52, 211, 153, 0.2);
            color: #34d399;
            font-size: 32px;
        }
        h2 {
            font-size: 24px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 10px;
        }
        p {
            color: #94a3b8;
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 25px;
        }
        .btn-primary {
            display: inline-block;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
            padding: 12px 28px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.25);
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
        }
        .feedback-form {
            margin-top: 5px;
            animation: fadeIn 0.5s ease-out forwards;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #cbd5e1;
            margin-bottom: 8px;
        }
        .radio-group {
            display: flex;
            gap: 15px;
            margin-top: 5px;
        }
        .radio-label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #94a3b8;
            font-size: 14px;
            cursor: pointer;
        }
        .radio-label input {
            accent-color: #6366f1;
            width: 16px;
            height: 16px;
        }
        input[type="text"], textarea, select {
            width: 100%;
            padding: 12px 16px;
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.08);
            background: #1e293b;
            color: white;
            font-size: 14px;
            outline: none;
            transition: all 0.3s ease;
        }
        input[type="text"]:focus, textarea:focus, select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }
        textarea {
            resize: none;
            height: 80px;
        }
        .rating-select {
            max-width: 120px;
        }
        .error-banner {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            padding: 12px;
            border-radius: 10px;
            border: 1px solid rgba(239, 68, 68, 0.2);
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @media(max-width: 480px) {
            .container { padding: 25px 20px; }
            .radio-group { flex-direction: column; gap: 8px; }
        }
    </style>
</head>
<body>

<div class="container">
    <?php if (!$feedback_submitted): ?>
        
        <div id="thankYouScreen" class="thankyou-screen">
            <div class="icon-wrapper">✓</div>
            <h2>Thank You!</h2>
            <p>Thank you for attempting the quiz. Keep learning and testing your knowledge daily to improve your scores.</p>
            <button class="btn-primary" onclick="showFeedbackForm()">Proceed to Feedback</button>
        </div>

        <div id="feedbackScreen" class="feedback-form" style="display: none;">
            <h2>Quiz Feedback</h2>
            <p>Please share your valuable feedback to help us improve your learning experience.</p>
            
            <?php if (!empty($error_msg)): ?>
                <div class="error-banner"><?php echo htmlspecialchars($error_msg); ?></div>
            <?php endif; ?>

            <form action="" method="POST">
                
                <div class="form-group">
                    <label>1. Did you understand the quiz questions clearly?</label>
                    <div class="radio-group">
                        <label class="radio-label"><input type="radio" name="q1" value="Yes" checked> Yes</label>
                        <label class="radio-label"><input type="radio" name="q1" value="No"> No</label>
                        <label class="radio-label"><input type="radio" name="q1" value="Somewhat"> Somewhat</label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="q2">2. Was any question confusing? If yes, which one?</label>
                    <input type="text" id="q2" name="q2" placeholder="e.g., Question 4 or none">
                </div>

                <div class="form-group">
                    <label>3. Difficulty level?</label>
                    <div class="radio-group">
                        <label class="radio-label"><input type="radio" name="q3" value="Easy"> Easy</label>
                        <label class="radio-label"><input type="radio" name="q3" value="Medium" checked> Medium</label>
                        <label class="radio-label"><input type="radio" name="q3" value="Hard"> Hard</label>
                    </div>
                </div>

                <div class="form-group">
                    <label>4. Was time sufficient?</label>
                    <div class="radio-group">
                        <label class="radio-label"><input type="radio" name="q4" value="Yes" checked> Yes</label>
                        <label class="radio-label"><input type="radio" name="q4" value="No"> No</label>
                    </div>
                </div>

                <div class="form-group">
                    <label>5. Were options clear or confusing?</label>
                    <div class="radio-group">
                        <label class="radio-label"><input type="radio" name="q5" value="Clear" checked> Clear</label>
                        <label class="radio-label"><input type="radio" name="q5" value="Confusing"> Confusing</label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="q6">6. Any technical issue faced?</label>
                    <input type="text" id="q6" name="q6" placeholder="e.g., Lag, images not loading, or none">
                </div>

                <div class="form-group">
                    <label for="q7_rating">7. Rate overall experience (1-5):</label>
                    <select id="q7_rating" name="q7_rating" class="rating-select">
                        <option value="5">5 ★★★★★</option>
                        <option value="4">4 ★★★★</option>
                        <option value="3">3 ★★★</option>
                        <option value="2">2 ★★</option>
                        <option value="1">1 ★</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="q7_suggestions">Any suggestions for improvement?</label>
                    <textarea id="q7_suggestions" name="q7_suggestions" placeholder="Write your suggestions here..."></textarea>
                </div>

                <button type="submit" name="submit_feedback" class="btn-primary" style="width: 100%;">Submit Feedback</button>
            </form>
        </div>

    <?php else: ?>
        
        <div class="thankyou-screen">
            <div class="icon-wrapper" style="background: rgba(99, 102, 241, 0.1); color: #6366f1; border-color: rgba(99, 102, 241, 0.2);">♥</div>
            <h2>Feedback Submitted!</h2>
            <p>Your responses have been successfully saved. Thank you for helping us improve our quizzes.</p>
            <a href="/quiz_system/dashboard/dashboard.php?page=home" class="btn-primary">Go to Dashboard</a>
        </div>

    <?php endif; ?>
</div>

<script>
function showFeedbackForm() {
    document.getElementById('thankYouScreen').style.display = 'none';
    document.getElementById('feedbackScreen').style.display = 'block';
}
</script>

</body>
</html>