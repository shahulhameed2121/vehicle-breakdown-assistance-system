<?php
session_start();

/* Clear only driver session */
unset($_SESSION['driver_id']);
unset($_SESSION['driver_name']);

/* Optional: destroy everything */
session_destroy();

/* Redirect to driver login */
header("Location: login.php");
exit();
