<?php
session_start();
include("../config/db.php");
include("../includes/csrf.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Validate CSRF token
    if (!validateCSRFToken()) {
        $_SESSION['message'] = ['text' => 'Security validation failed. Please try again.', 'type' => 'error'];
        header("Location: account.php");
        exit();
    }
    
    // Handle password change
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Fetch current user password_changed status
        $status_stmt = $conn->prepare("SELECT password_changed, password FROM users WHERE id = ?");
        if (!$status_stmt) {
            // Fallback for databases without password_changed column
            $status_stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            if (!$status_stmt) {
                $_SESSION['message'] = ['text' => 'Database error. Please try again.', 'type' => 'error'];
                header("Location: account.php");
                exit();
            }
            $status_stmt->bind_param("i", $user_id);
            if (!$status_stmt->execute()) {
                $_SESSION['message'] = ['text' => 'Database error. Please try again.', 'type' => 'error'];
                $status_stmt->close();
                header("Location: account.php");
                exit();
            }
            $status_result = $status_stmt->get_result();
            $user_status = $status_result->fetch_assoc();
            if (!$user_status) {
                $_SESSION['message'] = ['text' => 'User not found.', 'type' => 'error'];
                $status_stmt->close();
                header("Location: account.php");
                exit();
            }
            // Assume password not changed if column doesn't exist
            $user_status['password_changed'] = 1; // Default to changed
            $status_stmt->close();
        } else {
            $status_stmt->bind_param("i", $user_id);
            if (!$status_stmt->execute()) {
                $_SESSION['message'] = ['text' => 'Database error. Please try again.', 'type' => 'error'];
                $status_stmt->close();
                header("Location: account.php");
                exit();
            }
            $status_result = $status_stmt->get_result();
            $user_status = $status_result->fetch_assoc();
            if (!$user_status) {
                $_SESSION['message'] = ['text' => 'User not found.', 'type' => 'error'];
                $status_stmt->close();
                header("Location: account.php");
                exit();
            }
            $status_stmt->close();
        }

        // For first-time password change, skip current password validation
        $skip_current_validation = ($user_status['password_changed'] == 0);

        if (!$skip_current_validation && !password_verify($current_password, $user_status['password'])) {
            $_SESSION['message'] = ['text' => 'Current password is incorrect.', 'type' => 'error'];
        } elseif (strlen($new_password) < 6) {
            $_SESSION['message'] = ['text' => 'New password must be at least 6 characters.', 'type' => 'error'];
        } elseif ($new_password !== $confirm_password) {
            $_SESSION['message'] = ['text' => 'Passwords do not match.', 'type' => 'error'];
        } else {
            // Update password and mark as changed
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE users SET password = ?, password_changed = 1 WHERE id = ?");
            $update_stmt->bind_param("si", $hashed_password, $user_id);

            if ($update_stmt->execute()) {
                // Clear the force password change flag
                unset($_SESSION['force_password_change']);

                $message_text = $skip_current_validation ?
                    'Password set successfully! You can now access all features.' :
                    'Password changed successfully.';

                $_SESSION['message'] = ['text' => $message_text, 'type' => 'success'];

                // Redirect to dashboard after first password change
                if ($skip_current_validation) {
                    header("Location: dashboard.php");
                    exit();
                }
            } else {
                $_SESSION['message'] = ['text' => 'Error updating password. Please try again.', 'type' => 'error'];
            }
            $update_stmt->close();
        }
    }
    
    // Handle name/email change
    if (isset($_POST['update_profile'])) {
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        // Validate input
        if (empty($full_name)) {
            $_SESSION['message'] = ['text' => 'Name cannot be empty.', 'type' => 'error'];
        } elseif (empty($email)) {
            $_SESSION['message'] = ['text' => 'Email cannot be empty.', 'type' => 'error'];
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['message'] = ['text' => 'Invalid email format.', 'type' => 'error'];
        } else {
            // Check if email already exists (for other users)
            $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $check_stmt->bind_param("si", $email, $user_id);
            $check_stmt->execute();
            
            if ($check_stmt->get_result()->num_rows > 0) {
                $_SESSION['message'] = ['text' => 'This email is already in use.', 'type' => 'error'];
            } else {
                // Update profile
                $update_stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
                $update_stmt->bind_param("ssi", $full_name, $email, $user_id);
                
                if ($update_stmt->execute()) {
                    $_SESSION['full_name'] = $full_name;
                    $_SESSION['message'] = ['text' => 'Profile updated successfully.', 'type' => 'success'];
                } else {
                    $_SESSION['message'] = ['text' => 'Error updating profile. Please try again.', 'type' => 'error'];
                }
                $update_stmt->close();
            }
            $check_stmt->close();
        }
    }
    
    header("Location: account.php");
    exit();
}

