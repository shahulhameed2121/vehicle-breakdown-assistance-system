<?php
session_start();
include('../config/db.php');

if(!isset($_SESSION['admin_id'])) {
  header("Location: login.php");
  exit();
}

function countStatus($pdo,$status){
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM tbl_booking WHERE status=?");
  $stmt->execute([$status]);
  return (int)$stmt->fetchColumn();
}

$total = (int)$pdo->query("SELECT COUNT(*) FROM tbl_booking")->fetchColumn();
$new = countStatus($pdo,'new');
$approved = countStatus($pdo,'approved');
$assigned = countStatus($pdo,'assigned');
$inprogress = countStatus($pdo,'inprogress');
$completed = countStatus($pdo,'completed');
$rejected = countStatus($pdo,'rejected');

// last 7 days daily counts
$daily = $pdo->query("
  SELECT DATE(created_at) as d, COUNT(*) as c
  FROM tbl_booking
  WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
  GROUP BY DATE(created_at)
  ORDER BY d ASC
")->fetchAll(PDO::FETCH_ASSOC);

// status distribution
$statusDist = $pdo->query("
  SELECT status, COUNT(*) as c
  FROM tbl_booking
  GROUP BY status
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <title>Admin Statistics</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <style>
    *{margin:0;padding:0;box-sizing:border-box;font-family:Segoe UI,sans-serif}

    /* ✅ Dark Theme Default */
    :root{
      --bg:#0f172a;
      --text:#ffffff;
      --muted:rgba(255,255,255,.75);
      --card:rgba(255,255,255,.06);
      --border:rgba(255,255,255,.12);
      --accent:#00c6ff;
      --shadow:0 12px 40px rgba(0,0,0,.35);
    }

    /* ✅ Light Theme */
    html[data-theme="light"]{
      --bg:#f5f7fb;
      --text:#0f172a;
      --muted:rgba(15,23,42,.70);
      --card:rgba(255,255,255,.95);
      --border:rgba(15,23,42,.12);
      --accent:#2563eb;
      --shadow:0 12px 40px rgba(2,6,23,.10);
    }

    body{
      background:var(--bg);
      color:var(--text);
      padding:18px;
      transition:background .25s ease,color .25s ease;
    }

    a{color:var(--accent);text-decoration:none;font-weight:900}

    .top{
      display:flex;
      justify-content:space-between;
      align-items:center;
      flex-wrap:wrap;
      gap:10px;
      margin-bottom:10px;
    }

    .rightTop{
      display:flex;
      gap:10px;
      align-items:center;
      flex-wrap:wrap;
    }

    .themeToggle{
      padding:10px 14px;
      border-radius:999px;
      border:1px solid var(--border);
      background:var(--card);
      color:var(--text);
      font-weight:1000;
      cursor:pointer;
      box-shadow:var(--shadow);
      user-select:none;
    }

    .grid{
      display:grid;
      grid-template-columns:repeat(3,1fr);
      gap:12px;
      margin-top:12px
    }
    @media(max-width:900px){.grid{grid-template-columns:repeat(2,1fr)}}
    @media(max-width:600px){.grid{grid-template-columns:1fr}}

    .card{
      background:var(--card);
      border:1px solid var(--border);
      border-radius:16px;
      padding:14px;
      box-shadow:var(--shadow);
    }

    .num{font-size:28px;font-weight:1000;margin-top:6px}
    .muted{opacity:.85;color:var(--muted)}

    .charts{
      margin-top:18px;
      display:flex;
      flex-direction:column;
      gap:18px;
    }
    @media(max-width:900px){.charts{grid-template-columns:1fr}}

    canvas{
        background:var(--card);
        border:1px solid var(--border);
        border-radius:16px;
        padding:20px;
        box-shadow:var(--shadow);
        width:100%;
        height:320px;
    }
  </style>
</head>

<body>

<div class="top">
  <h2>📊 Admin Statistics Dashboard</h2>

  <div class="rightTop">
    <a href="dashboard.php">Back</a>
    <span style="opacity:.6;">|</span>
    <a href="live_tracking.php">Live Tracking</a>

    <button class="themeToggle" id="themeBtn" onclick="toggleTheme()">🌙 Dark</button>
  </div>
</div>

<div class="grid">
  <div class="card"><div class="muted">Total Bookings</div><div class="num"><?php echo $total; ?></div></div>
  <div class="card"><div class="muted">New</div><div class="num"><?php echo $new; ?></div></div>
  <div class="card"><div class="muted">Approved</div><div class="num"><?php echo $approved; ?></div></div>
  <div class="card"><div class="muted">Assigned</div><div class="num"><?php echo $assigned; ?></div></div>
  <div class="card"><div class="muted">In Progress</div><div class="num"><?php echo $inprogress; ?></div></div>
  <div class="card"><div class="muted">Completed</div><div class="num"><?php echo $completed; ?></div></div>
  <div class="card"><div class="muted">Rejected</div><div class="num"><?php echo $rejected; ?></div></div>
</div>

<div class="charts">

  <div class="card">
    <h3 class="muted">Bookings Last 7 Days</h3>
    <canvas id="dailyChart"></canvas>
  </div>

  <div class="card">
    <h3 class="muted">Booking Status Distribution</h3>
    <canvas id="statusChart"></canvas>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
/* ✅ Theme */
function applyTheme(theme){
  document.documentElement.setAttribute('data-theme', theme);
  localStorage.setItem('theme', theme);

  const btn = document.getElementById('themeBtn');
  if(btn) btn.innerHTML = (theme === 'light') ? '☀️ Light' : '🌙 Dark';
}
function toggleTheme(){
  const current = document.documentElement.getAttribute('data-theme') || 'dark';
  applyTheme(current === 'dark' ? 'light' : 'dark');
}
(function(){
  const saved = localStorage.getItem('theme') || 'dark';
  applyTheme(saved);
})();

/* ✅ Charts data */
const dailyLabels = <?php echo json_encode(array_map(fn($r)=>$r['d'], $daily)); ?>;
const dailyCounts = <?php echo json_encode(array_map(fn($r)=>(int)$r['c'], $daily)); ?>;

const statusLabels = <?php echo json_encode(array_map(fn($r)=>$r['status'], $statusDist)); ?>;
const statusCounts = <?php echo json_encode(array_map(fn($r)=>(int)$r['c'], $statusDist)); ?>;

// Line chart
new Chart(document.getElementById('dailyChart'), {
  type: 'line',
  data: {
    labels: dailyLabels,
    datasets: [{ label: 'Bookings (Last 7 Days)', data: dailyCounts, tension: 0.3 }]
  },
  options: { responsive: true, plugins: { legend: { display: true } } }
});

// Doughnut chart
new Chart(document.getElementById('statusChart'), {
  type: 'doughnut',
  data: {
    labels: statusLabels,
    datasets: [{ label: 'Status', data: statusCounts }]
  },
  options: { responsive: true }
});
</script>

</body>
</html>