<?php
session_start();
include("../config/db.php");
include("../includes/csrf.php");

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// APPROVE TEACHER
if (isset($_GET['approve']) && isset($_GET['csrf_token']) && validateCSRFToken('csrf_token')) {
    $id = intval($_GET['approve']);
    $stmt = $conn->prepare("UPDATE users SET status='approved' WHERE id=? AND role='teacher'");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['message'] = ['text' => 'Teacher approved successfully.', 'type' => 'success'];
    }
    $stmt->close();
    header("Location: manage_users.php");
    exit();
}

// DELETE USER - with CSRF protection
if (isset($_POST['delete_user'])) {
    if (!validateCSRFToken()) {
        $_SESSION['message'] = ['text' => 'Security validation failed.', 'type' => 'error'];
        header("Location: manage_users.php");
        exit();
    }
    
    $id = intval($_POST['user_id']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['message'] = ['text' => 'User deleted successfully.', 'type' => 'success'];
    }
    $stmt->close();
    header("Location: manage_users.php");
    exit();
}

// Get all teachers with stats
$result = $conn->query("
    SELECT id, full_name, email, status, role, created_at, 
           (SELECT COUNT(*) FROM papers WHERE teacher_id = users.id) as papers_count 
    FROM users 
    WHERE role='teacher' 
    ORDER BY created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users - PaperBank Admin</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .user-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-card h3 {
            margin: 0;
            font-size: 32px;
        }
        .stat-card p {
            margin: 5px 0 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 25px;
            border: 1px solid #888;
            border-radius: 8px;
            width: 90%;
            max-width: 450px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .modal-close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .modal-close:hover {
            color: #000;
        }
    </style>
    <script>
        function showDeleteModal(userId) {
            document.getElementById('deleteModal').style.display = 'block';
            document.getElementById('deleteUserId').value = userId;
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }
        
        window.onclick = function(event) {
            var modal = document.getElementById('deleteModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</head>
<body>
<?php include("../includes/navbar.php"); ?>

<div class="page-container">
    <a href="dashboard.php" class="btn btn-secondary">⬅ Back</a>
    <h2 class="page-title" style="margin-top:10px;">Manage Teachers</h2>
    
    <?php include_once("../includes/flash.php"); ?>
    
    <?php
    // Calculate stats
    $total_teachers = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='teacher'")->fetch_assoc()['count'];
    $approved_teachers = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='teacher' AND status='approved'")->fetch_assoc()['count'];
    $pending_teachers = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='teacher' AND status='pending'")->fetch_assoc()['count'];
    ?>
    
    <div class="user-stats">
        <div class="stat-card">
            <h3><?php echo $total_teachers; ?></h3>
            <p>Total Teachers</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $approved_teachers; ?></h3>
            <p>Approved</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $pending_teachers; ?></h3>
            <p>Pending Review</p>
        </div>
    </div>
    
    <div style="width: 100%;">
        <table class="table">
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Papers</th>
                <th>Status</th>
                <th>Joined</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo $row['papers_count']; ?></td>
                <td>
                    <span style="
                        padding: 4px 8px;
                        border-radius: 4px;
                        font-size: 12px;
                        font-weight: bold;
                        <?php echo $row['status'] === 'approved' ? 'background: #d4edda; color: #155724;' : 'background: #fff3cd; color: #856404;'; ?>
                    ">
                        <?php echo ucfirst($row['status']); ?>
                    </span>
                </td>
                <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                <td style="display: flex; gap: 5px; flex-wrap: nowrap; align-items: center;">
                    <a href="edit_user.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                    <?php if ($row['status'] === 'pending'): ?>
                        <a href="manage_users.php?approve=<?php echo $row['id']; ?>&csrf_token=<?php echo getCSRFToken(); ?>" class="btn btn-success btn-sm">Approve</a>
                    <?php endif; ?>
                    <button class="btn btn-danger btn-sm" onclick="showDeleteModal(<?php echo $row['id']; ?>)">Delete</button>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeDeleteModal()">&times;</span>
        <h2 style="margin-top: 0;">Confirm Delete</h2>
        <p>Are you sure you want to delete this teacher account? This action cannot be undone.</p>
        <form method="POST" action="manage_users.php" style="display: flex; gap: 10px; justify-content: flex-end;">
            <?php echo csrfField(); ?>
            <input type="hidden" name="delete_user" value="1">
            <input type="hidden" id="deleteUserId" name="user_id" value="">
            <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
            <button type="submit" class="btn btn-danger">Delete</button>
        </form>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
</body>
</html>