<?php
session_start();
include("../config/db.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Check if teacher needs to change password
$user_id = $_SESSION['user_id'];
$password_check_stmt = $conn->prepare("SELECT password_changed FROM users WHERE id = ?");
if ($password_check_stmt) {
    $password_check_stmt->bind_param("i", $user_id);
    if ($password_check_stmt->execute()) {
        $password_result = $password_check_stmt->get_result();
        $password_data = $password_result->fetch_assoc();
        $password_status = $password_data ? $password_data['password_changed'] : 1;
    } else {
        $password_status = 1; // Default to changed if query fails
    }
    $password_check_stmt->close();
} else {
    $password_status = 1; // Default to changed if column doesn't exist
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/login.css">
    <link rel="stylesheet" href="../assets/css/teacher.css">
</head>
<body>
<?php include("../includes/navbar.php"); ?>

<div class="page-container">
    <h2 class="page-title">Teacher Dashboard</h2>
    <p>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></p>

    <?php if ($password_status == 0): ?>
    <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
        <strong>⚠️ Security Notice:</strong> You are still using the default password. Please <a href="account.php" style="color: #856404; font-weight: bold;">change your password</a> immediately for security.
    </div>
    <?php endif; ?>

    <div class="dashboard-cards">
        <a href="upload.php" class="card">
            <div class="icon">📤</div>
            <div class="label">Upload Paper</div>
        </a>
        <a href="my_papers.php" class="card">
            <div class="icon">📄</div>
            <div class="label">My Uploads</div>
        </a>
        <a href="account.php" class="card">
            <div class="icon">⚙️</div>
            <div class="label">Account Settings</div>
        </a>
        <a href="logout.php" class="card">
            <div class="icon">🚪</div>
            <div class="label">Logout</div>
        </a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>