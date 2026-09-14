<?php
include("../config/db.php");
include("../config/subjects.php");

// Initialize filters
$search = "";
$grade_filter = "";
$subject_filter = "";
$year_filter = "";
$category_filter = "";
$track_filter = "";

// Get all distinct values for dropdowns
$grades = array_keys($subjects);
$categories = [];
$tracks = [];
$subjects_list = [];

// Build WHERE clause - prefix columns with table names to avoid ambiguity
$where = "WHERE papers.status='approved'";

if(!empty($_GET['search'])){
    $search = $conn->real_escape_string($_GET['search']);
    $searchLower = strtolower($search);
    $where .= " AND LOWER(papers.title) LIKE '%" . $conn->real_escape_string($searchLower) . "%'";
}

if(!empty($_GET['grade'])){
    $grade_filter = $conn->real_escape_string($_GET['grade']);
    $where .= " AND papers.grade='$grade_filter'";
}

if(!empty($_GET['subject'])){
    $subject_filter = $conn->real_escape_string($_GET['subject']);
    $where .= " AND papers.subject='$subject_filter'";
}

if(!empty($_GET['year'])){
    $year_filter = $conn->real_escape_string($_GET['year']);
    $where .= " AND papers.year='$year_filter'";
}

if(!empty($_GET['category'])){
    $category_filter = $conn->real_escape_string($_GET['category']);
    $where .= " AND papers.category='$category_filter'";
}

if(!empty($_GET['track'])){
    $track_filter = $conn->real_escape_string($_GET['track']);
    $where .= " AND papers.track='$track_filter'";
}

// Get distinct categories, tracks, and subjects for dropdowns
$catResult = $conn->query("SELECT DISTINCT category FROM papers WHERE papers.status='approved' ORDER BY category");
if($catResult){
    while($c = $catResult->fetch_assoc()){
        $categories[] = $c['category'];
    }
}

$trackResult = $conn->query("SELECT DISTINCT track FROM papers WHERE papers.status='approved' AND track IS NOT NULL AND track != '' ORDER BY track");
if($trackResult){
    while($t = $trackResult->fetch_assoc()){
        $tracks[] = $t['track'];
    }
}

// Get all subjects from entire database
$allSubjectsResult = $conn->query("SELECT DISTINCT subject FROM papers WHERE papers.status='approved' ORDER BY subject");
if($allSubjectsResult){
    while($s = $allSubjectsResult->fetch_assoc()){
        $subjects_list[] = $s['subject'];
    }
}

// Get papers with error handling
$query = "SELECT papers.*, users.full_name as teacher_name FROM papers LEFT JOIN users ON papers.teacher_id = users.id $where ORDER BY papers.created_at DESC LIMIT 500";
$result = $conn->query($query);

if(!$result){
    die("Database error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Papers - PaperBank</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/browse.css">
    <style>
        .advanced-filter {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
        }
        .filter-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
        }
        .filter-group label {
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
            font-size: 14px;
        }
        .filter-group input,
        .filter-group select {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }
        .filter-actions {
            display: flex;
            gap: 10px;
        }
        .filter-actions button {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-filter {
            background: #007bff;
            color: white;
        }
        .btn-filter:hover {
            background: #0056b3;
        }
        .btn-reset {
            background: #6c757d;
            color: white;
        }
        .btn-reset:hover {
            background: #545b62;
        }
        .results-info {
            margin-bottom: 15px;
            color: #666;
            font-size: 14px;
        }
        .no-results {
            text-align: center;
            padding: 40px;
            color: #999;
        }
    </style>
</head>
<body>
<?php include("../includes/navbar.php"); ?>

<div class="page-container">
    <a href="subjects.php" class="btn btn-secondary">⬅ Back</a>
    <h2 class="page-title" style="margin-top:15px;">All Papers</h2>
    
    <!-- Advanced Filter Section -->
    <div class="advanced-filter">
        <form method="GET" class="filter-form">
            <div class="filter-row">
                <div class="filter-group">
                    <label for="search">Search Title:</label>
                    <input type="text" id="search" name="search" placeholder="Enter paper title..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="filter-group">
                    <label for="grade">Grade:</label>
                    <select name="grade" id="grade">
                        <option value="">All Grades</option>
                        <?php foreach($grades as $g): ?>
                            <option value="<?php echo $g; ?>" <?php if($grade_filter==$g) echo 'selected'; ?>>Grade <?php echo $g; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="subject">Subject:</label>
                    <select name="subject" id="subject">
                        <option value="">All Subjects</option>
                        <?php foreach($subjects_list as $subj): ?>
                            <option value="<?php echo urlencode($subj); ?>" <?php if($subject_filter==urlencode($subj)) echo 'selected'; ?>><?php echo htmlspecialchars($subj); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="filter-row">
                <div class="filter-group">
                    <label for="year">Year:</label>
                    <select name="year" id="year">
                        <option value="">All Years</option>
                        <option value="2023" <?php if($year_filter=='2023') echo 'selected'; ?>>2023</option>
                        <option value="2024" <?php if($year_filter=='2024') echo 'selected'; ?>>2024</option>
                        <option value="2025" <?php if($year_filter=='2025') echo 'selected'; ?>>2025</option>
                        <option value="2026" <?php if($year_filter=='2026') echo 'selected'; ?>>2026</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="category">Category:</label>
                    <select name="category" id="category">
                        <option value="">All Categories</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php if($category_filter===$cat) echo 'selected'; ?>><?php echo htmlspecialchars($cat); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="track">Track:</label>
                    <select name="track" id="track">
                        <option value="">All Tracks</option>
                        <?php foreach($tracks as $t): ?>
                            <option value="<?php echo htmlspecialchars($t); ?>" <?php if($track_filter===$t) echo 'selected'; ?>><?php echo htmlspecialchars($t); ?> Track</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn-filter">🔍 Search & Filter</button>
                <button type="button" class="btn-reset" onclick="window.location.href='all_papers.php';">Reset Filters</button>
            </div>
        </form>
    </div>

    <?php if($result->num_rows > 0): ?>
        <div class="results-info">
            📊 Found <strong><?php echo $result->num_rows; ?></strong> paper(s)
        </div>

        <div style="width: 100%;">
            <table class="table">
                <tr>
                    <th>Title</th>
                    <th>Grade</th>
                    <th>Subject</th>
                    <th>Category</th>
                    <th>Track</th>
                    <th>Year</th>
                    <th>Teacher</th>
                    <th>Uploaded</th>
                    <th>View</th>
                    <th>Download</th>
                </tr>

                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo $row['grade']; ?></td>
                        <td><?php echo htmlspecialchars($row['subject']); ?></td>
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
            </table>
        </div>

    <?php else: ?>
        <div class="no-results">
            <p>📭 No papers found matching your filters.</p>
            <p style="margin-top: 10px;"><a href="all_papers.php" style="color: #007bff; text-decoration: none;">Clear filters and try again</a></p>
        </div>
    <?php endif; ?>

</div>

<?php include("../includes/footer.php"); ?>
</body>
</html>
