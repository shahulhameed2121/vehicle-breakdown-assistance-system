<?php
include('../config/db.php');

// ✅ Email system
require_once __DIR__ . '/../config/mailer.php';

if(isset($_POST['submit'])) {

    $booking_number = "VB" . rand(10000,99999);

    $name    = trim($_POST['name'] ?? '');
    $mobile  = trim($_POST['mobile'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $vehicle = trim($_POST['vehicle'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $problem = trim($_POST['problem'] ?? '');

    $latitude  = $_POST['latitude'] ?? null;
    $longitude = $_POST['longitude'] ?? null;

    // ✅ Insert
    $stmt = $pdo->prepare("INSERT INTO tbl_booking
        (booking_number, customer_name, mobile, email, vehicle_type, breakdown_address, problem_description, status, latitude, longitude)
        VALUES (?,?,?,?,?,?,?, 'new', ?, ?)");

    $stmt->execute([
        $booking_number,
        $name,
        $mobile,
        $email,
        $vehicle,
        $address,
        $problem,
        $latitude,
        $longitude
    ]);

    // ✅ Email to Admin
    $subA = "🚗 New Breakdown Request - $booking_number";
    $bodyA = "
      <h3>New Breakdown Request</h3>
      <p><b>Booking:</b> {$booking_number}</p>
      <p><b>Name:</b> ".htmlspecialchars($name)."</p>
      <p><b>Mobile:</b> ".htmlspecialchars($mobile)."</p>
      <p><b>Email:</b> ".htmlspecialchars($email)."</p>
      <p><b>Vehicle:</b> ".htmlspecialchars($vehicle)."</p>
      <p><b>Address:</b><br>".nl2br(htmlspecialchars($address))."</p>
      <p><b>Problem:</b><br>".nl2br(htmlspecialchars($problem))."</p>
      <p><b>Location:</b> ".htmlspecialchars((string)$latitude).", ".htmlspecialchars((string)$longitude)."</p>
    ";
    send_mail(ADMIN_EMAIL, $subA, $bodyA);

    // ✅ Email to User (confirmation)
    if(!empty($email)){
        $subU = "✅ Request Received - $booking_number";
        $bodyU = "
          <h3>Your request is received!</h3>
          <p><b>Booking Number:</b> {$booking_number}</p>
          <p>We will review your request and assign a driver soon.</p>
          <p>Track anytime using Booking Number + Mobile.</p>
        ";
        send_mail($email, $subU, $bodyU);
    }

    ?>
    <!DOCTYPE html>
    <html>
    <head>
      <title>Request Submitted</title>
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <style>
        body{font-family:Segoe UI,sans-serif;background:#0f172a;color:#fff;display:flex;justify-content:center;align-items:center;min-height:100vh;padding:20px}
        .box{max-width:650px;width:100%;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.15);border-radius:18px;padding:22px}
        a{color:#00c6ff;text-decoration:none;font-weight:800}
        .btns{margin-top:14px;display:flex;gap:14px;flex-wrap:wrap}
      </style>
    </head>
    <body>
      <div class="box">
        <h2>✅ Request Submitted Successfully!</h2>
        <p>Your Booking Number: <strong><?php echo htmlspecialchars($booking_number); ?></strong></p>
        <p>We will contact you shortly.</p>

        <div class="btns">
          <a href="index.php">Submit Another Request</a>
          <a href="track_booking.php">Track Booking</a>
          <a href="../index.php">Back to Home</a>
        </div>
      </div>
    </body>
    </html>
    <?php
    exit();
}
?>

<!-- If opened directly -->
<a href="index.php">Go back</a>