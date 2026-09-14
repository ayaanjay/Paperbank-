<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$search = '';
if (!empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
}

$where = "WHERE role='teacher'";
if ($search !== '') {
    $searchLower = strtolower($search);
    $where .= " AND (LOWER(full_name) LIKE '%" . $conn->real_escape_string($searchLower) . "%' OR LOWER(email) LIKE '%" . $conn->real_escape_string($searchLower) . "%')";
}

$result = $conn->query("SELECT * FROM users $where ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Teachers</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<?php include("../includes/navbar.php"); ?>

<div class="page-container">
    <h2 class="page-title">Teachers</h2>
    <form method="GET" class="filter-form">
        <input type="text" name="search" placeholder="Name or email" value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Search</button>
    </form>

    <div style="width: 100%;">
        <table class="table">
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo $row['status']; ?></td>
                    <td>
                        <?php if ($row['status']=='pending'): ?>
                            <a href="manage_users.php?approve=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">Approve</a>
                        <?php endif; ?>
                        <a href="manage_users.php?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" style="margin-left:4px;">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
</body>
</html>