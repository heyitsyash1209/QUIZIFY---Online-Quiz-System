<?php

require_once __DIR__ . '/PHPMailer-7.1.1/src/Exception.php';
require_once __DIR__ . '/PHPMailer-7.1.1/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer-7.1.1/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {

    // SMTP Settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;

    // Gmail
    $mail->Username   = 'quizify1230@gmail.com';

    // Google App Password
    $mail->Password   = 'ukyh jyok xkya trhe';

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Sender
    $mail->setFrom(
        'quizify1230@gmail.com',
        'Quizify Payment System'
    );

    // Receiver Email
    $mail->addAddress(
        'quizify1230@gmail.com'
    );

    // Email Content
    $mail->isHTML(true);

    $mail->Subject = 'Quizify Test Mail';

    $mail->Body = '
        <h2>PHPMailer Working Successfully ✅</h2>
        <p>This is a test email from Quizify.</p>
    ';

    $mail->send();

    echo "<h2>Email Sent Successfully ✅</h2>";

} catch (Exception $e) {

    echo "<h2>Mailer Error:</h2>";
    echo $mail->ErrorInfo;
}
?>