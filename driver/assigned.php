<?php
session_start();
include('../config/db.php');
require_once __DIR__ . '/../config/mailer.php';

if(!isset($_SESSION['driver_id'])) {
    header("Location: login.php");
    exit();
}

$driver_id = (int)$_SESSION['driver_id'];
$success = '';

if(isset($_POST['update_status'])) {

    $booking_id = (int)$_POST['booking_id'];
    $status = $_POST['status'];

    $stmt = $pdo->prepare("UPDATE tbl_booking SET status=? WHERE id=? AND driver_id=?");
    $stmt->execute([$status, $booking_id, $driver_id]);

    $b = $pdo->prepare("SELECT * FROM tbl_booking WHERE id=? AND driver_id=?");
    $b->execute([$booking_id, $driver_id]);
    $booking = $b->fetch();

    if($booking){
        send_mail(
            ADMIN_EMAIL,
            "📌 Status Updated - {$booking['booking_number']}",
            "<h3>Status Updated</h3><p><b>Booking:</b> {$booking['booking_number']}</p><p><b>Status:</b> ".htmlspecialchars($status)."</p>"
        );

        if($status === 'completed' && !empty($booking['email'])){
            send_mail(
                $booking['email'],
                "🟩 Service Completed - {$booking['booking_number']}",
                "<h3>Your service is completed.</h3><p><b>Booking:</b> {$booking['booking_number']}</p><p>Thank you for using our service.</p>"
            );
        }
    }

    $success = "Status Updated Successfully!";
}

$stmt = $pdo->prepare("SELECT * FROM tbl_booking WHERE driver_id=? ORDER BY id DESC");
$stmt->execute([$driver_id]);
$bookings = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
<title>Assigned Requests</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif}

/* DARK THEME DEFAULT */
:root{
--bg:#0f172a;
--text:#ffffff;
--card:rgba(255,255,255,.06);
--border:rgba(255,255,255,.15);
--input:rgba(255,255,255,.08);
--accent:#00c6ff;
--accent2:#0072ff;
}

/* LIGHT THEME */
html[data-theme="light"]{
--bg:#f5f7fb;
--text:#0f172a;
--card:rgba(255,255,255,.95);
--border:rgba(15,23,42,.12);
--input:rgba(15,23,42,.06);
--accent:#2563eb;
--accent2:#0ea5e9;
}

body{
font-family:Segoe UI,sans-serif;
background:var(--bg);
color:var(--text);
padding:18px;
transition:background .25s ease,color .25s ease;
}

h2{margin-bottom:10px}

.msg{
padding:10px 12px;
border-radius:12px;
margin:10px 0;
font-weight:900;
background:rgba(34,197,94,.18);
border:1px solid rgba(34,197,94,.35)
}

table{
width:100%;
border-collapse:collapse;
background:var(--card);
border:1px solid var(--border);
border-radius:14px;
overflow:hidden
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
background:linear-gradient(45deg,var(--accent),var(--accent2));
border:none
}

a{
color:var(--accent);
text-decoration:none;
font-weight:900
}

.mapBtn{
display:inline-block;
margin-top:6px;
padding:8px 12px;
border-radius:999px;
background:linear-gradient(45deg,#ff512f,#dd2476);
color:#fff;
text-decoration:none;
font-weight:900
}

/* THEME BUTTON */
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
cursor:pointer
}
</style>
</head>

<body>

<div class="themeToggle" id="themeBtn" onclick="toggleTheme()">🌙 Dark</div>

<h2>Assigned Booking Requests</h2>

<?php if($success): ?>
<div class="msg"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<table>
<tr>
<th>ID</th>
<th>Booking</th>
<th>Customer</th>
<th>Mobile</th>
<th>Address</th>
<th>Problem</th>
<th>Status</th>
<th>Update</th>
</tr>

<?php foreach($bookings as $booking): ?>
<tr>
<td><?php echo (int)$booking['id']; ?></td>
<td><?php echo htmlspecialchars($booking['booking_number']); ?></td>
<td><?php echo htmlspecialchars($booking['customer_name']); ?></td>
<td><?php echo htmlspecialchars($booking['mobile']); ?></td>
<td>
<?php echo nl2br(htmlspecialchars($booking['breakdown_address'])); ?>

<?php
$maps = "https://www.google.com/maps/search/?api=1&query=" . urlencode($booking['breakdown_address']);
?>

<br>
<a class="mapBtn" target="_blank" href="<?php echo $maps; ?>">📍 Open Maps</a>
</td>

<td><?php echo nl2br(htmlspecialchars($booking['problem_description'])); ?></td>
<td><?php echo htmlspecialchars($booking['status']); ?></td>

<td>
<?php if($booking['status'] !== 'completed' && $booking['status'] !== 'rejected'): ?>
<form method="POST">
<input type="hidden" name="booking_id" value="<?php echo (int)$booking['id']; ?>">
<select name="status" required>
<option value="inprogress" <?php if($booking['status']==='inprogress') echo 'selected'; ?>>In Progress</option>
<option value="completed">Completed</option>
</select>
<button type="submit" name="update_status">Update</button>
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

const btn=document.getElementById('themeBtn');
btn.innerHTML=(theme==="light")?"☀️ Light":"🌙 Dark";
}

function toggleTheme(){
const current=document.documentElement.getAttribute('data-theme')||'dark';
applyTheme(current==='dark'?'light':'dark');
}

(function(){
const saved=localStorage.getItem('theme')||'dark';
applyTheme(saved);
})();
</script>

</body>
</html>