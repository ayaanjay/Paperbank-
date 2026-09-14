<?php
session_start();
include("../config/db.php");
include("../includes/csrf.php");

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Get user ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage_users.php");
    exit();
}

$user_id = intval($_GET['id']);

// Fetch user details
$stmt = $conn->prepare("SELECT id, full_name, email, role, status, created_at FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows != 1) {
    header("Location: manage_users.php");
    exit();
}

$user = $result->fetch_assoc();
$stmt->close();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Validate CSRF token
    if (!validateCSRFToken()) {
        $_SESSION['message'] = ['text' => 'Security validation failed. Please try again.', 'type' => 'error'];
        header("Location: edit_user.php?id=" . $user_id);
        exit();
    }
    
    // Update profile
    if (isset($_POST['update_profile'])) {
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'teacher';
        $status = $_POST['status'] ?? 'pending';
        
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
                $update_stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, role = ?, status = ? WHERE id = ?");
                $update_stmt->bind_param("ssssi", $full_name, $email, $role, $status, $user_id);
                
                if ($update_stmt->execute()) {
                    $_SESSION['message'] = ['text' => 'User profile updated successfully.', 'type' => 'success'];
                    $user['full_name'] = $full_name;
                    $user['email'] = $email;
                    $user['role'] = $role;
                    $user['status'] = $status;
                } else {
                    $_SESSION['message'] = ['text' => 'Error updating profile. Please try again.', 'type' => 'error'];
                }
                $update_stmt->close();
            }
            $check_stmt->close();
        }
    }
    
    // Change password
    if (isset($_POST['change_password'])) {
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (strlen($new_password) < 6) {
            $_SESSION['message'] = ['text' => 'Password must be at least 6 characters.', 'type' => 'error'];
        } elseif ($new_password !== $confirm_password) {
            $_SESSION['message'] = ['text' => 'Passwords do not match.', 'type' => 'error'];
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update_stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($update_stmt->execute()) {
                $_SESSION['message'] = ['text' => 'Password changed successfully.', 'type' => 'success'];
            } else {
                $_SESSION['message'] = ['text' => 'Error updating password. Please try again.', 'type' => 'error'];
            }
            $update_stmt->close();
        }
    }
    
    header("Location: edit_user.php?id=" . $user_id);
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User - PaperBank Admin</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .edit-container {
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
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            font-family: Arial, sans-serif;
            box-sizing: border-box;
        }
        .form-group input:focus,
        .form-group select:focus {
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
        .row-2-col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
    </style>
</head>
<body>
<?php include("../includes/navbar.php"); ?>

<div class="edit-container">
    <div style="margin-bottom: 20px;">
        <a href="manage_users.php" class="btn btn-secondary">⬅ Back to Users</a>
    </div>
    
    <h2 class="page-title">Edit User: <?php echo htmlspecialchars($user['full_name']); ?></h2>
    
    <?php include_once("../includes/flash.php"); ?>
    
    <!-- User Profile Section -->
    <div class="form-section">
        <h3>User Profile</h3>
        <div class="info-box">
            <p><strong>User ID:</strong> <?php echo $user['id']; ?> | <strong>Joined:</strong> <?php echo date('M d, Y H:i', strtotime($user['created_at'])); ?></p>
        </div>
        
        <form method="POST" action="edit_user.php?id=<?php echo $user['id']; ?>">
            <?php echo csrfField(); ?>
            <input type="hidden" name="update_profile" value="1">
            
            <div class="row-2-col">
                <div class="form-group">
                    <label for="full_name">Full Name:</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
            </div>
            
            <div class="row-2-col">
                <div class="form-group">
                    <label for="role">Role:</label>
                    <select id="role" name="role" required>
                        <option value="teacher" <?php echo $user['role'] === 'teacher' ? 'selected' : ''; ?>>Teacher</option>
                        <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="status">Account Status:</label>
                    <select id="status" name="status" required>
                        <option value="pending" <?php echo $user['status'] === 'pending' ? 'selected' : ''; ?>>Pending Review</option>
                        <option value="approved" <?php echo $user['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="rejected" <?php echo $user['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </div>
        </form>
    </div>
    
    <!-- Password Change Section -->
    <div class="form-section">
        <h3>Reset Password</h3>
        <div class="info-box">
            <p>Use this form to set a new password for this user account. The user will need to use the new password to log in.</p>
        </div>
        
        <form method="POST" action="edit_user.php?id=<?php echo $user['id']; ?>">
            <?php echo csrfField(); ?>
            <input type="hidden" name="change_password" value="1">
            
            <div class="form-group">
                <label for="new_password">New Password:</label>
                <input type="password" id="new_password" name="new_password" required>
                <small style="color: #666; margin-top: 5px; display: block;">Minimum 6 characters</small>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-danger">Reset Password</button>
            </div>
        </form>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
</body>
</html>
