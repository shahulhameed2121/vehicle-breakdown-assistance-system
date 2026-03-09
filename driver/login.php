<?php
session_start();
include('../config/db.php');

if(isset($_POST['login'])) {

    $phone = $_POST['phone'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM tbl_driver WHERE phone=? AND status='active'");
    $stmt->execute([$phone]);
    $driver = $stmt->fetch();

    if($driver && password_verify($password, $driver['password'])) {
        $_SESSION['driver_id'] = $driver['id'];
        $_SESSION['driver_name'] = $driver['name'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid Phone or Password!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Driver Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
* {
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI', sans-serif;
}

:root{
    --bg1:#0f172a;
    --bg2:#1e293b;
    --card: rgba(255,255,255,0.1);
    --border: rgba(255,255,255,0.2);
    --text:#ffffff;
    --input-bg: rgba(255,255,255,0.15);
}

body.light{
    --bg1:#e8f0ff;
    --bg2:#f7fbff;
    --card: rgba(0,0,0,0.05);
    --border: rgba(0,0,0,0.15);
    --text:#0f172a;
    --input-bg: rgba(0,0,0,0.05);
}

body{
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
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

.container{
    width:380px;
    padding:40px;
    border-radius:20px;
    background:var(--card);
    border:1px solid var(--border);
    backdrop-filter:blur(15px);
    box-shadow:0 20px 40px rgba(0,0,0,0.3);
    animation:fadeIn 1.2s ease;
}

@keyframes fadeIn{
    from{opacity:0;transform:translateY(20px);}
    to{opacity:1;transform:translateY(0);}
}

h2{
    text-align:center;
    margin-bottom:25px;
}

label{
    font-size:14px;
    display:block;
    margin-bottom:5px;
    margin-top:10px;
}

input{
    width:100%;
    padding:10px;
    border-radius:10px;
    border:1px solid var(--border);
    background:var(--input-bg);
    color:var(--text);
    outline:none;
    transition:0.3s ease;
}

input:focus{
    border-color:#1d976c;
    box-shadow:0 0 8px rgba(29,151,108,0.6);
}

button{
    width:100%;
    padding:12px;
    margin-top:20px;
    border:none;
    border-radius:30px;
    font-weight:bold;
    cursor:pointer;
    background:linear-gradient(45deg,#1d976c,#93f9b9);
    color:white;
    transition:0.3s ease;
}

button:hover{
    transform:scale(1.05);
    box-shadow:0 10px 20px rgba(0,0,0,0.4);
}

.error{
    text-align:center;
    color:#ff4d4d;
    margin-bottom:10px;
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
</style>
</head>

<body>

<div class="toggle" onclick="toggleTheme()">🌙 Toggle Mode</div>

<div class="container">

<h2>Driver Login</h2>

<?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>

<form method="POST">

    <label>Phone Number</label>
    <input type="text" name="phone" required>

    <label>Password</label>
    <input type="password" name="password" required>

    <button type="submit" name="login">Login</button>

</form>
<button type="submit"><a href="../index.php">Back to Home</a></button>
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
