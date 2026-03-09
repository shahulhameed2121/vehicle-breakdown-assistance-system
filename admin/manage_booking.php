<?php
session_start();
include('../config/db.php');
require_once __DIR__ . '/../config/mailer.php';

if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$success = '';
$error = '';

// ✅ Approve
if(isset($_POST['approve'])) {
    $booking_id = (int)$_POST['booking_id'];

    $stmt = $pdo->prepare("UPDATE tbl_booking SET status='approved' WHERE id=?");
    $stmt->execute([$booking_id]);

    $b = $pdo->prepare("SELECT * FROM tbl_booking WHERE id=?");
    $b->execute([$booking_id]);
    $booking = $b->fetch();

    if($booking && !empty($booking['email'])){
        send_mail(
            $booking['email'],
            "✅ Request Approved - {$booking['booking_number']}",
            "<h3>Your request is approved.</h3><p><b>Booking:</b> {$booking['booking_number']}</p><p>We will assign a driver soon.</p>"
        );
    }

    $success = "Booking Approved Successfully!";
}

// ✅ Reject
if(isset($_POST['reject'])) {
    $booking_id = (int)$_POST['booking_id'];

    $stmt = $pdo->prepare("UPDATE tbl_booking SET status='rejected' WHERE id=?");
    $stmt->execute([$booking_id]);

    $b = $pdo->prepare("SELECT * FROM tbl_booking WHERE id=?");
    $b->execute([$booking_id]);
    $booking = $b->fetch();

    if($booking && !empty($booking['email'])){
        send_mail(
            $booking['email'],
            "❌ Request Rejected - {$booking['booking_number']}",
            "<h3>Your request is rejected.</h3><p><b>Booking:</b> {$booking['booking_number']}</p><p>Please contact support or submit a new request.</p>"
        );
    }

    $success = "Booking Rejected Successfully!";
}

// ✅ Assign Driver
if(isset($_POST['assign'])) {
    $booking_id = (int)$_POST['booking_id'];
    $driver_id  = (int)$_POST['driver_id'];

    $stmt = $pdo->prepare("UPDATE tbl_booking SET status='assigned', driver_id=? WHERE id=?");
    $stmt->execute([$driver_id, $booking_id]);

    $b = $pdo->prepare("SELECT * FROM tbl_booking WHERE id=?");
    $b->execute([$booking_id]);
    $booking = $b->fetch();

    $d = $pdo->prepare("SELECT * FROM tbl_driver WHERE id=?");
    $d->execute([$driver_id]);
    $driver = $d->fetch();

    if($booking && $driver){
        send_mail(
            ADMIN_EMAIL,
            "✅ Driver Assigned - {$booking['booking_number']}",
            "
            <h3>Driver Assigned</h3>
            <p><b>Booking:</b> {$booking['booking_number']}</p>
            <p><b>Customer:</b> ".htmlspecialchars($booking['customer_name'])." (".htmlspecialchars($booking['mobile']).")</p>
            <p><b>Driver:</b> ".htmlspecialchars($driver['name'])." (".htmlspecialchars($driver['phone']).")</p>
            <p><b>Vehicle No:</b> ".htmlspecialchars($driver['vehicle_number'])."</p>
            <p><b>Address:</b><br>".nl2br(htmlspecialchars($booking['breakdown_address']))."</p>
            "
        );
    }

    if($booking && $driver && !empty($driver['email'])){
        send_mail(
            $driver['email'],
            "🛠 New Job Assigned - {$booking['booking_number']}",
            "
            <h3>New Job Assigned</h3>
            <p><b>Booking:</b> {$booking['booking_number']}</p>
            <p><b>Customer:</b> ".htmlspecialchars($booking['customer_name'])." (".htmlspecialchars($booking['mobile']).")</p>
            <p><b>Address:</b><br>".nl2br(htmlspecialchars($booking['breakdown_address']))."</p>
            <p><b>Problem:</b><br>".nl2br(htmlspecialchars($booking['problem_description']))."</p>
            "
        );
    }

    if($booking && $driver && !empty($booking['email'])){
        send_mail(
            $booking['email'],
            "✅ Driver Assigned - {$booking['booking_number']}",
            "
            <h3>Driver Assigned</h3>
            <p><b>Booking:</b> {$booking['booking_number']}</p>
            <p><b>Driver:</b> ".htmlspecialchars($driver['name'])."</p>
            <p><b>Driver Phone:</b> ".htmlspecialchars($driver['phone'])."</p>
            <p><b>Vehicle No:</b> ".htmlspecialchars($driver['vehicle_number'])."</p>
            "
        );
    }

    $success = "Driver Assigned Successfully!";
}

