<?php
session_start();
include('../config/db.php');

if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if(isset($_POST['add_driver'])) {

    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $vehicle = $_POST['vehicle'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO tbl_driver (name, phone, vehicle_number, password) VALUES (?,?,?,?)");
    $stmt->execute([$name, $phone, $vehicle, $password]);

    $success = "Driver Added Successfully!";
}

$drivers = $pdo->query("SELECT * FROM tbl_driver")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Drivers</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif;}

:root{
  --bg1:#0f172a;--bg2:#1e293b;
  --card:rgba(255,255,255,0.1);
  --border:rgba(255,255,255,0.2);
  --text:#fff;
  --input-bg:rgba(255,255,255,0.15);
}

body.light{
  --bg1:#e8f0ff;--bg2:#f7fbff;
  --card:rgba(0,0,0,0.05);
  --border:rgba(0,0,0,0.15);
  --text:#0f172a;
  --input-bg:rgba(0,0,0,0.05);
}

body{
  min-height:100vh;
  padding:30px;
  background:linear-gradient(-45deg,var(--bg1),var(--bg2),var(--bg1),var(--bg2));
  background-size:400% 400%;
  animation:gradient 10s ease infinite;
  color:var(--text);
}

@keyframes gradient{
  0%{background-position:0% 50%;}
  50%{background-position:100% 50%;}
  100%{background-position:0% 50%;}
}

.toggle{
  position:fixed;
  top:15px;
  right:20px;
  cursor:pointer;
  padding:8px 12px;
  border-radius:20px;
  background:var(--card);
  border:1px solid var(--border);
}

.container{
  max-width:1000px;
  margin:auto;
}

.card{
  background:var(--card);
  padding:25px;
  border-radius:20px;
  border:1px solid var(--border);
  backdrop-filter:blur(15px);
  box-shadow:0 20px 40px rgba(0,0,0,0.3);
  margin-bottom:25px;
  animation:fadeIn 1s ease;
}

@keyframes fadeIn{
  from{opacity:0;transform:translateY(20px);}
  to{opacity:1;transform:translateY(0);}
}

h2,h3{
  margin-bottom:15px;
}

input{
  width:100%;
  padding:10px;
  border-radius:10px;
  border:1px solid var(--border);
  background:var(--input-bg);
  color:var(--text);
  margin-bottom:10px;
  outline:none;
}

input:focus{
  border-color:#00c6ff;
  box-shadow:0 0 8px rgba(0,198,255,0.6);
}

button{
  padding:10px 20px;
  border:none;
  border-radius:25px;
  font-weight:bold;
  cursor:pointer;
  background:linear-gradient(45deg,#00c6ff,#0072ff);
  color:white;
  transition:0.3s ease;
}

button:hover{
  transform:scale(1.05);
}

.success{
  color:#00ff88;
  margin-bottom:10px;
}

table{
  width:100%;
  border-collapse:collapse;
  margin-top:10px;
}

table th, table td{
  padding:12px;
  text-align:left;
}

table th{
  background:rgba(255,255,255,0.15);
}

table tr:nth-child(even){
  background:rgba(255,255,255,0.05);
}

table tr:hover{
  background:rgba(255,255,255,0.15);
}

.back{
  display:inline-block;
  margin-top:15px;
  text-decoration:none;
  color:#00c6ff;
  font-weight:bold;
}
</style>
</head>

<body>

<div class="toggle" onclick="toggleTheme()">🌙 Toggle Mode</div>

<div class="container">

<div class="card">
<h2>Manage Drivers</h2>

<?php if(isset($success)) echo "<div class='success'>$success</div>"; ?>

<form method="POST">
    <input type="text" name="name" placeholder="Driver Name" required>
    <input type="text" name="phone" placeholder="Phone Number" required>
    <input type="text" name="vehicle" placeholder="Vehicle Number" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit" name="add_driver">Add Driver</button>
</form>
</div>

<div class="card">
<h3>Driver List</h3>

<table>
<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Phone</th>
    <th>Vehicle</th>
</tr>

<?php foreach($drivers as $driver): ?>
<tr>
    <td><?php echo $driver['id']; ?></td>
    <td><?php echo $driver['name']; ?></td>
    <td><?php echo $driver['phone']; ?></td>
    <td><?php echo $driver['vehicle_number']; ?></td>
</tr>
<?php endforeach; ?>

</table>

<a href="dashboard.php" class="back">← Back to Dashboard</a>

</div>

</div>

<script>
function toggleTheme(){
    document.body.classList.toggle('light');
    localStorage.setItem("theme",
        document.body.classList.contains("light") ? "light" : "dark"
    );
}

window.onload = function(){
    if(localStorage.getItem("theme") === "light"){
        document.body.classList.add("light");
    }
}
</script>

</body>
</html>
