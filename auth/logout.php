<?php
session_start();
// use cookie for flash since session will be destroyed
$flashData = ['text' => 'Logged out successfully.', 'type' => 'success'];
setcookie('flash', json_encode($flashData), time() + 60, '/');

session_unset();
session_destroy();
header("Location: login.php");
exit();
?>