$bookings = $pdo->query("SELECT * FROM tbl_booking ORDER BY id DESC")->fetchAll();
$drivers = $pdo->query("SELECT * FROM tbl_driver WHERE status='active' ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Manage Bookings</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <style>
    *{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif;}

    /* ✅ Dark Theme Default */
    :root{
      --bg:#0f172a;
      --text:#ffffff;
      --card:rgba(255,255,255,.06);
      --border:rgba(255,255,255,.15);
      --input:rgba(255,255,255,.08);
      --accent:#00c6ff;
      --accent2:#0072ff;
      --shadow:0 12px 40px rgba(0,0,0,.35);
    }

    /* ✅ Light Theme */
    html[data-theme="light"]{
      --bg:#f5f7fb;
      --text:#0f172a;
      --card:rgba(255,255,255,.95);
      --border:rgba(15,23,42,.12);
      --input:rgba(15,23,42,.06);
      --accent:#2563eb;
      --accent2:#0ea5e9;
      --shadow:0 12px 40px rgba(2,6,23,.10);
    }

    body{
      background:var(--bg);
      color:var(--text);
      padding:18px;
      transition:background .25s ease, color .25s ease;
    }

    h2{margin-bottom:10px}

    .msg{padding:10px 12px;border-radius:12px;margin:10px 0;font-weight:800}
    .ok{background:rgba(34,197,94,.18);border:1px solid rgba(34,197,94,.35)}
    .bad{background:rgba(239,68,68,.18);border:1px solid rgba(239,68,68,.35)}

    table{
      width:100%;
      border-collapse:collapse;
      background:var(--card);
      border:1px solid var(--border);
      border-radius:14px;
      overflow:hidden;
      box-shadow:var(--shadow);
      transition:background .25s ease, border-color .25s ease;
    }

    th,td{
      padding:10px;
      border-bottom:1px solid var(--border);
      vertical-align:top
    }

    th{
      background:rgba(255,255,255,.08);
      text-align:left
    }

    select,button{
      padding:8px 10px;
      border-radius:10px;
      border:1px solid var(--border);
      background:var(--input);
      color:var(--text)
    }

    button{
      cursor:pointer;
      font-weight:900;
      border:none;
      background:linear-gradient(45deg,var(--accent),var(--accent2));
      color:#fff;
    }

    .actions form{display:inline-block;margin-right:6px;margin-top:6px}

    .badge{
      display:inline-block;
      padding:5px 10px;
      border-radius:999px;
      font-weight:900;
      font-size:12px;
      border:1px solid var(--border)
    }
    .b-new{background:rgba(59,130,246,.25)}
    .b-approved{background:rgba(34,197,94,.22)}
    .b-assigned{background:rgba(249,115,22,.22)}
    .b-inprogress{background:rgba(168,85,247,.22)}
    .b-completed{background:rgba(16,185,129,.22)}
    .b-rejected{background:rgba(239,68,68,.22)}

    a{color:var(--accent);text-decoration:none;font-weight:900}

    /* ✅ Theme Toggle Button */
    .themeToggle{
      position:fixed;
      top:14px;
      right:14px;
      padding:10px 14px;
      border-radius:999px;
      border:1px solid var(--border);
      background:var(--card);
      color:var(--text);
      font-weight:900;
      cursor:pointer;
      box-shadow:var(--shadow);
      user-select:none;
    }
  </style>
</head>
<body>

<div class="themeToggle" id="themeBtn" onclick="toggleTheme()">🌙 Dark</div>

<h2>Manage Bookings</h2>

<?php if($success): ?><div class="msg ok"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
<?php if($error): ?><div class="msg bad"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

<table>
  <tr>
    <th>ID</th>
    <th>Booking</th>
    <th>Customer</th>
    <th>Mobile</th>
    <th>Status</th>
    <th>Assign Driver</th>
    <th>Actions</th>
  </tr>

  <?php foreach($bookings as $booking): ?>
    <tr>
      <td><?php echo (int)$booking['id']; ?></td>
      <td><?php echo htmlspecialchars($booking['booking_number']); ?></td>
      <td><?php echo htmlspecialchars($booking['customer_name']); ?></td>
      <td><?php echo htmlspecialchars($booking['mobile']); ?></td>
      <td>
        <?php
          $s = $booking['status'];
          $cls = 'badge ';
          if($s==='new') $cls.='b-new';
          elseif($s==='approved') $cls.='b-approved';
          elseif($s==='assigned') $cls.='b-assigned';
          elseif($s==='inprogress') $cls.='b-inprogress';
          elseif($s==='completed') $cls.='b-completed';
          elseif($s==='rejected') $cls.='b-rejected';
          else $cls.='b-new';
        ?>
        <span class="<?php echo $cls; ?>"><?php echo htmlspecialchars($s); ?></span>
      </td>

      <td>
        <form method="POST">
          <input type="hidden" name="booking_id" value="<?php echo (int)$booking['id']; ?>">
          <select name="driver_id" required>
            <option value="">-- Select Driver --</option>
            <?php foreach($drivers as $driver): ?>
              <option value="<?php echo (int)$driver['id']; ?>">
                <?php echo htmlspecialchars($driver['name']); ?> (<?php echo htmlspecialchars($driver['phone']); ?>)
              </option>
            <?php endforeach; ?>
          </select>
          <button type="submit" name="assign">Assign</button>
        </form>
      </td>

      <td class="actions">
        <?php if($booking['status'] === 'new'): ?>
          <form method="POST">
            <input type="hidden" name="booking_id" value="<?php echo (int)$booking['id']; ?>">
            <button type="submit" name="approve">Approve</button>
          </form>
          <form method="POST">
            <input type="hidden" name="booking_id" value="<?php echo (int)$booking['id']; ?>">
            <button type="submit" name="reject">Reject</button>
          </form>
        <?php elseif($booking['status'] !== 'rejected' && $booking['status'] !== 'completed'): ?>
          <form method="POST">
            <input type="hidden" name="booking_id" value="<?php echo (int)$booking['id']; ?>">
            <button type="submit" name="reject">Reject</button>
          </form>
        <?php else: ?>
          -
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
</table>

<br>
<a href="dashboard.php">Back</a>

<script>
  function applyTheme(theme){
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
    const btn = document.getElementById('themeBtn');
    btn.innerHTML = (theme === 'light') ? '☀️ Light' : '🌙 Dark';
  }

  function toggleTheme(){
    const current = document.documentElement.getAttribute('data-theme') || 'dark';
    applyTheme(current === 'dark' ? 'light' : 'dark');
  }

  (function(){
    const saved = localStorage.getItem('theme') || 'dark';
    applyTheme(saved);
  })();
</script>

</body>
</html>