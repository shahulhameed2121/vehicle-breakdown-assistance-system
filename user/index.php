<?php include('../config/db.php'); ?>
<!DOCTYPE html>
<html>
<head>
  <title>Request Vehicle Assistance</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <style>
    *{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif;}

    /* ✅ Theme Variables (Default = Dark) */
    :root{
      --bg:#0f172a;
      --text:#ffffff;
      --card:rgba(255,255,255,.06);
      --border:rgba(255,255,255,.15);
      --input:rgba(255,255,255,.08);
      --muted:rgba(255,255,255,.75);
      --accent:#00c6ff;
      --accent2:#0072ff;
      --shadow:0 12px 40px rgba(0,0,0,.35);
    }

    /* ✅ Light Theme */
    html[data-theme="light"]{
      --bg:#f5f7fb;
      --text:#0f172a;
      --card:rgba(255,255,255,.92);
      --border:rgba(15,23,42,.12);
      --input:rgba(15,23,42,.06);
      --muted:rgba(15,23,42,.70);
      --accent:#2563eb;
      --accent2:#0ea5e9;
      --shadow:0 12px 40px rgba(2,6,23,.10);
    }

    body{
      min-height:100vh;
      display:flex;
      justify-content:center;
      align-items:center;
      padding:20px;
      background:var(--bg);
      color:var(--text);
      transition:background .25s ease, color .25s ease;
    }

    .card{
      width:100%;
      max-width:700px;
      background:var(--card);
      border:1px solid var(--border);
      border-radius:18px;
      padding:22px;
      backdrop-filter:blur(10px);
      box-shadow:var(--shadow);
      transition:background .25s ease, border-color .25s ease;
    }

    h2{margin-bottom:14px;text-align:center}

    label{display:block;margin-top:10px;margin-bottom:6px;font-weight:700}

    input,textarea,select{
      width:100%;
      padding:10px;
      border-radius:10px;
      border:1px solid var(--border);
      background:var(--input);
      color:var(--text);
      outline:none;
      transition:background .25s ease, border-color .25s ease, color .25s ease;
    }

    input::placeholder, textarea::placeholder{color:var(--muted);}

    textarea{min-height:90px;resize:vertical}

    input:focus,textarea:focus{
      border-color:var(--accent);
      box-shadow:0 0 10px rgba(0,198,255,.25);
    }
    html[data-theme="light"] input:focus,
    html[data-theme="light"] textarea:focus{
      box-shadow:0 0 10px rgba(37,99,235,.20);
    }

    .row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    @media(max-width:650px){.row{grid-template-columns:1fr}}

    #map{
      height:320px;
      width:100%;
      border-radius:14px;
      border:1px solid var(--border);
      overflow:hidden;
      margin-top:8px;
    }

    button{
      margin-top:14px;
      width:100%;
      padding:12px;
      border:none;
      border-radius:999px;
      font-weight:800;
      cursor:pointer;
      background:linear-gradient(45deg,var(--accent),var(--accent2));
      color:#fff;
      transition:.2s;
    }
    button:hover{transform:scale(1.02)}

    .links{
      margin-top:14px;
      display:flex;
      justify-content:space-between;
      gap:10px;
      flex-wrap:wrap;
    }
    .links a{color:var(--accent);text-decoration:none;font-weight:800}
    .hint{font-size:13px;color:var(--muted);margin-top:6px}

    /* ✅ Theme Toggle Button */
    .themeToggle{
      position:fixed;
      top:14px;
      right:14px;
      z-index:9999;
      padding:10px 14px;
      border-radius:999px;
      border:1px solid var(--border);
      background:var(--card);
      color:var(--text);
      font-weight:900;
      cursor:pointer;
      user-select:none;
      transition:transform .2s ease;
      box-shadow:var(--shadow);
    }
    .themeToggle:hover{transform:scale(1.03)}
  </style>
</head>

<body>

<!-- ✅ Dark/Light Toggle -->
<div class="themeToggle" id="themeBtn" onclick="toggleTheme()">🌙 Dark</div>

<div class="card">
  <h2>Vehicle Breakdown Assistance Form</h2>

  <form method="POST" action="submit_request.php">
    <div class="row">
      <div>
        <label>Full Name</label>
        <input type="text" name="name" required>
      </div>
      <div>
        <label>Mobile Number</label>
        <input type="text" name="mobile" required placeholder="10-digit mobile">
      </div>
    </div>

    <div class="row">
      <div>
        <label>Email Address</label>
        <input type="email" name="email" required placeholder="example@gmail.com">
      </div>
      <div>
        <label>Vehicle Type</label>
        <input type="text" name="vehicle" required placeholder="Car / Bike / Truck">
      </div>
    </div>

    <label>Breakdown Address</label>
    <input type="text" id="address" name="address" required placeholder="Click on map to select location">

    <div class="hint">Tip: Click on the map to pick the exact breakdown location (Latitude/Longitude saved).</div>

    <input type="hidden" name="latitude" id="latitude">
    <input type="hidden" name="longitude" id="longitude">

    <div id="map"></div>

    <label>Problem Description</label>
    <textarea name="problem" required placeholder="Describe the issue..."></textarea>

    <button type="submit" name="submit">Submit Request</button>
  </form>

  <div class="links">
    <a href="track_booking.php">Track Booking</a>
    <a href="../index.php">Back to Home</a>
  </div>
</div>

<!-- ✅ Your API Key included (SECURITY: restrict to http://localhost/* in Google Cloud) -->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA0XKHmS87Jox4LgEcz5x5tl_fBY3aVhiQ"></script>

<script>
  // ✅ Theme functions
  function applyTheme(theme){
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);

    const btn = document.getElementById('themeBtn');
    if(btn){
      btn.innerHTML = (theme === 'light') ? '☀️ Light' : '🌙 Dark';
    }
  }
  function toggleTheme(){
    const current = document.documentElement.getAttribute('data-theme') || 'dark';
    applyTheme(current === 'dark' ? 'light' : 'dark');
  }
  (function(){
    const saved = localStorage.getItem('theme') || 'dark';
    applyTheme(saved);
  })();

  // ✅ Google Map
  function initMap(){
    const defaultLocation = { lat: 10.7905, lng: 78.7047 }; // Trichy default

    const map = new google.maps.Map(document.getElementById("map"), {
      zoom: 13,
      center: defaultLocation,
    });

    const marker = new google.maps.Marker({
      position: defaultLocation,
      map: map,
      draggable: true
    });

    function setLatLng(lat, lng){
      document.getElementById("latitude").value = lat;
      document.getElementById("longitude").value = lng;
      document.getElementById("address").value = lat + ", " + lng;
    }

    map.addListener("click", (event) => {
      const lat = event.latLng.lat();
      const lng = event.latLng.lng();
      marker.setPosition(event.latLng);
      setLatLng(lat, lng);
    });

    marker.addListener("dragend", (event) => {
      const lat = event.latLng.lat();
      const lng = event.latLng.lng();
      setLatLng(lat, lng);
    });
  }
  window.onload = initMap;
</script>

</body>
</html>