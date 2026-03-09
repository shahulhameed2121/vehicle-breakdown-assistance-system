<?php
session_start();
include('../config/db.php');

if(!isset($_SESSION['driver_id'])) {
  header("Location: login.php");
  exit();
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Live Location Tracker</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    *{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Segoe UI',sans-serif;
}

/* Dark theme default */
:root{
--bg:#0f172a;
--card:rgba(255,255,255,.06);
--border:rgba(255,255,255,.12);
--text:#ffffff;
--muted:rgba(255,255,255,.75);
--accent:#00c6ff;
}

/* Light theme */
body.light{
--bg:#f5f7fb;
--card:rgba(0,0,0,.04);
--border:rgba(0,0,0,.12);
--text:#0f172a;
--muted:rgba(15,23,42,.75);
--accent:#2563eb;
}

body{
background:var(--bg);
color:var(--text);
padding:18px;
transition:.25s ease;
}

/* header */
.top{
display:flex;
justify-content:space-between;
align-items:center;
gap:10px;
flex-wrap:wrap;
}
a{
color:var(--accent);
text-decoration:none;
font-weight:900;
}

/* hint */
.hint{
opacity:.85;
font-size:13px;
margin-top:8px;
}

/* theme toggle */
.toggle{
padding:8px 14px;
border-radius:999px;
background:var(--card);
border:1px solid var(--border);
cursor:pointer;
font-weight:800;
}

   /*body{font-family:Segoe UI,sans-serif;background:#0f172a;color:#fff;padding:18px}*/
    .box{max-width:720px;margin:auto;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);border-radius:16px;padding:16px}
    .ok{margin-top:10px;padding:10px;border-radius:12px;background:rgba(34,197,94,.18);border:1px solid rgba(34,197,94,.35)}
    .bad{margin-top:10px;padding:10px;border-radius:12px;background:rgba(239,68,68,.18);border:1px solid rgba(239,68,68,.35)}
    button{padding:10px 14px;border:none;border-radius:999px;font-weight:900;cursor:pointer;background:linear-gradient(45deg,#00c6ff,#0072ff);color:#fff}
    a{color:#00c6ff;text-decoration:none;font-weight:900}
  </style>
</head>
<body>
    <div class="top">
    <div class="toggle" onclick="toggleTheme()">🌙 Theme</div>
     <a href="dashboard.php">Back</a>
    </div>

  <div class="box">
    <h2>📍 Live Location Tracking</h2>
    <p>Keep this page open while you travel. Admin can see your live location.</p>

    <button onclick="startTracking()">Start Tracking</button>
    <button onclick="stopTracking()">Stop Tracking</button>

    <div id="msg" class="ok" style="display:none;"></div>
    <div id="err" class="bad" style="display:none;"></div>

    <br><br>
    
  </div>

<script>
let watchId = null;
let timerId = null;

function showMsg(txt){
  const m=document.getElementById('msg');
  const e=document.getElementById('err');
  e.style.display='none';
  m.style.display='block';
  m.innerText=txt;
}
function showErr(txt){
  const m=document.getElementById('msg');
  const e=document.getElementById('err');
  m.style.display='none';
  e.style.display='block';
  e.innerText=txt;
}

async function sendLocation(lat,lng,acc){
  try{
    const res = await fetch("location_update.php",{
      method:"POST",
      headers:{ "Content-Type":"application/x-www-form-urlencoded" },
      body:`latitude=${encodeURIComponent(lat)}&longitude=${encodeURIComponent(lng)}&accuracy=${encodeURIComponent(acc)}`
    });
    const data = await res.json();
    if(data.ok){
      showMsg(`✅ Updated: ${lat}, ${lng} (Accuracy: ${acc}m)`);
    } else {
      showErr("❌ Server error: " + (data.error || 'unknown'));
    }
  }catch(err){
    showErr("❌ Network error: " + err.message);
  }
}

function startTracking(){
  if(!navigator.geolocation){
    showErr("Geolocation not supported in this browser.");
    return;
  }

  // NOTE: Geolocation works best on HTTPS; localhost is OK.
  watchId = navigator.geolocation.watchPosition(
    (pos)=>{
      const lat = pos.coords.latitude;
      const lng = pos.coords.longitude;
      const acc = pos.coords.accuracy;
      sendLocation(lat,lng,acc);
    },
    (error)=>{
      showErr("Location permission error: " + error.message);
    },
    { enableHighAccuracy:true, maximumAge:3000, timeout:10000 }
  );

  showMsg("📡 Tracking started...");
}

function stopTracking(){
  if(watchId !== null){
    navigator.geolocation.clearWatch(watchId);
    watchId = null;
  }
  showMsg("🛑 Tracking stopped.");
}

/* Theme Toggle */

function toggleTheme(){
document.body.classList.toggle("light");

localStorage.setItem(
"theme",
document.body.classList.contains("light") ? "light" : "dark"
);
}

window.onload = function(){

initMap();

if(localStorage.getItem("theme") === "light"){
document.body.classList.add("light");
}

}
</script>

</body>
</html>