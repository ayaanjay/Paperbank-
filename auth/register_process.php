<?php
session_start();
include("../config/db.php");
include("../includes/csrf.php");

// Validate CSRF token
if (!validateCSRFToken()) {
    $_SESSION['message'] = ['text'=>'Security validation failed. Please try again.','type'=>'error'];
    header('Location: register.php');
    exit();
}

// sanitize inputs
$full_name = trim($_POST['full_name']);
$email     = trim($_POST['email']);
$password  = $_POST['password'];
$role      = 'teacher'; // Only teachers can register

// Validate universal password for teachers
$universal_password = 'paperbank147';
if ($password !== $universal_password) {
    $_SESSION['message'] = ['text'=>'You must use the universal password for registration. Please contact your administrator for the correct password.','type'=>'error'];
    header('Location: register.php');
    exit();
}

// basic duplicate email guard
$check = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$res = $check->get_result();
if ($res->num_rows > 0) {
    $_SESSION['message'] = ['text'=>'This email is already registered.','type'=>'error'];
    header('Location: register.php');
    exit();
}

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// explicitly set status to pending so admin approval is required
// password_changed defaults to 0 (false) for new teachers
$stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role, status, password_changed) VALUES (?, ?, ?, ?, 'pending', 0)");
$stmt->bind_param("ssss", $full_name, $email, $hashed_password, $role);

if ($stmt->execute()) {
    // flash message and stay on register page
    $_SESSION['message'] = ['text'=>'Registration successful. Wait for admin approval; come back later.','type'=>'success'];
    header('Location: register.php');
    exit();
} else {
    $_SESSION['message'] = ['text'=>'Error during registration: ' . $conn->error,'type'=>'error'];
    header('Location: register.php');
    exit();
}

$stmt->close();
$conn->close();
?>