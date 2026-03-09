<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587); // TLS
define('MAIL_USERNAME', 'shahulhameeddev76@gmail.com');
define('MAIL_PASSWORD', 'nmhk utsp cauu ssbp');
define('MAIL_FROM', 'shahulhameeddev76@gmail.com');
define('MAIL_FROM_NAME', 'Roadside Assistance');

// Admin receives notifications here (can be same Gmail)
define('ADMIN_EMAIL', 'shahulhameeddev76@gmail.com');

function send_mail($to, $subject, $htmlBody, $plainBody = '')
{
    try {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;

        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = $plainBody ?: strip_tags($htmlBody);

        $mail->send();
        return true;

    } catch (Exception $e) {
        if (!is_dir(__DIR__ . '/../logs')) mkdir(__DIR__ . '/../logs', 0777, true);
        error_log("Mail Error: " . $mail->ErrorInfo . PHP_EOL, 3, __DIR__ . '/../logs/mail_error.log');
        return false;
    }
}