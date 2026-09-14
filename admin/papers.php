<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// filters
$params = [];
$where = "WHERE 1=1";

// Get distinct values for dropdown filters
$teachers = [];
$grades = [];
$subjList = [];
$trackList = [];

$teacherResult = $conn->query("SELECT DISTINCT users.id, users.full_name FROM users JOIN papers ON users.id = papers.teacher_id ORDER BY users.full_name");
if($teacherResult){
    while($t = $teacherResult->fetch_assoc()){
        $teachers[] = $t;
    }
}

$gradeResult = $conn->query("SELECT DISTINCT grade FROM papers ORDER BY grade");
if($gradeResult){
    while($g = $gradeResult->fetch_assoc()){
        $grades[] = $g['grade'];
    }
}

$subjResult = $conn->query("SELECT DISTINCT subject FROM papers ORDER BY subject");
if($subjResult){
    while($s = $subjResult->fetch_assoc()){
        $subjList[] = $s['subject'];
    }
}

$trackResult = $conn->query("SELECT DISTINCT track FROM papers WHERE track IS NOT NULL AND track != '' ORDER BY track");
if($trackResult){
    while($tr = $trackResult->fetch_assoc()){
        $trackList[] = $tr['track'];
    }
}

if (!empty($_GET['grade'])) {
    $grade = $conn->real_escape_string($_GET['grade']);
    $where .= " AND papers.grade='$grade'";
    $params['grade'] = $grade;
}
if (!empty($_GET['subject'])) {
    $subject = $conn->real_escape_string($_GET['subject']);
    $where .= " AND papers.subject='$subject'";
    $params['subject'] = $subject;
}
if (!empty($_GET['track'])) {
    $track = $conn->real_escape_string($_GET['track']);
    $where .= " AND papers.track='$track'";
    $params['track'] = $track;
}
if (!empty($_GET['teacher'])) {
    $t = $conn->real_escape_string($_GET['teacher']);
    $tLower = strtolower($t);
    $where .= " AND LOWER(users.full_name) LIKE '%" . $conn->real_escape_string($tLower) . "%'";
    $params['teacher'] = $t;
}
if (!empty($_GET['search'])) {
    $s = $conn->real_escape_string($_GET['search']);
    $sLower = strtolower($s);
    $where .= " AND LOWER(papers.title) LIKE '%" . $conn->real_escape_string($sLower) . "%'";
    $params['search'] = $s;
}

$query = "SELECT papers.*, users.full_name as teacher_name FROM papers JOIN users ON papers.teacher_id = users.id $where ORDER BY papers.created_at DESC";
$result = $conn->query($query);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Papers</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<?php include("../includes/navbar.php"); ?>

<div class="page-container">
    <h2 class="page-title">All Papers</h2>
    <form method="GET" class="filter-form" style="flex-wrap: wrap; gap: 8px;">
        <input type="text" name="search" placeholder="Search title..." value="<?php echo htmlspecialchars($params['search'] ?? ''); ?>" style="flex: 1; min-width: 150px;">
        <select name="grade" style="flex: 0 1 auto;">
            <option value="">All Grades</option>
            <?php if(!empty($grades)): foreach($grades as $g): ?>
                <option value="<?php echo $g; ?>" <?php if(($params['grade'] ?? '')== $g) echo 'selected'; ?>>Grade <?php echo $g; ?></option>
            <?php endforeach; endif; ?>
        </select>
        <select name="subject" style="flex: 0 1 auto;">
            <option value="">All Subjects</option>
            <?php if(!empty($subjList)): foreach($subjList as $sbj): ?>
                <option value="<?php echo htmlspecialchars($sbj); ?>" <?php if(($params['subject'] ?? '')==$sbj) echo 'selected'; ?>><?php echo htmlspecialchars($sbj); ?></option>
            <?php endforeach; endif; ?>
        </select>
        <select name="track" style="flex: 0 1 auto;">
            <option value="">All Tracks</option>
            <?php if(!empty($trackList)): foreach($trackList as $tr): ?>
                <option value="<?php echo htmlspecialchars($tr); ?>" <?php if(($params['track'] ?? '')==$tr) echo 'selected'; ?>><?php echo htmlspecialchars($tr); ?> Track</option>
            <?php endforeach; endif; ?>
        </select>
        <select name="teacher" style="flex: 0 1 auto;">
            <option value="">All Teachers</option>
            <?php if(!empty($teachers)): foreach($teachers as $teach): ?>
                <option value="<?php echo htmlspecialchars($teach['full_name']); ?>" <?php if(($params['teacher'] ?? '')==$teach['full_name']) echo 'selected'; ?>><?php echo htmlspecialchars($teach['full_name']); ?></option>
            <?php endforeach; endif; ?>
        </select>
        <button type="submit" style="white-space: nowrap;">Filter</button>
    </form>

    <div style="width: 100%;">
        <table class="table">
            <tr>
                <th>Title</th>
                <th>Grade</th>
                <th>Subject</th>
                <th>Track</th>
                <th>Teacher</th>
                <th>Year</th>
                <th>Actions</th>
            </tr>
            <?php if($result->num_rows): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo $row['grade']; ?></td>
                        <td><?php echo htmlspecialchars($row['subject']); ?></td>
                        <td><?php echo htmlspecialchars($row['track'] ?? ''); ?> Track</td>
                        <td><?php echo htmlspecialchars($row['teacher_name']); ?></td>
                        <td><?php echo $row['year']; ?></td>
                        <td>
                            <a href="../view.php?id=<?php echo $row['id']; ?>" target="_blank" class="btn btn-secondary btn-sm" style="display: inline-block; padding: 4px 8px; white-space: nowrap;">View</a>
                            <a href="delete_paper.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" style="display: inline-block; padding: 4px 8px; white-space: nowrap;">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7">No papers found</td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
</body>
</html>