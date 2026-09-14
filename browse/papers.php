<?php
include("../config/db.php");

if(!isset($_GET['grade']) || !isset($_GET['subject'])){
    die("Invalid access");
}

$grade = $conn->real_escape_string($_GET['grade']);
$subject = $conn->real_escape_string($_GET['subject']);

$search = "";
$year_filter = "";
$category_filter = "";
$track_filter = "";

$where = "WHERE papers.grade='$grade' AND papers.subject='$subject' AND papers.status='approved'";

// prepare category list
$catResult = $conn->query("SELECT DISTINCT category FROM papers WHERE papers.grade='$grade' AND papers.subject='$subject' AND papers.status='approved'");
$categories = [];
while($c = $catResult->fetch_assoc()){
    $categories[] = $c['category'];
}

// prepare track list for grades 9-12
$tracks = [];
if($grade >= 9 && $grade <= 12){
    $trackResult = $conn->query("SELECT DISTINCT track FROM papers WHERE papers.grade='$grade' AND papers.subject='$subject' AND papers.status='approved' AND track IS NOT NULL AND track != ''");
    while($t = $trackResult->fetch_assoc()){
        $tracks[] = $t['track'];
    }
}

if(isset($_GET['search']) && $_GET['search'] !== ""){
    $search = $conn->real_escape_string($_GET['search']);
    $searchLower = strtolower($search);
    $where .= " AND LOWER(papers.title) LIKE '%" . $conn->real_escape_string($searchLower) . "%'";
}

if(isset($_GET['year']) && $_GET['year'] !== ""){
    $year_filter = $conn->real_escape_string($_GET['year']);
    $where .= " AND papers.year='$year_filter'";
}

if(isset($_GET['category']) && $_GET['category'] !== ""){
    $category_filter = $conn->real_escape_string($_GET['category']);
    $where .= " AND papers.category='$category_filter'";
}

if(isset($_GET['track']) && $_GET['track'] !== ""){
    $track_filter = $conn->real_escape_string($_GET['track']);
    $where .= " AND papers.track='$track_filter'";
}
$query = "SELECT papers.*, users.full_name as teacher_name FROM papers LEFT JOIN users ON papers.teacher_id = users.id $where ORDER BY papers.created_at DESC LIMIT 500";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Grade <?php echo $grade; ?> - <?php echo htmlspecialchars($subject); ?></title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/browse.css">
</head>
<body>
<?php include("../includes/navbar.php"); ?>

<div class="page-container">
    <h2 class="page-title">Grade <?php echo $grade; ?> - <?php echo htmlspecialchars($subject); ?></h2>

    <!-- Search + Filter -->
    <form method="GET" class="filter-form">
        <input type="hidden" name="grade" value="<?php echo $grade; ?>">
        <input type="hidden" name="subject" value="<?php echo htmlspecialchars($subject); ?>">

        <input type="text" name="search" placeholder="Search title..." value="<?php echo htmlspecialchars($search); ?>">

        <select name="year">
            <option value="">All Years</option>
            <option value="2023" <?php if($year_filter=='2023') echo 'selected'; ?>>2023</option>
            <option value="2024" <?php if($year_filter=='2024') echo 'selected'; ?>>2024</option>
            <option value="2025" <?php if($year_filter=='2025') echo 'selected'; ?>>2025</option>
            <option value="2026" <?php if($year_filter=='2026') echo 'selected'; ?>>2026</option>
        </select>

        <?php if(!empty($categories)): ?>
            <select name="category">
                <option value="">All Categories</option>
                <?php foreach($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>" <?php if($category_filter===$cat) echo 'selected'; ?>><?php echo htmlspecialchars($cat); ?></option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <?php if(!empty($tracks)): ?>
            <select name="track">
                <option value="">All Tracks</option>
                <?php foreach($tracks as $track): ?>
                    <option value="<?php echo htmlspecialchars($track); ?>" <?php if($track_filter===$track) echo 'selected'; ?>><?php echo htmlspecialchars($track); ?> Track</option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <button type="submit">Filter</button>
    </form>

    <div style="width: 100%;">
        <table class="table">
            <tr>
                <th>Title</th>
                <th>Category</th>
                <th>Track</th>
                <th>Year</th>
                <th>Teacher</th>
                <th>Uploaded</th>
                <th>View</th>
                <th>Download</th>
            </tr>

            <?php if($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars($row['category']); ?></td>
                        <td><?php echo htmlspecialchars($row['track'] ?? ''); ?> <?php echo ($row['track'] ?? '') ? 'Track' : ''; ?></td>
                        <td><?php echo $row['year']; ?></td>
                        <td><?php echo htmlspecialchars($row['teacher_name'] ?? 'N/A'); ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($row['created_at'])); ?></td>
                        <td style="padding: 5px;">
                            <a href="../view.php?id=<?php echo $row['id']; ?>" target="_blank" class="btn btn-secondary btn-sm" style="display: inline-block; padding: 4px 8px; white-space: nowrap;">View</a>
                        </td>
                        <td style="padding: 5px;">
                            <?php if ($row['upload_type'] === 'link'): ?>
                                <a href="<?php echo htmlspecialchars($row['link_url']); ?>" target="_blank" style="color: #007bff; text-decoration: none; word-break: break-all;" title="Click to open link">
                                    <?php echo htmlspecialchars($row['link_url']); ?>
                                </a>
                            <?php else: ?>
                                <a href="../download.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm" style="display: inline-block; padding: 4px 8px; white-space: nowrap;">Download</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8">No papers found</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

    <a href="subjects.php?grade=<?php echo $grade; ?>" class="btn btn-secondary mt-20">← Back to Subjects</a>
</div>

<?php include("../includes/footer.php"); ?>

<script>
    // auto-submit when a filter dropdown changes
    document.addEventListener('DOMContentLoaded', function(){
        document.querySelectorAll('.filter-form select').forEach(function(el){
            el.addEventListener('change', function(){
                this.form.submit();
            });
        });
    });
</script>

</body>
</html>