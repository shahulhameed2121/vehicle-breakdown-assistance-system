<?php
include('config/db.php');

$name  = "Shahul Hameed";
$email = "shahulhameeddev@gmail.com";
$plain = "shahul123";

$hash = password_hash($plain, PASSWORD_DEFAULT);

// Check already exists
$check = $pdo->prepare("SELECT id FROM tbl_admin WHERE email=?");
$check->execute([$email]);

if ($check->fetch()) {
    echo "Admin already exists with this email.";
    exit();
}

$stmt = $pdo->prepare("INSERT INTO tbl_admin (name, email, password) VALUES (?,?,?)");
$stmt->execute([$name, $email, $hash]);

echo "✅ Admin created successfully!<br>";
echo "Email: $email<br>";
echo "Password: $plain<br>";
?>
