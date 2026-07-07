<?php
// payment.php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/quiz_system/config.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require $_SERVER['DOCUMENT_ROOT'].'/quiz_system/PHPMailer-7.1.1/src/Exception.php';
require $_SERVER['DOCUMENT_ROOT'].'/quiz_system/PHPMailer-7.1.1/src/PHPMailer.php';
require $_SERVER['DOCUMENT_ROOT'].'/quiz_system/PHPMailer-7.1.1/src/SMTP.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['reg_id']) || !isset($_GET['quiz_id'])) {
    die("Invalid Request parameters.");
}

$reg_id = intval($_GET['reg_id']);
$quiz_id = intval($_GET['quiz_id']);
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM quiz_registrations WHERE id = ? AND user_id = ? AND quiz_id = ?");
$stmt->bind_param("iii", $reg_id, $user_id, $quiz_id);
$stmt->execute();
$reg_res = $stmt->get_result();
if ($reg_res->num_rows === 0) {
    die("Registration record not found.");
}
$registration = $reg_res->fetch_assoc();

if ($registration['payment_status'] === 'paid') {
    header("Location: payment_slip.php?reg_id=" . $reg_id);
    exit();
}

$stmt = $conn->prepare("SELECT * FROM quizzes WHERE ID = ?");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$quiz_res = $stmt->get_result();
if ($quiz_res->num_rows === 0) {
    die("Quiz not found.");
}
$quiz = $quiz_res->fetch_assoc();

$stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_res = $stmt->get_result();
$user_data = $user_res->fetch_assoc();
$user_email = $user_data['email'];

$error_msg = "";
$success_payment = false;
$payment_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_payment'])) {
    $transaction_id = trim($_POST['transaction_id']);
    if (empty($transaction_id)) {
        $error_msg = "Please enter your Transaction ID.";
    } else {
        $access_code = "QZ" . rand(1000, 9999);
        $amount = $quiz['price'];
        $status = 'paid';
        $payment_id_generated = 'PAY' . rand(10000, 99999);

        $conn->begin_transaction();
        try {
            $ins_stmt = $conn->prepare("INSERT INTO payments (user_id, quiz_id, amount, payment_id, status, transaction_id, email, access_code, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $ins_stmt->bind_param("iidsssss", $user_id, $quiz_id, $amount, $payment_id_generated, $status, $transaction_id, $user_email, $access_code);
            $ins_stmt->execute();
            $inserted_payment_id = $conn->insert_id;

            $upd_stmt = $conn->prepare("UPDATE quiz_registrations SET payment_status = 'paid' WHERE id = ?");
            $upd_stmt->bind_param("i", $reg_id);
            $upd_stmt->execute();

            $conn->commit();

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'quizify1230@gmail.com';
                $mail->Password   = 'xpnk adpi zqqs mhpw';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('quizify1230@gmail.com', 'Quizify System');
                $mail->addAddress($user_email);

                $mail->isHTML(true);
                $mail->Subject = 'Quiz Payment Successful';
                $mail->Body    = "
                    <h3>Payment Successful</h3>
                    <p><strong>Quiz Name:</strong> {$quiz['Title']}</p>
                    <p><strong>Transaction ID:</strong> {$transaction_id}</p>
                    <p><strong>Access Code:</strong> {$access_code}</p>
                    <p><strong>Exam Date:</strong> {$quiz['exam_date']}</p>
                    <p><strong>Exam Time:</strong> {$quiz['exam_time']}</p>
                ";
                $mail->send();
            } catch (Exception $e) {
                // Email failure logged silently to maintain flow
            }

            $stmt = $conn->prepare("SELECT * FROM payments WHERE id = ?");
            $stmt->bind_param("i", $inserted_payment_id);
            $stmt->execute();
            $payment_data = $stmt->get_result()->fetch_assoc();
            $success_payment = true;

        } catch (Exception $e) {
            $conn->rollback();
            $error_msg = "Transaction processing failed. Please try again.";
        }
    }
}

