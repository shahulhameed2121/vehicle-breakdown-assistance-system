<?php
require_once __DIR__ . '/config/mailer.php';

$ok = send_mail(ADMIN_EMAIL, "✅ Test Mail", "<b>Gmail SMTP Working!</b>");

echo $ok ? "Mail sent successfully" : "Mail failed (check logs/mail_error.log)";