<?php
session_start();
include("../config/db.php");
include("../includes/csrf.php");

// Validate CSRF token
if (!validateCSRFToken()) {
    $_SESSION['message'] = ['text'=>'Security validation failed. Please login again.','type'=>'error'];
    header('Location: login.php');
    exit();
}

$email = trim($_POST['email']);
$password = $_POST['password'];

// look up user by email
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $user = $result->fetch_assoc();

    if (!password_verify($password, $user['password'])) {
        $_SESSION['message'] = ['text'=>'Incorrect password.','type'=>'error'];
        header('Location: login.php');
        exit();
    }

    if ($user['status'] != 'approved') {
        $_SESSION['message'] = ['text'=>'Your account is not approved yet.','type'=>'error'];
        header('Location: login.php');
        exit();
    }

    // Check if teacher needs to change password from universal password
    if ($user['role'] === 'teacher') {
        // Check if password_changed column exists
        $password_changed_check = isset($user['password_changed']) ? $user['password_changed'] : 1;
        if ($password_changed_check == 0) {
            // First login with universal password - force password change
            session_regenerate_id(true);
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['role']      = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['force_password_change'] = true;

            $_SESSION['message'] = ['text'=>'Welcome! You must change your password before continuing.','type'=>'warning'];
            header("Location: ../teacher/account.php");
            exit();
        }
    }

    // credentials are good; prevent session fixation
    session_regenerate_id(true);
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['role']      = $user['role'];
    $_SESSION['full_name'] = $user['full_name'];

    // redirect with flash message to show styled popup on destination
    $target = $user['role'] === 'admin' ? '../admin/dashboard.php' : '../teacher/dashboard.php';
    $_SESSION['message'] = ['text'=>'Login successful.','type'=>'success'];
    header("Location: $target");
    exit();
} else {
    $_SESSION['message'] = ['text'=>'User not found.','type'=>'error'];
    header('Location: login.php');
    exit();
}

$stmt->close();
$conn->close();
?>