// Fetch current user data
$stmt = $conn->prepare("SELECT full_name, email, role, created_at, password_changed FROM users WHERE id = ?");
if (!$stmt) {
    // Fallback for databases without password_changed column
    $stmt = $conn->prepare("SELECT full_name, email, role, created_at FROM users WHERE id = ?");
    if (!$stmt) {
        $_SESSION['message'] = ['text' => 'Database error. Please try again.', 'type' => 'error'];
        header("Location: dashboard.php");
        exit();
    }
    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        $_SESSION['message'] = ['text' => 'Database error. Please try again.', 'type' => 'error'];
        header("Location: dashboard.php");
        exit();
    }
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    if (!$user) {
        $_SESSION['message'] = ['text' => 'User not found.', 'type' => 'error'];
        header("Location: ../auth/logout.php");
        exit();
    }
    // Assume password not changed if column doesn't exist
    $user['password_changed'] = 1;
    $stmt->close();
} else {
    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        $_SESSION['message'] = ['text' => 'Database error. Please try again.', 'type' => 'error'];
        header("Location: dashboard.php");
        exit();
    }
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    if (!$user) {
        $_SESSION['message'] = ['text' => 'User not found.', 'type' => 'error'];
        header("Location: ../auth/logout.php");
        exit();
    }
    $stmt->close();
}

// Check if this is a forced password change
$force_password_change = isset($_SESSION['force_password_change']) && $_SESSION['force_password_change'] === true;
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Account - PaperBank</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/teacher.css">
    <style>
        .account-container {
            max-width: 100%;
            width: 95%;
            margin: 30px auto;
            background: white;
        }
        .form-section {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 25px;
        }
        .form-section h3 {
            color: #333;
            margin-top: 0;
            padding-bottom: 15px;
            border-bottom: 2px solid #007bff;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            font-family: Arial, sans-serif;
            box-sizing: border-box;
        }
        .form-group input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
        }
        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-start;
        }
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .info-box p {
            margin: 0;
            color: #004085;
            font-size: 14px;
        }
    </style>
</head>
<body>
<?php include("../includes/navbar.php"); ?>

<div class="account-container">
    <div style="margin-bottom: 20px;">
        <a href="dashboard.php" class="btn btn-secondary">⬅ Back to Dashboard</a>
    </div>
    
    <h2 class="page-title">My Account</h2>
    
    <?php include_once("../includes/flash.php"); ?>
    
    <!-- Profile Section -->
    <div class="form-section">
        <h3>Profile Information</h3>
        <div class="info-box">
            <p><strong>Role:</strong> <?php echo ucfirst($user['role']); ?></p>
            <p><strong>Member Since:</strong> <?php echo date('M d, Y', strtotime($user['created_at'])); ?></p>
        </div>
        
        <form method="POST" action="account.php">
            <?php echo csrfField(); ?>
            <input type="hidden" name="update_profile" value="1">
            
            <div class="form-group">
                <label for="full_name">Full Name:</label>
                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </div>
        </form>
    </div>
    
    <!-- Password Change Section -->
    <div class="form-section">
        <h3><?php echo $force_password_change ? 'Set Your Password' : 'Change Password'; ?></h3>

        <?php if ($force_password_change): ?>
        <div class="info-box" style="background: #fff3cd; border-left-color: #ffc107;">
            <p><strong>Important:</strong> This is your first login. You must set a new password before you can access other features of the system.</p>
        </div>
        <?php endif; ?>

        <form method="POST" action="account.php">
            <?php echo csrfField(); ?>
            <input type="hidden" name="change_password" value="1">

            <?php if (!$force_password_change): ?>
            <div class="form-group">
                <label for="current_password">Current Password:</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="new_password">New Password:</label>
                <input type="password" id="new_password" name="new_password" required>
                <small style="color: #666; margin-top: 5px; display: block;">Minimum 6 characters</small>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <?php echo $force_password_change ? 'Set Password' : 'Change Password'; ?>
                </button>
                <?php if (!$force_password_change): ?>
                <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
</body>
</html>
