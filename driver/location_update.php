<?php
session_start();
header('Content-Type: application/json');
include('../config/db.php');

if(!isset($_SESSION['driver_id'])){
  echo json_encode(['ok'=>false,'error'=>'not_logged_in']);
  exit();
}

$driver_id = (int)$_SESSION['driver_id'];
$lat = trim($_POST['latitude'] ?? '');
$lng = trim($_POST['longitude'] ?? '');
$acc = trim($_POST['accuracy'] ?? '');

if($lat==='' || $lng===''){
  echo json_encode(['ok'=>false,'error'=>'missing_lat_lng']);
  exit();
}

try{
  // Upsert: insert or update (unique key driver_id)
  $stmt = $pdo->prepare("
    INSERT INTO tbl_driver_location (driver_id, latitude, longitude, accuracy)
    VALUES (?,?,?,?)
    ON DUPLICATE KEY UPDATE
      latitude=VALUES(latitude),
      longitude=VALUES(longitude),
      accuracy=VALUES(accuracy),
      updated_at=CURRENT_TIMESTAMP
  ");
  $stmt->execute([$driver_id, $lat, $lng, $acc]);
  echo json_encode(['ok'=>true]);
}catch(Exception $e){
  echo json_encode(['ok'=>false,'error'=>'db_error']);
}