// Mobile/Tablet dynamic runtime link parameters mapping setup
$upi_id = "7906001327@fam";
$quiz_price = isset($quiz['price']) ? (float)$quiz['price'] : 0.00;
$deeplink_url = "upi://pay?pa=" . urlencode($upi_id) . "&pn=" . urlencode("Quizify") . "&am=" . urlencode($quiz_price) . "&cu=INR";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quizify Quiz Payment</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-main: #eef6ff;
            --bg-card: #ffffff;
            --primary: #2563eb;
            --border-light: #dbeafe;
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --success: #16a34a;
            --success-hover: #15803d;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', -apple-system, sans-serif; }
        body { 
            background-color: var(--bg-main); 
            color: var(--text-dark); 
            padding: 40px 20px; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
        }
        
        .payment-container, .slip-container { 
            max-width: 500px; 
            width: 100%;
            background: var(--bg-card); 
            padding: 30px; 
            border-radius: 20px; 
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.05); 
            border: 1px solid var(--border-light);
            position: relative;
            overflow: hidden;
        }

        /* LIGHT BOOKMARK/WATERMARK BACKGROUND IMPLEMENTATION LAYER */
        .payment-container::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            width: 150px;
            height: 150px;
            transform: translate(-50%, -50%);
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%232563eb'%3E%3Cpath d='M19 2H5c-1.1 0-2 .9-2 2v18l7-3 7 3V4c0-1.1-.9-2-2-2zm0 15l-5-2.18L9 17V5h10v12z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-size: contain;
            opacity: 0.035; /* Crisp 3.5% transparency pattern window */
            pointer-events: none;
            z-index: 0;
        }

        .payment-container * {
            position: relative;
            z-index: 1;
        }

        h2 { font-size: 24px; font-weight: 800; text-align: center; color: var(--primary); margin-bottom: 25px; letter-spacing: -0.5px; }
        h3 { text-align: center; color: var(--text-dark); margin-bottom: 20px; }
        
        .detail-row { display: flex; justify-content: space-between; margin-bottom: 16px; border-bottom: 1px solid var(--border-light); padding-bottom: 10px; font-size: 14px; }
        .detail-label { font-weight: 600; color: var(--text-muted); }
        .detail-value { font-weight: 700; color: var(--text-dark); }
        
        .qr-section { text-align: center; margin: 25px 0; }
        .qr-section label { display: block; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); margin-bottom: 12px; }
        .qr-wrapper { display: inline-block; position: relative; cursor: pointer; border-radius: 12px; overflow: hidden; border: 1px solid var(--border-light); box-shadow: 0 4px 12px rgba(0,0,0,0.02); background: #fff; padding: 6px; }
        .qr-image { width: 200px; height: 200px; display: block; transition: filter 0.3s ease; }
        .blur { filter: blur(12px); }
        .qr-overlay-text { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: rgba(37, 99, 235, 0.9); color: #fff; padding: 10px 16px; border-radius: 8px; font-size: 13px; font-weight: 700; pointer-events: none; white-space: nowrap; box-shadow: 0 4px 10px rgba(0,0,0,0.15); }
        
        .upi-box { background: #f4f9ff; padding: 14px; border-radius: 12px; font-weight: 600; margin-bottom: 25px; font-size: 14px; border: 1px dashed rgba(37, 99, 235, 0.3); display: flex; justify-content: space-between; align-items: center; color: var(--text-dark); }
        .copy-btn { background: var(--primary); color: white; border: none; padding: 8px 14px; border-radius: 8px; cursor: pointer; font-size: 12px; font-weight: 700; transition: background 0.2s; }
        .copy-btn:hover { background: #1d4ed8; }
        
        /* EQUALLY SIZED GRID-BASED PAYMENT APPLICATIONS SECTION */
        .apps-container { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 25px; width: 100%; }
        .app-icon { 
            text-decoration: none;
            color: inherit;
            background: #fff;
            border: 1px solid var(--border-light);
            border-radius: 14px;
            padding: 14px 8px;
            text-align: center; 
            font-size: 13px; 
            color: var(--text-dark); 
            font-weight: 700; 
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .app-icon:hover { 
            transform: translateY(-3px);
            border-color: var(--primary);
            background: #f8fbff;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.06);
        }
        .app-icon:active { transform: scale(0.97); }
        .app-icon img { width: 44px; height: 44px; display: block; margin: 0 auto 8px; border-radius: 10px; object-fit: contain; }
        
        /* FORM COMPONENT NESTED INTEGRATION ENGINE BLOCKS */
        .verification-form-block { margin-top: 15px; padding-top: 20px; border-top: 1px dashed var(--border-light); width: 100%; }
        .form-group { margin-bottom: 20px; display: flex; flex-direction: column; gap: 8px; width: 100%; }
        label.input-label { display: block; font-size: 13px; font-weight: 700; color: var(--text-dark); }
        input[type="text"].form-input { width: 100%; padding: 14px; border: 1px solid var(--border-light); border-radius: 12px; box-sizing: border-box; font-size: 14px; font-weight: 600; color: var(--text-dark); outline: none; transition: border-color 0.2s; background-color: #fafafa; }
        input[type="text"].form-input:focus { border-color: var(--primary); background-color: #fff; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.08); }
        
        .submit-btn { width: 100%; background: var(--success); color: white; border: none; padding: 15px; font-size: 15px; border-radius: 12px; cursor: pointer; font-weight: 700; transition: all 0.2s; box-shadow: 0 4px 12px rgba(22, 163, 74, 0.15); }
        .submit-btn:hover { background: var(--success-hover); transform: translateY(-1px); box-shadow: 0 6px 16px rgba(22, 163, 74, 0.25); }
        .submit-btn:active { transform: translateY(0); }
        
        .error-banner { background: #fef2f2; color: #991b1b; padding: 12px; border-radius: 10px; margin-bottom: 20px; text-align: center; border: 1px solid #fca5a5; font-size: 14px; font-weight: 600; }
        .limit-msg { color: #dc3545; font-weight: bold; margin-top: 10px; display: none; font-size: 14px; }
        .timer-msg { font-size: 13px; color: var(--primary); font-weight: 600; margin-top: 8px; }
        
        .slip-header { text-align: center; color: var(--success); font-size: 22px; font-weight: 800; margin-bottom: 20px; letter-spacing: -0.5px; }
        .print-btn { background: #64748b; color: white; border: none; padding: 14px; width: 100%; border-radius: 12px; cursor: pointer; margin-top: 20px; font-weight: 700; font-size: 15px; transition: background 0.2s; }
        .print-btn:hover { background: #475569; }

        @media (max-width: 400px) {
            .apps-container { grid-template-columns: 1fr; }
            .payment-container, .slip-container { padding: 20px; }
        }
    </style>
</head>
<body>

<?php if (!$success_payment): ?>
    <div class="payment-container">
        <h2>Quizify Quiz Payment</h2>
        
        <?php if (!empty($error_msg)): ?>
            <div class="error-banner"><?php echo htmlspecialchars($error_msg); ?></div>
        <?php endif; ?>

        <div class="detail-row">
            <span class="detail-label">Quiz Title:</span>
            <span class="detail-value"><?php echo htmlspecialchars($quiz['title']); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Amount to Pay:</span>
            <span class="detail-value" style="color: #ef4444;">₹<?php echo htmlspecialchars($quiz['price']); ?></span>
        </div>

        <div class="qr-section">
            <label>Scan QR Code to Pay</label>
            <div class="qr-wrapper" id="qrWrapper">
                <img src="/quiz_system/dashboard/images/payment_qr.jpeg" alt="Payment QR" class="qr-image blur" id="qrImage">
                <div class="qr-overlay-text" id="qrOverlayText">Click to Reveal QR</div>
            </div>
            <div class="timer-msg" id="timerMsg"></div>
            <div class="limit-msg" id="limitMsg">Maximum QR View Limit Reached.</div>
        </div>

        <div class="upi-box">
            <span id="upiId">7906001327@fam</span>
            <button class="copy-btn" onclick="copyUPI(null)">Copy UPI ID</button>
        </div>

        <div class="apps-container">
            <a href="<?php echo $deeplink_url; ?>" class="app-icon" onclick="handleAppClick(event)">
                <img src="https://img.icons8.com/color/96/phone-pe.png" alt="PhonePe">
                PhonePe
            </a>

            <a href="<?php echo $deeplink_url; ?>" class="app-icon" onclick="handleAppClick(event)">
                <img src="https://img.icons8.com/color/96/google-pay.png" alt="Google Pay">
                G-Pay
            </a>

            <a href="<?php echo $deeplink_url; ?>" class="app-icon" onclick="handleAppClick(event)">
                <img src="https://img.icons8.com/color/96/paytm.png" alt="Paytm">
                Paytm
            </a>

            <a href="<?php echo $deeplink_url; ?>" class="app-icon" onclick="handleAppClick(event)">
                <img src="https://www.uxdt.nic.in/wp-content/uploads/2020/06/BHIM_Preview.png" alt="BHIM">
                BHIM
            </a>
        </div>

        <div class="verification-form-block">
            <form action="" method="POST">
                <div class="form-group">
                    <label for="transaction_id" class="input-label">Enter Transaction ID / UTR Numbers:</label>
                    <input type="text" id="transaction_id" name="transaction_id" class="form-input" autocomplete="off" placeholder="Enter 12-digit UPI Transaction ID">
                </div>
                <button type="submit" name="submit_payment" class="submit-btn">Submit Payment</button>
            </form>
        </div>
    </div>

    <script>
        let viewCount = 0;
        let timeoutId = null;
        let intervalId = null;
        const maxLimit = 3;

        const qrWrapper = document.getElementById('qrWrapper');
        const qrImage = document.getElementById('qrImage');
        const qrOverlayText = document.getElementById('qrOverlayText');
        const limitMsg = document.getElementById('limitMsg');
        const timerMsg = document.getElementById('timerMsg');

        qrWrapper.addEventListener('click', function() {
            if (viewCount >= maxLimit) return;
            if (!qrImage.classList.contains('blur')) return;

            viewCount++;
            qrImage.classList.remove('blur');
            qrOverlayText.style.display = 'none';

            let timeLeft = 60;
            timerMsg.innerText = `Visible for ${timeLeft} seconds`;

            intervalId = setInterval(() => {
                timeLeft--;
                if (timeLeft > 0) {
                    timerMsg.innerText = `Visible for ${timeLeft} seconds`;
                } else {
                    clearInterval(intervalId);
                }
            }, 1000);

            timeoutId = setTimeout(() => {
                blurQR();
            }, 60000);
        });

        function blurQR() {
            clearInterval(intervalId);
            clearTimeout(timeoutId);
            qrImage.classList.add('blur');
            timerMsg.innerText = "";

            if (viewCount >= maxLimit) {
                qrOverlayText.style.display = 'none';
                limitMsg.style.display = 'block';
                qrWrapper.style.cursor = 'default';
            } else {
                qrOverlayText.style.display = 'block';
            }
        }

        function copyUPI(event) {
            if(event) {
                event.preventDefault();
            }
            const upiText = document.getElementById('upiId').innerText;
            navigator.clipboard.writeText(upiText).then(() => {
                alert("UPI ID Copied!\n\n" + upiText + "\n\nPaste it in PhonePe / GPay / Paytm");
            });
        }

        function handleAppClick(event) {
            // Checks if user is on desktop to offer quick copy fallback, otherwise triggers app link redirect behavior
            if (!/Android|iPhone|iPad|iPod/i.test(navigator.userAgent)) {
                event.preventDefault();
                copyUPI(null);
            }
        }
    </script>

<?php else: ?>
    <div class="slip-container">
        <div class="slip-header">Payment Slip</div>
        <div class="detail-row">
            <span class="detail-label">Quiz Name:</span>
            <span class="detail-value"><?php echo htmlspecialchars($quiz['Title']); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Amount:</span>
            <span class="detail-value">₹<?php echo htmlspecialchars($payment_data['amount']); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Transaction ID:</span>
            <span class="detail-value"><?php echo htmlspecialchars($payment_data['transaction_id']); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Email:</span>
            <span class="detail-value"><?php echo htmlspecialchars($payment_data['email']); ?></span>
        </div>
        <div class="detail-row" style="background: #fef3c7; padding: 10px; border-radius: 10px; border: 1px solid #fde68a;">
            <span class="detail-label" style="color: #b45309;">Access Code:</span>
            <span class="detail-value" style="font-weight: 800; font-size: 16px; color: #b45309;"><?php echo htmlspecialchars($payment_data['access_code']); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Date & Time:</span>
            <span class="detail-value"><?php echo htmlspecialchars($payment_data['created_at']); ?></span>
        </div>
        <button class="print-btn" onclick="window.print()">Print Slip</button>
        <div style="text-align: center; margin-top: 25px;">
            <a href="my_registrations.php" style="color: var(--primary); text-decoration: none; font-weight: 700; font-size: 14px;">Go to My Registrations</a>
        </div>
    </div>
<?php endif; ?>
</body>
</html>