<?php
session_start();
header('Content-Type: application/json');
include('../config/db.php');

if(!isset($_SESSION['admin_id'])){
  echo json_encode([]);
  exit();
}

/*
  This joins location + driver + latest assigned booking (optional)
  You can customize as you want.
*/
$sql = "
SELECT 
  dl.driver_id, dl.latitude, dl.longitude, dl.updated_at,
  d.name AS driver_name, d.phone,
  b.booking_number
FROM tbl_driver_location dl
JOIN tbl_driver d ON d.id = dl.driver_id
LEFT JOIN tbl_booking b ON b.driver_id = dl.driver_id AND b.status IN ('assigned','inprogress')
ORDER BY dl.updated_at DESC
";

$rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($rows);