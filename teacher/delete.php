<?php
session_start();
include("../config/db.php");

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher'){
    header("Location: ../auth/login.php");
    exit();
}

$id = $_GET['id'];
$teacher_id = $_SESSION['user_id'];

$paper = $conn->query("SELECT file_path, upload_type FROM papers WHERE id=$id AND teacher_id=$teacher_id")->fetch_assoc();

if($paper){
    // Only delete physical file if it's a file upload
    if ($paper['upload_type'] === 'file' && !empty($paper['file_path'])) {
        $file_path = "../uploads/papers/" . $paper['file_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    $conn->query("DELETE FROM papers WHERE id=$id");
    $_SESSION['message'] = ['text'=>'Paper deleted successfully.','type'=>'success'];
} else {
    $_SESSION['message'] = ['text'=>'Paper not found.','type'=>'error'];
}

header("Location: my_papers.php");
?>