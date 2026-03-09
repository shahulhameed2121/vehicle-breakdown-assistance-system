<?php
session_start();
include('../config/db.php');

if(!isset($_SESSION['driver_id'])) {
    header("Location: login.php");
    exit();
}

$driver_id = $_SESSION['driver_id'];

$totalAssigned = $pdo->prepare("SELECT COUNT(*) FROM tbl_booking WHERE driver_id=?");
$totalAssigned->execute([$driver_id]);
$totalAssigned = $totalAssigned->fetchColumn();

$inProgress = $pdo->prepare("SELECT COUNT(*) FROM tbl_booking WHERE driver_id=? AND status='inprogress'");
$inProgress->execute([$driver_id]);
$inProgress = $inProgress->fetchColumn();

$completed = $pdo->prepare("SELECT COUNT(*) FROM tbl_booking WHERE driver_id=? AND status='completed'");
$completed->execute([$driver_id]);
$completed = $completed->fetchColumn();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Driver Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif;}
:root{
  --bg1:#0f172a;--bg2:#1e293b;--card:rgba(255,255,255,0.10);
  --border:rgba(255,255,255,0.18);--text:#fff;--muted:rgba(255,255,255,0.85);
  --shadow:0 20px 40px rgba(0,0,0,0.35);
}
body.light{
  --bg1:#e8f0ff;--bg2:#f7fbff;--card:rgba(0,0,0,0.05);
  --border:rgba(0,0,0,0.15);--text:#0f172a;--muted:rgba(15,23,42,0.75);
  --shadow:0 20px 40px rgba(2,6,23,0.12);
}
body{
  min-height:100vh; padding:22px;
  background:linear-gradient(-45deg,var(--bg1),var(--bg2),var(--bg1),var(--bg2));
  background-size:400% 400%; animation:grad 10s ease infinite;
  color:var(--text);
}
@keyframes grad{0%{background-position:0% 50%}50%{background-position:100% 50%}100%{background-position:0% 50%}}

.toggle{
  position:fixed; top:14px; right:18px;
  padding:8px 12px; border-radius:999px;
  background:var(--card); border:1px solid var(--border);
  backdrop-filter:blur(14px); box-shadow:var(--shadow);
  cursor:pointer; user-select:none; font-weight:700; color:var(--muted);
}
.wrapper{max-width:1050px;margin:0 auto;}
.header{
  margin-top:36px;
  display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;
}
.title h2{font-size:22px;}
.title p{margin-top:6px;color:var(--muted);font-size:14px;}
.nav{
  display:flex; gap:10px; flex-wrap:wrap;
}
.nav a{
  text-decoration:none; color:#fff; font-weight:700;
  padding:10px 14px; border-radius:999px;
  background:rgba(255,255,255,0.12);
  border:1px solid var(--border);
  transition:.25s ease;
}
body.light .nav a{color:var(--text); background:rgba(0,0,0,0.05);}
.nav a:hover{transform:translateY(-2px); box-shadow:0 12px 25px rgba(0,0,0,0.25);}

.grid{
  margin-top:18px;
  display:grid;
  grid-template-columns:repeat(12,1fr);
  gap:14px;
}
.card{
  grid-column: span 4;
  background:var(--card);
  border:1px solid var(--border);
  border-radius:18px;
  padding:18px;
  backdrop-filter:blur(14px);
  box-shadow:var(--shadow);
  animation:fadeIn 0.9s ease;
}
@keyframes fadeIn{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}
.card h3{font-size:14px;color:var(--muted);font-weight:700;}
.card .num{margin-top:10px;font-size:30px;font-weight:900;letter-spacing:.4px;}
.badge{
  margin-top:10px; display:inline-block;
  padding:6px 10px; border-radius:999px; font-size:12px; font-weight:800;
  border:1px solid var(--border);
}
.b1{background:linear-gradient(45deg,#1d976c,#93f9b9);}
.b2{background:linear-gradient(45deg,#00c6ff,#0072ff);}
.b3{background:linear-gradient(45deg,#f59e0b,#f97316);}

@media(max-width:900px){ .card{grid-column: span 6;} }
@media(max-width:520px){ .card{grid-column: span 12;} .title h2{font-size:20px;} }
</style>
</head>

<body>
<div class="toggle" onclick="toggleTheme()">🌙 Toggle Mode</div>

<div class="wrapper">
  <div class="header">
    <div class="title">
      <h2>Welcome, <?php echo htmlspecialchars($_SESSION['driver_name']); ?> (Driver)</h2>
      <p>View your assigned requests and update status.</p>
    </div>

    <div class="nav">
      <a href="assigned.php">Assigned Requests</a>
      <a href="location_tracker.php">📍 Start Live Tracking</a><br>
      <a href="logout.php">Logout</a>
    </div>
  </div>

  <div class="grid">
    <div class="card">
      <h3>Total Assigned</h3>
      <div class="num"><?php echo $totalAssigned; ?></div>
      <span class="badge b2">Your tasks</span>
    </div>

    <div class="card">
      <h3>In Progress</h3>
      <div class="num"><?php echo $inProgress; ?></div>
      <span class="badge b3">On the way</span>
    </div>

    <div class="card">
      <h3>Completed</h3>
      <div class="num"><?php echo $completed; ?></div>
      <span class="badge b1">Done</span>
    </div>
  </div>
</div>

<script>
function toggleTheme(){
  document.body.classList.toggle('light');
  localStorage.setItem("theme", document.body.classList.contains("light") ? "light" : "dark");
}
window.onload = function(){
  if(localStorage.getItem("theme") === "light"){ document.body.classList.add("light"); }
}
</script>
</body>
</html>
