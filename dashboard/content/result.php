<?php
if(session_status() == PHP_SESSION_NONE){
    session_start();
}

if(!isset($_SESSION['user_id'])){
    header("Location: /quiz_system/login.php");
    exit();
}

include($_SERVER['DOCUMENT_ROOT'].'/quiz_system/config.php');

$user_id = $_SESSION['user_id'];

/* ================== START: CERTIFICATE GENERATION LOGIC ================== */
$show_cert_success = false;
$target_quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;

// जाँचें कि क्या यूज़र समीक्षा पेज से आया है और सर्टिफिकेट मैसेज की मांग है
if (isset($_GET['cert_msg']) && $_GET['cert_msg'] == '1' && $target_quiz_id > 0) {
    
    // 1. डेटाबेस से इस विशिष्ट क्विज़ की passing_percentage निकालें
    $passing_percentage = 40; // एक सुरक्षित डिफ़ॉल्ट मान (यदि टेबल में न मिले)
    $q_stmt = $conn->prepare("SELECT passing_percentage FROM quizzes WHERE ID = ?");
    if($q_stmt) {
        $q_stmt->bind_param("i", $target_quiz_id);
        $q_stmt->execute();
        $q_res = $q_stmt->get_result()->fetch_assoc();
        if($q_res && isset($q_res['passing_percentage'])) {
            $passing_percentage = floatval($q_res['passing_percentage']);
        }
        $q_stmt->close();
    }

    // 2. यूज़र के इस विशिष्ट क्विज़ के ताजा स्कोर/रिज़ल्ट की जानकारी प्राप्त करें
    $score_stmt = $conn->prepare("SELECT Score, Level FROM result WHERE User_id = ? AND Quiz_id = ? ORDER BY Id DESC LIMIT 1");
    if($score_stmt) {
        $score_stmt->bind_param("ii", $user_id, $target_quiz_id);
        $score_stmt->execute();
        $score_res = $score_stmt->get_result()->fetch_assoc();
        
        if($score_res) {
            $current_score = $score_res['Score'];
            $current_level = ucfirst(strtolower($score_res['Level']));
            
            // कुल प्रश्नों की संख्या निकालें ताकि सही प्रतिशत (Percentage) मिल सके
            $total_q_query = mysqli_query($conn, "
                SELECT COUNT(*) as total
                FROM quiz_questions qz
                JOIN questions q ON qz.Question_id = q.Id
                WHERE qz.Quiz_id = '$target_quiz_id'
                AND LOWER(q.Difficulty) = LOWER('$current_level')
            ");
            
            $total_q_row = mysqli_fetch_assoc($total_q_query);
            $target_total = ($total_q_row && $total_q_row['total'] > 0) ? $total_q_row['total'] : 0;
            
            $user_percentage = ($target_total > 0) ? ($current_score / $target_total) * 100 : 0;

            // 3. अगर यूज़र पासिंग क्राइटेरिया को पूरा करता है
            if ($user_percentage >= $passing_percentage) {
                
                // 4. डुप्लीकेट सर्टिफिकेट चेक करें
                $cert_check = $conn->prepare("SELECT id FROM certificates WHERE user_id = ? AND quiz_id = ?");
                $cert_check->bind_param("ii", $user_id, $target_quiz_id);
                $cert_check->execute();
                $cert_check_res = $cert_check->get_result();
                
                if ($cert_check_res->num_rows === 0) {
                    // यूनीक सर्टिफिकेट नंबर निर्माण
                    $certificate_no = "CERT-" . strtoupper(uniqid()) . "-" . rand(1000, 9999);
                    $issue_date = date('Y-m-d H:i:s');
                    
                    // certificates टेबल में इंसर्ट करें
                    $cert_insert = $conn->prepare("INSERT INTO certificates (user_id, quiz_id, certificate_no, issue_date, score) VALUES (?, ?, ?, ?, ?)");
                    $cert_insert->bind_param("iissd", $user_id, $target_quiz_id, $certificate_no, $issue_date, $user_percentage);
                    
                    if ($cert_insert->execute()) {
                        $show_cert_success = true;
                    }
                    $cert_insert->close();
                } else {
                    // यदि पहले से जनरेटेड है तो भी संदेश दिखाएँ
                    $show_cert_success = true;
                }
                $cert_check->close();
            }
        }
        $score_stmt->close();
    }
}
/* ================== END: CERTIFICATE GENERATION LOGIC ================== */

// आपका पुराना रिज़ल्ट फेच करने का कोड (बिना किसी बदलाव के)
$stmt = $conn->prepare("
    SELECT r.*, q.Title
    FROM result r
    JOIN quizzes q ON r.Quiz_id = q.ID
    WHERE r.User_id = ?
    ORDER BY r.Id DESC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
<title>User Results</title>

<style>
body {
    font-family: Arial;
    background: linear-gradient(135deg, #667eea, #764ba2);
    margin: 0;
}

.container {
    width: 85%;
    margin: auto;
    padding: 20px;
}

h2 {
    color: white;
    text-align: center;
}

.card {
    background: white;
    padding: 20px;
    margin: 15px 0;
    border-radius: 12px;
}

.title {
    font-size: 20px;
    font-weight: bold;
}

.level {
    color: #555;
    margin-top: 5px;
}

.easy { background: green; }
.medium { background: orange; }
.hard { background: red; }

.progress-bar {
    background: #ddd;
    height: 12px;
    border-radius: 10px;
}

.progress {
    height: 100%;
}

.percent {
    font-weight: bold;
    margin-top: 8px;
}

.date {
    font-size: 13px;
    color: gray;
}

/* एनीमेशन को सुचारू रूप से चलाने के लिए छोटा सा कीफ्रेम स्टाइल */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

</head>

<body>

<div class="container">

<h2>Your Quiz Results</h2>

<?php if (isset($show_cert_success) && $show_cert_success): ?>
    <div class="cert-notification-box" style="margin-top: 15px; margin-bottom: 25px; padding: 25px; background: rgba(52, 211, 153, 0.1); border: 1px solid rgba(52, 211, 153, 0.2); border-radius: 15px; text-align: center; animation: fadeIn 0.5s ease;">
        <h3 style="color: #34d399; font-size: 20px; margin-bottom: 12px; font-weight: 700; font-family: 'Arial', sans-serif;">🎉 Congratulations!</h3>
        <p style="color: #e2e8f0; font-size: 14px; line-height: 1.6; margin-bottom: 0; font-family: 'Arial', sans-serif;">
            You have successfully passed this quiz.<br>
            Your certificate has been generated successfully.<br>
            You can download your certificate from the <strong>My Certificates</strong> section.<br><br>
            <span style="color: #cbd5e1; font-style: italic;">Keep learning and keep attempting quizzes to improve your knowledge and skills. Happy Learning! 🚀</span>
        </p>
    </div>
<?php endif; ?>
<?php
while($row = $result->fetch_assoc()){

    $quiz_id = $row['Quiz_id'];
    $score = $row['Score'];
    $level = ucfirst(strtolower($row['Level']));

    /* ================== LEVEL-WISE TOTAL FIX ================== */
    $total_query = mysqli_query($conn, "
        SELECT COUNT(*) as total
        FROM quiz_questions qz
        JOIN questions q ON qz.Question_id = q.Id
        WHERE qz.Quiz_id = '$quiz_id'
        AND LOWER(q.Difficulty) = LOWER('$level')
    ");

    $total = mysqli_fetch_assoc($total_query)['total'];

    /* ================== SAFETY ================== */
    if($total == 0){
        $percentage = 0;
    } else {
        $percentage = ($score / $total) * 100;
    }

    if($percentage >= 70) $color="easy";
    elseif($percentage >= 40) $color="medium";
    else $color="hard";

    echo "<div class='card'>";
    echo "<div class='title'>{$row['Title']}</div>";
    echo "<div class='level'>Level: <b>$level</b></div>";
    echo "<div>Score: <b>$score / $total</b></div>";

    echo "<div class='progress-bar'>
            <div class='progress $color' style='width:{$percentage}%'></div>
          </div>";

    echo "<div class='percent'>".round($percentage,2)."%</div>";
    echo "<div class='date'>{$row['Date']}</div>";
    echo "</div>";
}
?>

</div>
</body>
</html>