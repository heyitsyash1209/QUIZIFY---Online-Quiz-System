<?php
if(session_status() == PHP_SESSION_NONE){
    session_start();
}

include($_SERVER['DOCUMENT_ROOT'].'/quiz_system/config.php');

if(!isset($_SESSION['user_id'])){
    header("Location: /quiz_system/login.php");
    exit();
}

$quiz_id = intval($_GET['quiz_id'] ?? 0);
$level = ucfirst(strtolower(trim($_GET['level'] ?? 'Easy')));

if($level == 'Advanced'){
    $level = 'Hard';
}

$quiz = mysqli_fetch_assoc(mysqli_query($conn, "
SELECT * FROM quizzes WHERE ID='$quiz_id'
"));

// DYNAMIC TIMER FIX: Extract from column, falling back to 45 if unset or non-numeric
$time_limit_minutes = isset($quiz['Time-limit']) && intval($quiz['Time-limit']) > 0 ? intval($quiz['Time-limit']) : 45;

/* ================== SUBMIT QUIZ (UNCHANGED BUSINESS LOGIC) ================== */

if(isset($_POST['submit_quiz'])){

    $user_id = $_SESSION['user_id'];

    $score = 0;
    $total_questions = 0;
    $correct_answers = 0;

    mysqli_query($conn, "
        INSERT INTO result 
        (User_id, Quiz_id, Score, Date, Level)
        VALUES 
        ('$user_id', '$quiz_id', 0, NOW(), '$level')
    ");

    $result_id = mysqli_insert_id($conn);

    $query = mysqli_query($conn, "
SELECT * FROM questions 
WHERE quiz_id = '$quiz_id'
AND LOWER(Difficulty) = LOWER('$level')
");

    while($row = mysqli_fetch_assoc($query)){

        $total_questions++;

        $qid = $row['Id'];

        $correct = trim($row['correct_answer'] ?: $row['Answer']);

        if(isset($_POST['answers'][$qid])){

            $user_answer = trim($_POST['answers'][$qid]);

          $stmt = $conn->prepare("
INSERT INTO answers_log
(Result_id, Question_id, User_answer)
VALUES (?, ?, ?)
");

$stmt->bind_param("iis", $result_id, $qid, $user_answer);
$stmt->execute();

            if(strtolower($user_answer) == strtolower($correct)){
                $score++;
                $correct_answers++;
            }
        }
    }

    /* ================== PERCENTAGE ================== */

    $percentage = 0;

    if($total_questions > 0){
        $percentage = ($correct_answers / $total_questions) * 100;
    }

    /* ================== POINTS SYSTEM ================== */

    $earned_points = 0;

    if($percentage >= 90){
        $earned_points = 200;
    }
    elseif($percentage >= 70){
        $earned_points = 100;
    }
    elseif($percentage >= 50){
        $earned_points = 50;
    }
    else{
        $earned_points = 10;
    }

    /* ================== UPDATE USER POINTS ================== */

    mysqli_query($conn, "
        UPDATE users 
        SET points = points + $earned_points
        WHERE id = '$user_id'
    ");

    /* ================== BADGE SYSTEM ================== */

    $getUser = mysqli_query($conn, "
        SELECT points FROM users
        WHERE id = '$user_id'
    ");

    $userData = mysqli_fetch_assoc($getUser);

    $totalPoints = $userData['points'];

    $badge = 'Beginner';

    if($totalPoints >= 20000){
        $badge = 'Elite Champion';
    }
    elseif($totalPoints >= 10000){
        $badge = 'Quiz Master';
    }
    elseif($totalPoints >= 5000){
        $badge = 'Advanced';
    }
    elseif($totalPoints >= 1000){
        $badge = 'Learner';
    }

    mysqli_query($conn, "
        UPDATE users
        SET badge = '$badge'
        WHERE id = '$user_id'
    ");

    /* ================== UPDATE RESULT ================== */

    mysqli_query($conn, "
        UPDATE result 
        SET 
        Score = '$score',
        correct_answers = '$correct_answers',
        total_questions = '$total_questions'
        WHERE Id = '$result_id'
    ");

    /* ================= CERTIFICATE LOGIC ================= */

    /* ================= CERTIFICATE LOGIC ================= */
$quiz_type = $quiz['quiz_type'];
$passing_percentage = $quiz['passing_percentage'];

if(
    ($quiz_type == 'free_certificate' || $quiz_type == 'paid_certificate')
    &&
    $percentage >= $passing_percentage
){

    $checkCert = mysqli_query($conn,"
        SELECT id
        FROM certificates
        WHERE user_id='$user_id'
        AND quiz_id='$quiz_id'
    ");

    if(mysqli_num_rows($checkCert) == 0){

        $cert_no = "CERT".time().rand(100,999);

        $insert = mysqli_query($conn,"
            INSERT INTO certificates
            (user_id, quiz_id, certificate_no, issue_date, score)
            VALUES
            ('$user_id','$quiz_id','$cert_no',NOW(),'$percentage')
        ");

        if(!$insert){
            die(mysqli_error($conn));
        }
    }
}

    /* ================= REWARD ALERT ================= */

    if($totalPoints >= 20000){
        $_SESSION['reward_msg'] = "🎉 Congratulations! You unlocked Quizify Gift Voucher!";
    }

    header("Location: /quiz_system/dashboard/content/reviewpage.php?quiz_id=".$quiz_id."&level=".$level);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CBT Exam Workspace</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
/* CBT DESIGN SYSTEM - LIGHT BLUE & WHITE THEME */
:root {
    --bg-main: #f8fbff;
    --bg-card: #ffffff;
    --bg-input-hover: #eef6ff;
    --bg-input-selected: #dbeafe;
    --primary: #2563eb;
    --primary-hover: #1d4ed8;
    --success: #16a34a;
    --warning: #f59e0b;
    --gray-light: #f1f5f9;
    --gray-dark: #334155;
    --gray-muted: #64748b;
    --border-color: #e2e8f0;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Inter', sans-serif;
    user-select: none; /* Anti-copy layer */
}

body {
    background-color: var(--bg-main);
    color: var(--gray-dark);
    line-height: 1.5;
}

/* TOPBAR HEADER */
.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--bg-card);
    padding: 16px 24px;
    box-shadow: 0 2px 8px rgba(37, 99, 235, 0.04);
    border-bottom: 1px solid var(--border-color);
    position: sticky;
    top: 0;
    z-index: 900;
}

.exam-title {
    font-size: 18px;
    font-weight: 700;
    color: var(--primary);
    letter-spacing: -0.3px;
}

.topbar-actions {
    display: flex;
    align-items: center;
    gap: 16px;
}

.timer {
    background: #ef4444;
    color: #fff;
    padding: 8px 16px;
    border-radius: 8px;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
    box-shadow: 0 4px 10px rgba(239, 68, 68, 0.2);
    display: flex;
    align-items: center;
    gap: 6px;
}

.exit-btn {
    background: var(--gray-light);
    color: var(--gray-dark);
    border: 1px solid var(--border-color);
    padding: 8px 16px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.2s ease;
}

.exit-btn:hover {
    background: #fee2e2;
    color: #ef4444;
    border-color: #fca5a5;
}

/* EXAM LIVE METRICS OVERVIEW BAR */
.stats-bar {
    max-width: 1400px;
    margin: 20px auto 0 auto;
    padding: 0 15px;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
}

.stat-item {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    padding: 14px 20px;
    border-radius: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.01);
}

.stat-item label {
    font-size: 13px;
    font-weight: 600;
    color: var(--gray-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-item span {
    font-size: 18px;
    font-weight: 700;
    color: var(--gray-dark);
}

.stat-item.active-time span {
    color: #ef4444;
}

/* WORKSPACE CONTAINERS */
.container {
    display: flex;
    max-width: 1400px;
    margin: 0 auto;
    gap: 20px;
    padding: 20px 15px 40px 15px;
}

.question-area {
    flex: 1;
    background: var(--bg-card);
    padding: 30px;
    border-radius: 16px;
    border: 1px solid var(--border-color);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.02);
    display: flex;
    flex-direction: column;
}

.sidebar {
    width: 320px;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.sidebar-panel {
    background: var(--bg-card);
    padding: 20px;
    border-radius: 16px;
    border: 1px solid var(--border-color);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.02);
}

.panel-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--gray-dark);
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--border-color);
}

/* QUESTION CARD INTERACTION WORKSPACE */
.question-card {
    display: none;
}

.question-card.active {
    display: block;
}

.question-text {
    font-size: 16px;
    font-weight: 600;
    color: var(--gray-dark);
    margin-bottom: 20px;
    line-height: 1.6;
}

.options-group {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 25px;
}

.option-container {
    display: flex;
    align-items: center;
    padding: 14px 18px;
    border: 2px solid var(--border-color);
    border-radius: 10px;
    cursor: pointer;
    background-color: var(--bg-card);
    transition: all 0.2s ease;
    position: relative;
}

.option-container:hover {
    background-color: var(--bg-input-hover);
    border-color: #bbf7d0;
}

.option-container.selected-ui {
    background-color: var(--bg-input-selected);
    border-color: var(--primary);
}

.option-container input[type="radio"] {
    margin-right: 14px;
    width: 18px;
    height: 18px;
    accent-color: var(--primary);
    cursor: pointer;
}

.option-text {
    font-size: 14px;
    font-weight: 500;
    color: var(--gray-dark);
}

/* ACTIONS FOOTER SYSTEM */
.nav-wrap {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: auto;
    padding-top: 25px;
    border-top: 1px solid var(--border-color);
    gap: 10px;
}

.btn {
    border: none;
    padding: 12px 20px;
    border-radius: 10px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.btn-secondary {
    background: var(--gray-light);
    color: var(--gray-dark);
    border: 1px solid var(--border-color);
}

.btn-secondary:hover {
    background: #e2e8f0;
}

.btn-warning {
    background: #fff7ed;
    color: var(--warning);
    border: 1px solid #fed7aa;
}

.btn-warning:hover {
    background: #ffedd5;
}

.btn-primary {
    background: var(--primary);
    color: #fff;
}

.btn-primary:hover {
    background: var(--primary-hover);
}

.btn-success {
    background: var(--success);
    color: #fff;
}

.btn-success:hover {
    background: #15803d;
}

/* PALETTE MATRIX ELEMENT SECTORS */
.palette {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 8px;
    margin-bottom: 15px;
}

.qbtn {
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s ease;
    background: var(--gray-light);
    color: var(--gray-muted);
    border: 1px solid var(--border-color);
}

.qbtn.current {
    background: var(--primary) !important;
    color: #fff !important;
    border-color: var(--primary) !important;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
}

.qbtn.answered {
    background: var(--success) !important;
    color: #fff !important;
    border-color: var(--success) !important;
}

.qbtn.reviewedBtn {
    background: var(--warning) !important;
    color: #fff !important;
    border-color: var(--warning) !important;
}

/* COLOR SCHEME PALETTE LEGEND INDICATORS */
.legend-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    font-size: 12px;
    font-weight: 500;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--gray-muted);
}

.legend-dot {
    width: 14px;
    height: 14px;
    border-radius: 4px;
}

.dot-current { background: var(--primary); }
.dot-answered { background: var(--success); }
.dot-review { background: var(--warning); }
.dot-unanswered { background: var(--gray-light); border: 1px solid var(--border-color); }

/* SIDEBAR PROGRESS HOOP SEGMENTS */
.progress-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    padding: 10px 0;
}

.progress-bar-track {
    width: 100%;
    height: 8px;
    background: var(--gray-light);
    border-radius: 10px;
    overflow: hidden;
}

.progress-bar-fill {
    height: 100%;
    background: var(--primary);
    width: 0%;
    transition: width 0.4s ease;
}

.progress-metrics {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 8px;
    font-size: 13px;
}

.metric-row {
    display: flex;
    justify-content: space-between;
    font-weight: 500;
}

/* DIALOG OVERLAY WIREFRAMES */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    z-index: 1000;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.modal-box {
    background: var(--bg-card);
    width: 100%;
    max-width: 450px;
    border-radius: 16px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    border: 1px solid var(--border-color);
    overflow: hidden;
    animation: modalSlide 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes modalSlide {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.modal-header {
    padding: 20px 24px;
    background: var(--bg-input-hover);
    border-bottom: 1px solid var(--border-color);
    font-weight: 700;
    font-size: 16px;
    color: var(--gray-dark);
}

.modal-body {
    padding: 24px;
    font-size: 14px;
    color: var(--gray-dark);
}

.summary-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-top: 15px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 12px;
    background: var(--bg-main);
    border-radius: 8px;
    font-weight: 600;
}

.modal-footer {
    padding: 16px 24px;
    background: var(--gray-light);
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    border-top: 1px solid var(--border-color);
}

/* RESPONSIVE CSS RE-STYLES */
@media (max-width: 1024px) {
    .container { flex-direction: column; }
    .sidebar { width: 100%; }
    .stats-bar { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 640px) {
    .stats-bar { grid-template-columns: 1fr; }
    .topbar { flex-direction: column; gap: 12px; text-align: center; }
    .nav-wrap { flex-wrap: wrap; }
    .btn { width: 100%; justify-content: center; }
}
</style>
</head>
<body>

<div class="topbar">
    <div class="exam-title"><?php echo htmlspecialchars($quiz['title'] ?? "Quiz Module Engine", ENT_QUOTES, 'UTF-8'); ?></div>
    <div class="topbar-actions">
        <div class="timer" id="timer">⏰ --:--</div>
        <button type="button" class="exit-btn" onclick="openExitModal()">🚪 Exit Exam</button>
    </div>
</div>

<div class="stats-bar">
    <div class="stat-item">
        <label>📚 Total Questions</label>
        <span id="statTotal">0</span>
    </div>
    <div class="stat-item">
        <label>✅ Answered</label>
        <span id="statAnswered" style="color: var(--success);">0</span>
    </div>
    <div class="stat-item">
        <label>⭐ Marked Review</label>
        <span id="statReview" style="color: var(--warning);">0</span>
    </div>
    <div class="stat-item active-time">
        <label>⏳ Time Left</label>
        <span id="statTimeBar">--:--</span>
    </div>
</div>

<div class="container">

    <form method="POST" class="question-area" id="quizForm">

    <?php
    $q = mysqli_query($conn, "
    SELECT * FROM questions 
    WHERE quiz_id = '$quiz_id'
    AND LOWER(Difficulty) = LOWER('$level')
    ");

    $ids = [];
    $i = 1;

    while($row = mysqli_fetch_assoc($q)){
        $ids[] = $row['Id'];
        $qid = $row['Id'];
    ?>

    <div class="question-card" id="q<?php echo $qid; ?>" data-qid="<?php echo $qid; ?>">
        <p class="question-text"><b>Q<?php echo $i++; ?>. <?php echo htmlspecialchars($row['Question'], ENT_QUOTES, 'UTF-8'); ?></b></p>

        <div class="options-group">
            <label class="option-container" id="container_<?php echo $qid; ?>_1">
                <input type="radio" name="answers[<?php echo $qid; ?>]" value="<?php echo htmlspecialchars($row['Option1'], ENT_QUOTES, 'UTF-8'); ?>" onchange="registerAnswer('<?php echo $qid; ?>')">
                <span class="option-text"><?php echo htmlspecialchars($row['Option1'], ENT_QUOTES, 'UTF-8'); ?></span>
            </label>

            <label class="option-container" id="container_<?php echo $qid; ?>_2">
                <input type="radio" name="answers[<?php echo $qid; ?>]" value="<?php echo htmlspecialchars($row['Option2'], ENT_QUOTES, 'UTF-8'); ?>" onchange="registerAnswer('<?php echo $qid; ?>')">
                <span class="option-text"><?php echo htmlspecialchars($row['Option2'], ENT_QUOTES, 'UTF-8'); ?></span>
            </label>

            <label class="option-container" id="container_<?php echo $qid; ?>_3">
                <input type="radio" name="answers[<?php echo $qid; ?>]" value="<?php echo htmlspecialchars($row['Option3'], ENT_QUOTES, 'UTF-8'); ?>" onchange="registerAnswer('<?php echo $qid; ?>')">
                <span class="option-text"><?php echo htmlspecialchars($row['Option3'], ENT_QUOTES, 'UTF-8'); ?></span>
            </label>

            <label class="option-container" id="container_<?php echo $qid; ?>_4">
                <input type="radio" name="answers[<?php echo $qid; ?>]" value="<?php echo htmlspecialchars($row['Option4'], ENT_QUOTES, 'UTF-8'); ?>" onchange="registerAnswer('<?php echo $qid; ?>')">
                <span class="option-text"><?php echo htmlspecialchars($row['Option4'], ENT_QUOTES, 'UTF-8'); ?></span>
            </label>
        </div>
    </div>

    <?php } ?>

    <div class="nav-wrap">
        <div>
            <button type="button" class="btn btn-secondary" onclick="prevQuestion()">⬅ Previous</button>
            <button type="button" class="btn btn-warning" onclick="toggleCurrentReview()">⭐ Mark Review</button>
        </div>
        <div>
            <button type="button" class="btn btn-secondary" onclick="nextQuestion()">Next ➡</button>
            <button type="button" class="btn btn-success" onclick="openSubmitModal()">Submit Quiz</button>
            <input type="hidden" name="submit_quiz" value="1">
        </div>
    </div>

    </form>

    <div class="sidebar">
        
        <div class="sidebar-panel">
            <div class="panel-title">📈 Exam Progress</div>
            <div class="progress-container">
                <div class="progress-bar-track">
                    <div class="progress-bar-fill" id="progressBarFill"></div>
                </div>
                <div class="progress-metrics">
                    <div class="metric-row">
                        <span style="color: var(--success);">Answered:</span>
                        <span id="metricAnswered">0</span>
                    </div>
                    <div class="metric-row">
                        <span style="color: var(--warning);">Marked Review:</span>
                        <span id="metricReview">0</span>
                    </div>
                    <div class="metric-row">
                        <span style="color: var(--gray-muted);">Remaining:</span>
                        <span id="metricRemaining">0</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="sidebar-panel">
            <div class="panel-title">🧩 Question Palette</div>
            <div class="palette">
                <?php foreach($ids as $index => $qid){ ?>
                <div class="qbtn" id="btn<?php echo $qid; ?>" onclick="goToQ('<?php echo $qid; ?>')">
                    <?php echo $index+1; ?>
                </div>
                <?php } ?>
            </div>
            
            <div class="legend-grid">
                <div class="legend-item"><div class="legend-dot dot-current"></div>Current</div>
                <div class="legend-item"><div class="legend-dot dot-answered"></div>Answered</div>
                <div class="legend-item"><div class="legend-dot dot-review"></div>Review</div>
                <div class="legend-item"><div class="legend-dot dot-unanswered"></div>Unanswered</div>
            </div>
        </div>

    </div>
</div>

<div class="modal-overlay" id="exitModal">
    <div class="modal-box">
        <div class="modal-header">🚪 Exit Examination Confirmation</div>
        <div class="modal-body">
            Are you sure you want to exit the exam? Your progress will not be submitted, and you will be redirected to the dashboard.
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeExitModal()">Cancel</button>
            <button type="button" class="btn btn-primary" style="background:#ef4444;" onclick="triggerExitRedirect()">Exit</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="submitModal">
    <div class="modal-box">
        <div class="modal-header">📋 Exam Summary Report</div>
        <div class="modal-body">
            Review your structural status metrics details carefully before final computational execution:
            <div class="summary-list">
                <div class="summary-row"><span>Total Questions</span><span id="sumTotal">0</span></div>
                <div class="summary-row" style="color: var(--success);"><span>Answered</span><span id="sumAnswered">0</span></div>
                <div class="summary-row" style="color: var(--warning);"><span>Marked Review</span><span id="sumReview">0</span></div>
                <div class="summary-row" style="color: #ef4444;"><span>Unanswered</span><span id="sumUnanswered">0</span></div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeSubmitModal()">Back to Exam</button>
            <button type="button" class="btn btn-success" onclick="executeFormFinalSubmission()">Submit Quiz</button>
        </div>
    </div>
</div>

<script>
// Local variable tracking contextual states map array instances
let currentQuestion = 0;
let cards = document.querySelectorAll('.question-card');
const totalQuestionsCount = cards.length;
const storageKeyPrefix = "quiz_session_<?php echo $quiz_id; ?>_<?php echo $_SESSION['user_id']; ?>_";

// Core Array Structures mapped across instances
let structureReviewedIds = new Set();
let tabSwitchCounter = 0;

// Dynamic Timer Configuration derived securely from DB configurations setup variables
let timeRemainingSeconds = <?php echo $time_limit_minutes * 60; ?>;

// ANTI-COPY / PASTE / CONTEXT MOUSE SHORTCUT COUNTERMEASURES
document.addEventListener('contextmenu', event => event.preventDefault());
document.addEventListener('keydown', function(event) {
    if (event.ctrlKey && (event.key === 'c' || event.key === 'v' || event.key === 'x' || event.key === 'C' || event.key === 'V' || event.key === 'X')) {
        event.preventDefault();
    }
});
document.addEventListener('copy', event => event.preventDefault());
document.addEventListener('cut', event => event.preventDefault());
document.addEventListener('paste', event => event.preventDefault());

// NAVIGATION RENDER HANDLER SYSTEM
function showQuestion(index) {
    if(cards.length === 0) return;
    
    cards.forEach(card => card.classList.remove('active'));
    if(cards[index]) cards[index].classList.add('active');

    // Sync state layout styles for dynamic node updates tracking
    document.querySelectorAll('.qbtn').forEach((btn, idx) => {
        btn.classList.remove('current');
        const qid = cards[idx].getAttribute('data-qid');
        
        // Re-apply background variations contextually
        if(structureReviewedIds.has(qid)) {
            btn.classList.add('reviewedBtn');
        } else if(isQuestionAnsweredLocal(qid)) {
            btn.classList.add('answered');
        } else {
            btn.classList.remove('reviewedBtn', 'answered');
        }
    });

    if(document.querySelectorAll('.qbtn')[index]) {
        document.querySelectorAll('.qbtn')[index].classList.add('current');
    }
    
    currentQuestion = index;
    refreshLiveExamStatsMetrics();
}

function nextQuestion() {
    if(currentQuestion < cards.length - 1) {
        showQuestion(currentQuestion + 1);
    }
}

function prevQuestion() {
    if(currentQuestion > 0) {
        showQuestion(currentQuestion - 1);
    }
}

function goToQ(id) {
    let btns = [...document.querySelectorAll('.qbtn')];
    let index = btns.findIndex(btn => btn.id === 'btn' + id);
    if(index >= 0) {
        showQuestion(index);
    }
}

// TOGGLE CORE MARK REVIEW STATUS ELEMENTS
function toggleCurrentReview() {
    if(cards.length === 0) return;
    const currentQid = cards[currentQuestion].getAttribute('data-qid');
    
    if(structureReviewedIds.has(currentQid)) {
        structureReviewedIds.delete(currentQid);
    } else {
        structureReviewedIds.add(currentQid);
    }
    
    showQuestion(currentQuestion);
}

// OLD LEGACY ACTION FUNCTION LAYER COMPATIBILITY PRESERVATION LAYER
function toggleReview(btnElement, questionId) {
    if(structureReviewedIds.has(questionId)) {
        structureReviewedIds.delete(questionId);
    } else {
        structureReviewedIds.add(questionId);
    }
    showQuestion(currentQuestion);
}

// SELECTION DATA PERSISTENT TRACKING CONTROLLERS
function registerAnswer(questionId) {
    const selectedRadio = document.querySelector(`input[name="answers[${questionId}]"]:checked`);
    if(selectedRadio) {
        localStorage.setItem(storageKeyPrefix + questionId, selectedRadio.value);
    }
    
    // Refresh visual wrapper layers
    syncRadioContainerSelectionEffects(questionId);
    showQuestion(currentQuestion);
}

function isQuestionAnsweredLocal(questionId) {
    return localStorage.getItem(storageKeyPrefix + questionId) !== null || 
           document.querySelector(`input[name="answers[${questionId}]"]:checked`) !== null;
}

function syncRadioContainerSelectionEffects(questionId) {
    // Strip container selection highlights inside specific groups
    const cardEl = document.getElementById('q' + questionId);
    if(!cardEl) return;
    
    cardEl.querySelectorAll('.option-container').forEach(container => {
        container.classList.remove('selected-ui');
    });
    
    const selectedInput = cardEl.querySelector(`input[name="answers[${questionId}]"]:checked`);
    if(selectedInput) {
        const parentLabel = selectedInput.closest('.option-container');
        if(parentLabel) parentLabel.classList.add('selected-ui');
    }
}

// REFRESH METRIC INSIGHTS LAYERS LIVE COUNTERS
function refreshLiveExamStatsMetrics() {
    let countAnswered = 0;
    let countReview = structureReviewedIds.size;
    
    cards.forEach(card => {
        const qid = card.getAttribute('data-qid');
        if(isQuestionAnsweredLocal(qid)) {
            countAnswered++;
        }
    });
    
    let countRemaining = totalQuestionsCount - countAnswered;
    
    // Sync Top tier bars variables
    document.getElementById('statTotal').textContent = totalQuestionsCount;
    document.getElementById('statAnswered').textContent = countAnswered;
    document.getElementById('statReview').textContent = countReview;
    
    // Sync Right Dashboard Metrics
    document.getElementById('metricAnswered').textContent = countAnswered;
    document.getElementById('metricReview').textContent = countReview;
    document.getElementById('metricRemaining').textContent = countRemaining;
    
    // Compute dynamic linear track animation ratios
    if(totalQuestionsCount > 0) {
        let percentRatio = (countAnswered / totalQuestionsCount) * 100;
        document.getElementById('progressBarFill').style.width = percentRatio + '%';
    }
}

// RESTORE LOCAL STORAGE STATE ARRAYS FOR CACHE PRESERVATION
function restoreLocalCachedAnswers() {
    cards.forEach(card => {
        const qid = card.getAttribute('data-qid');
        const savedValue = localStorage.getItem(storageKeyPrefix + qid);
        
        if(savedValue !== null) {
            const targetRadio = card.querySelector(`input[name="answers[${qid}]"][value="${savedValue}"]`);
            if(targetRadio) {
                targetRadio.checked = true;
                syncRadioContainerSelectionEffects(qid);
            }
        }
    });
    refreshLiveExamStatsMetrics();
}

function clearLocalStorageQuizCache() {
    cards.forEach(card => {
        const qid = card.getAttribute('data-qid');
        localStorage.removeItem(storageKeyPrefix + qid);
    });
}

// MODAL WINDOW INTERACTION LOGICS
function openExitModal() {
    document.getElementById('exitModal').style.display = 'flex';
}
function closeExitModal() {
    document.getElementById('exitModal').style.display = 'none';
}
function triggerExitRedirect() {
    clearLocalStorageQuizCache();
    window.location.href = '/quiz_system/dashboard/dashboard.php';
}

function openSubmitModal() {
    let countAnswered = 0;
    let countReview = structureReviewedIds.size;
    
    cards.forEach(card => {
        const qid = card.getAttribute('data-qid');
        if(isQuestionAnsweredLocal(qid)) {
            countAnswered++;
        }
    });
    
    let countUnanswered = totalQuestionsCount - countAnswered;
    
    document.getElementById('sumTotal').textContent = totalQuestionsCount;
    document.getElementById('sumAnswered').textContent = countAnswered;
    document.getElementById('sumReview').textContent = countReview;
    document.getElementById('sumUnanswered').textContent = countUnanswered;
    
    document.getElementById('submitModal').style.display = 'flex';
}

function closeSubmitModal() {
    document.getElementById('submitModal').style.display = 'none';
}

function executeFormFinalSubmission() {
    clearLocalStorageQuizCache();
    document.getElementById('quizForm').submit();
}

// ANTI TAB SWITCHING SECURITY FRAMEWORK
window.addEventListener('blur', function() {
    tabSwitchCounter++;
    if(tabSwitchCounter === 1) {
        alert("⚠️ Warning: Do not switch tabs during the exam. Switching tabs again will automatically submit your quiz execution immediately.");
    } else if(tabSwitchCounter >= 2) {
        alert("🚨 Security Policy Violation: Multiple tab switches detected. Your exam session is being forcefully compiled for auto-submission.");
        executeFormFinalSubmission();
    }
});

// INITIALIZATION DRIVER INSTANTIATION
document.addEventListener("DOMContentLoaded", function() {
    // Restore answers from localStorage cache map array references
    restoreLocalCachedAnswers();
    
    // Map structural visualization components layout defaults
    showQuestion(0);
    
    // Countdown Timer Loop Processing Wrapper Instantiations
    const timerDisplayTop = document.getElementById('timer');
    const timerDisplayStats = document.getElementById('statTimeBar');
    
    const intervalTickDriver = setInterval(() => {
        if(timeRemainingSeconds <= 0) {
            clearInterval(intervalTickDriver);
            timerDisplayTop.innerHTML = '⏰ 00:00';
            timerDisplayStats.innerHTML = '00:00';
            alert("⏳ Time's up! Your examination progress is being submitted automatically.");
            executeFormFinalSubmission();
            return;
        }
        
        timeRemainingSeconds--;
        
        let calculationMinutes = Math.floor(timeRemainingSeconds / 60);
        let calculationSeconds = timeRemainingSeconds % 60;
        
        let outputFormattedString = String(calculationMinutes).padStart(2, '0') + ':' + String(calculationSeconds).padStart(2, '0');
        
        if(timerDisplayTop) timerDisplayTop.innerHTML = '⏰ ' + outputFormattedString;
        if(timerDisplayStats) timerDisplayStats.innerHTML = outputFormattedString;
        
        // Critical color indicator shifts
        if(timeRemainingSeconds <= 60) {
            timerDisplayTop.style.background = '#dc2626';
            if(timerDisplayStats.parentElement) {
                timerDisplayStats.style.color = '#dc2626';
            }
        }
    }, 1000);
});
</script>
</body>
</html>