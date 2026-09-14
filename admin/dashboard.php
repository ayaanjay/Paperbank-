<?php
session_start();
include("../config/db.php");
include("../includes/csrf.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// gather comprehensive metrics
$pending_teachers = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='teacher' AND status='pending'")->fetch_assoc()['c'];
$total_teachers = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='teacher'")->fetch_assoc()['c'];
$approved_papers = $conn->query("SELECT COUNT(*) as c FROM papers WHERE status='approved'")->fetch_assoc()['c'];
$total_papers = $conn->query("SELECT COUNT(*) as c FROM papers")->fetch_assoc()['c'];
$new_papers = $conn->query("SELECT COUNT(*) as c FROM papers WHERE created_at > DATE_SUB(NOW(), INTERVAL 1 DAY)")->fetch_assoc()['c'];

// Category distribution
$papers_by_category = $conn->query("SELECT category, COUNT(*) as count FROM papers GROUP BY category ORDER BY count DESC");

// Grade distribution
$papers_by_grade = $conn->query("SELECT grade, COUNT(*) as count FROM papers GROUP BY grade ORDER BY grade ASC");

// Top teachers
$top_teachers = $conn->query("SELECT u.full_name, u.email, COUNT(p.id) as paper_count FROM users u LEFT JOIN papers p ON u.id = p.teacher_id WHERE u.role='teacher' GROUP BY u.id ORDER BY paper_count DESC LIMIT 5");

// Total storage used
$total_storage = $conn->query("SELECT SUM(file_size) as total FROM papers")->fetch_assoc()['total'];
$total_storage_mb = round($total_storage / (1024 * 1024), 2);

// recent activity
$recent_papers = $conn->query("SELECT title, grade, subject, track, created_at FROM papers ORDER BY created_at DESC LIMIT 5");
$recent_users = $conn->query("SELECT full_name, email, created_at FROM users WHERE role='teacher' ORDER BY created_at DESC LIMIT 5");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .dashboard-metrics {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .metric-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .metric-card h3 {
            margin: 0;
            font-size: 36px;
            font-weight: bold;
        }
        .metric-card p {
            margin: 8px 0 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .dashboard-cards {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .card {
            flex: 1;
            min-width: 200px;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-decoration: none;
            color: #333;
            border: 1px solid #ddd;
            transition: all 0.3s ease;
        }
        .card:hover {
            background: #e9ecef;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .card .icon {
            font-size: 32px;
            margin-bottom: 10px;
        }
        .card .label {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .card .badge {
            display: inline-block;
            background: #ff6b6b;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            margin-top: 8px;
        }
        .distribution-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }
        .distribution-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        .distribution-box h3 {
            margin-top: 0;
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        .distribution-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .distribution-item:last-child {
            border-bottom: none;
        }
        .dist-bar {
            background: #007bff;
            height: 24px;
            border-radius: 4px;
            margin: 5px 0;
        }
        .mt-20 {
            margin-top: 30px;
            padding: 20px;
            background: white;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
<?php include("../includes/navbar.php"); ?>

<div class="page-container">
    <h2 class="page-title">Admin Dashboard</h2>
    <p style="color: #666; margin: 10px 0 20px 0;">Overview and management of PaperBank system</p>

    <!-- Main Metrics -->
    <div class="dashboard-metrics">
        <div class="metric-card">
            <h3><?php echo $total_papers; ?></h3>
            <p>Total Papers</p>
        </div>
        <div class="metric-card">
            <h3><?php echo $approved_papers; ?></h3>
            <p>Published</p>
        </div>
        <div class="metric-card">
            <h3><?php echo $total_teachers; ?></h3>
            <p>Teachers</p>
        </div>
        <div class="metric-card">
            <h3><?php echo $total_storage_mb; ?> MB</h3>
            <p>Storage Used</p>
        </div>
    </div>

    <!-- Quick Action Cards -->
    <div class="dashboard-cards">
        <a href="papers.php" class="card">
            <div class="icon">📄</div>
            <div class="label">All Papers</div>
            <div><?php echo $total_papers; ?> total</div>
        </a>
        <a href="manage_users.php" class="card">
            <div class="icon">👥</div>
            <div class="label">Manage Teachers</div>
            <div><?php echo $total_teachers; ?> total</div>
            <?php if($pending_teachers > 0): ?>
            <div class="badge"><?php echo $pending_teachers; ?> pending</div>
            <?php endif; ?>
        </a>
        <a href="upload.php" class="card">
            <div class="icon">⬆️</div>
            <div class="label">Upload Paper</div>
        </a>
        <a href="subjects.php" class="card">
            <div class="icon">📚</div>
            <div class="label">Manage Subjects</div>
        </a>
        <a href="logout.php" class="card">
            <div class="icon">🚪</div>
            <div class="label">Logout</div>
        </a>
    </div>

    <?php if ($new_papers > 0): ?>
    <div class="flash-message flash-success">
        📄 <?php echo $new_papers; ?> new document(s) have been uploaded in the last 24 hours. <a href="papers.php">View details</a>
    </div>
    <?php endif; ?>

    <!-- Distribution Charts -->
    <div class="distribution-section">
        <div class="distribution-box">
            <h3>Papers by Category</h3>
            <?php
            $max_count = 1;
            $categories = [];
            $papers_by_category = $conn->query("SELECT category, COUNT(*) as count FROM papers GROUP BY category ORDER BY count DESC");
            while($row = $papers_by_category->fetch_assoc()) {
                $categories[] = $row;
                if($row['count'] > $max_count) $max_count = $row['count'];
            }
            
            foreach($categories as $cat):
                $percentage = round(($cat['count'] / $total_papers * 100), 1);
                $width = round(($cat['count'] / $max_count * 100), 0);
            ?>
            <div class="distribution-item">
                <span><strong><?php echo htmlspecialchars($cat['category']); ?></strong></span>
                <span><?php echo $cat['count']; ?> (<?php echo $percentage; ?>%)</span>
            </div>
            <div class="dist-bar" style="width: <?php echo $width; ?>%;"></div>
            <?php endforeach; ?>
        </div>

        <div class="distribution-box">
            <h3>Papers by Grade</h3>
            <?php
            $max_grade = 1;
            $grades = [];
            $papers_by_grade = $conn->query("SELECT grade, COUNT(*) as count FROM papers GROUP BY grade ORDER BY grade ASC");
            while($row = $papers_by_grade->fetch_assoc()) {
                $grades[] = $row;
                if($row['count'] > $max_grade) $max_grade = $row['count'];
            }
            
            foreach($grades as $g):
                $percentage = round(($g['count'] / $total_papers * 100), 1);
                $width = round(($g['count'] / $max_grade * 100), 0);
            ?>
            <div class="distribution-item">
                <span><strong>Grade <?php echo htmlspecialchars($g['grade']); ?></strong></span>
                <span><?php echo $g['count']; ?> (<?php echo $percentage; ?>%)</span>
            </div>
            <div class="dist-bar" style="width: <?php echo $width; ?>%;"></div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Top Teachers -->
    <div class="mt-20">
        <h3>🏆 Top Teachers (by Paper Count)</h3>
        <table class="table">
            <tr><th>Name</th><th>Email</th><th>Papers Uploaded</th></tr>
            <?php while($tt = $top_teachers->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($tt['full_name']); ?></td>
                <td><?php echo htmlspecialchars($tt['email']); ?></td>
                <td><strong><?php echo $tt['paper_count']; ?></strong></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <div class="mt-20">
        <h3>📄 Recent Uploads</h3>
        <table class="table">
            <tr><th>Title</th><th>Grade</th><th>Subject</th><th>Track</th><th>Uploaded</th></tr>
            <?php while($rp = $recent_papers->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($rp['title']); ?></td>
                <td><?php echo htmlspecialchars($rp['grade']); ?></td>
                <td><?php echo htmlspecialchars($rp['subject']); ?></td>
                <td><?php echo htmlspecialchars($rp['track'] ?? '—'); ?></td>
                <td><?php echo date('Y-m-d H:i', strtotime($rp['created_at'])); ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <div class="mt-20">
        <h3>👥 Recent Teachers</h3>
        <table class="table">
            <tr><th>Name</th><th>Email</th><th>Joined</th></tr>
            <?php while($ru = $recent_users->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($ru['full_name']); ?></td>
                <td><?php echo htmlspecialchars($ru['email']); ?></td>
                <td><?php echo date('M d, Y', strtotime($ru['created_at'])); ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
setTimeout(function() {
    var el = document.querySelector('.flash-message');
    if (el) {
        el.classList.add('hide');
        setTimeout(function(){ el.remove(); }, 6);
    }
}, 8);
</script>

</body>
</html>

