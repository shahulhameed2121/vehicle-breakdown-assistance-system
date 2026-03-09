<?php
session_start();
include('../config/db.php');

if(!isset($_SESSION['admin_id'])) {
  header("Location: login.php");
  exit();
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Live Tracking</title>
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

/* map */
#map{
height:520px;
width:100%;
border-radius:14px;
border:1px solid var(--border);
margin-top:12px;
background:#000;
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

</style>
</head>

<body>


<div class="top">
<h2>📍 Live Driver Location Tracking</h2>
<a href="dashboard.php">Back</a>
<div class="toggle" onclick="toggleTheme()">🌙 Theme</div>

</div>

<div class="hint">
Auto refresh every 10 seconds. You can restrict to assigned/inprogress bookings later.
</div>

<div id="map"></div>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA0XKHmS87Jox4LgEcz5x5tl_fBY3aVhiQ"></script>

<script>

let map;
let markers = {};

function initMap(){

map = new google.maps.Map(document.getElementById("map"), {
zoom: 11,
center: {lat:10.7905, lng:78.7047}
});

fetchLocations();
setInterval(fetchLocations, 10000);

}

async function fetchLocations(){

const res = await fetch("live_tracking_data.php");
const data = await res.json();

data.forEach(d=>{

const pos = {
lat: parseFloat(d.latitude),
lng: parseFloat(d.longitude)
};

const key = d.driver_id;

const title =
`${d.driver_name} (${d.phone})
Updated: ${d.updated_at}
Booking: ${d.booking_number || '-'}`;

if(!markers[key]){

markers[key] = new google.maps.Marker({
position: pos,
map,
title
});

}else{

markers[key].setPosition(pos);
markers[key].setTitle(title);

}

});

}

window.onload = initMap;

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