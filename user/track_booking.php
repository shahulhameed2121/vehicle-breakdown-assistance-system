<?php
include('../config/db.php');

$result = null;

if(isset($_POST['track'])){

    $booking_number = trim($_POST['booking_number']);
    $mobile = trim($_POST['mobile']);

    // Join booking with driver
    $stmt = $pdo->prepare("
        SELECT 
            b.*,
            d.name AS driver_name,
            d.phone AS driver_phone,
            d.vehicle_number AS driver_vehicle
        FROM tbl_booking b
        LEFT JOIN tbl_driver d ON b.driver_id = d.id
        WHERE b.booking_number=? AND b.mobile=?
        LIMIT 1
    ");
    $stmt->execute([$booking_number, $mobile]);
    $result = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Track Booking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif;}
:root{
  --bg1:#0f172a;--bg2:#1e293b;--card:rgba(255,255,255,0.1);
  --border:rgba(255,255,255,0.2);--text:#fff;--input-bg:rgba(255,255,255,0.15);
}
body.light{
  --bg1:#e8f0ff;--bg2:#f7fbff;--card:rgba(0,0,0,0.05);
  --border:rgba(0,0,0,0.15);--text:#0f172a;--input-bg:rgba(0,0,0,0.05);
}
body{
  min-height:100vh;display:flex;justify-content:center;align-items:center;padding:20px;
  background:linear-gradient(-45deg,var(--bg1),var(--bg2),var(--bg1),var(--bg2));
  background-size:400% 400%;animation:gradient 10s ease infinite;color:var(--text);
}
a.mapBtn{
  display:inline-block;
  margin-top:10px;
  text-decoration:none;
  color:white;
  font-weight:bold;
  padding:10px 14px;
  border-radius:999px;
  background:linear-gradient(45deg,#ff512f,#dd2476);
  transition:0.3s ease;
}

a.mapBtn:hover{
  transform:scale(1.05);
  box-shadow:0 10px 20px rgba(0,0,0,0.3);
}
@keyframes gradient{0%{background-position:0% 50%}50%{background-position:100% 50%}100%{background-position:0% 50%}}
.toggle{
  position:fixed;top:15px;right:20px;cursor:pointer;padding:8px 12px;border-radius:20px;
  background:var(--card);border:1px solid var(--border);
}
.card{
  width:100%;max-width:520px;background:var(--card);padding:30px;border-radius:20px;
  border:1px solid var(--border);backdrop-filter:blur(15px);
  box-shadow:0 20px 40px rgba(0,0,0,0.3);animation:fadeIn 1s ease;
}
@keyframes fadeIn{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
h2{text-align:center;margin-bottom:18px;}
input{
  width:100%;padding:10px;border-radius:10px;border:1px solid var(--border);
  background:var(--input-bg);color:var(--text);margin-bottom:12px;outline:none;
}
input:focus{border-color:#00c6ff;box-shadow:0 0 8px rgba(0,198,255,0.6);}
button{
  width:100%;padding:10px;border:none;border-radius:25px;font-weight:bold;cursor:pointer;
  background:linear-gradient(45deg,#00c6ff,#0072ff);color:white;transition:0.3s ease;
}
button:hover{transform:scale(1.05);}
.details{margin-top:18px;padding:16px;border-radius:15px;background:rgba(255,255,255,0.08);}
.sectionTitle{margin-top:12px;font-weight:900;}
.badge{
  display:inline-block;margin-left:8px;padding:5px 10px;border-radius:999px;
  border:1px solid var(--border);font-size:12px;font-weight:900;
  background:rgba(255,255,255,0.12);
}
.error{color:#ff4d4d;margin-top:14px;text-align:center;font-weight:700;}
.back{display:block;margin-top:18px;text-align:center;text-decoration:none;color:#00c6ff;font-weight:900;}
a.callBtn{
  display:inline-block;margin-top:10px;text-decoration:none;color:white;font-weight:900;
  padding:10px 14px;border-radius:999px;background:linear-gradient(45deg,#1d976c,#93f9b9);
}
a.callBtn:hover{transform:scale(1.05);}
</style>
</head>

<body>
<div class="toggle" onclick="toggleTheme()">🌙 Toggle Mode</div>

<div class="card">
  <h2>Track Your Booking</h2>

  <form method="POST">
      <input type="text" name="booking_number" placeholder="Booking Number (ex: VB12345)" required>
      <input type="text" name="mobile" placeholder="Mobile Number" required>
      <button type="submit" name="track">Track Booking</button>
  </form>

  <?php if($result): ?>
    <div class="details">
      <p><strong>Booking No:</strong> <?php echo htmlspecialchars($result['booking_number']); ?></p>

      <p><strong>Status:</strong>
        <?php
          $status = $result['status'];
          if($status == 'new') echo "🟦 New";
          elseif($status == 'assigned') echo "🟧 Assigned";
          elseif($status == 'inprogress') echo "🟪 In Progress";
          elseif($status == 'completed') echo "🟩 Completed";
          elseif($status == 'rejected') echo "🟥 Rejected";
          else echo htmlspecialchars($status);
        ?>
      </p>

      <p><strong>Vehicle:</strong> <?php echo htmlspecialchars($result['vehicle_type']); ?></p>
      <p><strong>Problem:</strong> <?php echo htmlspecialchars($result['problem_description']); ?></p>
      <p><strong>Address:</strong> <?php echo htmlspecialchars($result['breakdown_address']); ?></p>
   <?php
$encodedAddress = urlencode($result['breakdown_address']);
$googleMapsUrl = "https://www.google.com/maps/search/?api=1&query=" . $encodedAddress;
?>

<a class="mapBtn" href="<?php echo $googleMapsUrl; ?>" target="_blank">
📍 Open in Google Maps
</a>
      <?php
        // Show driver details only after driver assigned (or later statuses)
        $showDriver = in_array($result['status'], ['assigned','inprogress','completed']);
      ?>
      <?php if($showDriver && !empty($result['driver_id'])): ?>
        <div class="sectionTitle">Driver Details <span class="badge">Available</span></div>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($result['driver_name'] ?? ''); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($result['driver_phone'] ?? ''); ?></p>
        <p><strong>Vehicle No:</strong> <?php echo htmlspecialchars($result['driver_vehicle'] ?? ''); ?></p>

        <?php if(!empty($result['driver_phone'])): ?>
          <a class="callBtn" href="tel:<?php echo htmlspecialchars($result['driver_phone']); ?>">📞 Call Driver</a>
        <?php endif; ?>
      <?php endif; ?>

    </div>
  <?php elseif(isset($_POST['track'])): ?>
    <div class="error">Invalid Booking Number or Mobile Number</div>
  <?php endif; ?>

  <a href="index.php" class="back">← Back</a>
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