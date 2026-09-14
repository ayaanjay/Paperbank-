<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../auth/login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];

$result = mysqli_query($conn, "
    SELECT * FROM papers 
    WHERE teacher_id = $teacher_id
    ORDER BY created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Papers</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/login.css">
    <link rel="stylesheet" href="../assets/css/teacher.css">
</head>
<body>
<?php include("../includes/navbar.php"); ?>

<div class="page-container">
    <a href="dashboard.php" class="btn btn-secondary">⬅ Back</a>
    <h2 class="page-title" style="margin-top:10px;">My Uploaded Papers</h2>

    <div style="width: 100%;">
        <table class="table">
            <tr>
                <th>Title</th>
                <th>Subject</th>
                <th>Grade</th>
                <th>Year</th>
                <th>Category</th>
                <th>Track</th>
                <th>Uploaded</th>
                <th>View</th>
                <th>Edit</th>
                <th>Delete</th>
            </tr>
<?php while ($row = mysqli_fetch_assoc($result)) { ?>
<tr>
    <td><?php echo htmlspecialchars($row['title']); ?></td>
    <td><?php echo htmlspecialchars($row['subject']); ?></td>
    <td><?php echo $row['grade']; ?></td>
    <td><?php echo $row['year']; ?></td>
    <td><?php echo htmlspecialchars($row['category']); ?></td>
    <td><?php echo htmlspecialchars($row['track'] ?? ''); ?></td>
    <td><?php echo date('Y-m-d H:i', strtotime($row['created_at'])); ?></td>
    <td style="padding: 5px;">
        <a href="../view.php?id=<?php echo $row['id']; ?>" target="_blank" class="btn btn-secondary btn-sm" style="display: inline-block; padding: 4px 8px; white-space: nowrap;">View</a>
    </td>
    <td style="padding: 5px;">
        <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm" style="display: inline-block; padding: 4px 8px; white-space: nowrap;">Edit</a>
    </td>
    <td style="padding: 5px;">
        <a href="delete.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this paper?');" class="btn btn-danger btn-sm" style="display: inline-block; padding: 4px 8px; white-space: nowrap;">Delete</a>
    </td>
</tr>
<?php } ?>

</table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

</body>
</html>