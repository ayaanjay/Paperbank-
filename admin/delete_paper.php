<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: papers.php');
    exit();
}

$id = intval($_GET['id']);

// fetch file path
$res = $conn->query("SELECT file_path FROM papers WHERE id=$id");
if ($res && $row = $res->fetch_assoc()) {
    $path = '../uploads/papers/' . $row['file_path'];
    if (file_exists($path)) {
        @unlink($path);
    }
    $conn->query("DELETE FROM papers WHERE id=$id");
    $_SESSION['message'] = ['text' => 'Paper deleted.','type'=>'success'];
}
header('Location: papers.php');
exit();
?>