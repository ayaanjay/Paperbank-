<?php
include("../config/subjects.php");

$grade = $_GET['grade'] ?? null;

// Validate grade if provided
if($grade && !isset($subjects[$grade])){
    header("Location: subjects.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $grade ? "Grade $grade Subjects" : "Browse Papers by Grade"; ?> - PaperBank</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/browse.css">
</head>
<body>
<?php include("../includes/navbar.php"); ?>

<div class="page-container">
    
    <?php if(!$grade): ?>
        <!-- GRADE SELECTION VIEW -->
        <h2 class="page-title">Select Your Grade</h2>
        <p style="text-align: center; color: #666; margin-bottom: 30px;">Choose a grade to explore available papers and subjects</p>
        
        <div class="grades-grid-browse">
            <?php for($i = 6; $i <= 12; $i++): ?>
                <a href="subjects.php?grade=<?php echo $i; ?>" class="subject-grade-card">
                    Grade <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>

    <?php else: ?>
        <!-- SUBJECT SELECTION VIEW -->
        <a href="subjects.php" class="btn btn-secondary">⬅ Back to Grades</a>
        <h2 class="page-title" style="margin-top:15px;">Grade <?php echo $grade; ?> - Select Subject</h2>
        <p style="text-align: center; color: #666; margin-bottom: 30px;">Choose a subject to view available papers</p>

        <div class="subject-grid-browse">
            <?php foreach($subjects[$grade] as $subject): ?>
                <a class="subject-grade-card" href="papers.php?grade=<?php echo $grade; ?>&subject=<?php echo urlencode($subject); ?>">
                    <?php echo htmlspecialchars($subject); ?>
                </a>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</div>

<?php include("../includes/footer.php"); ?>
</body>
</html>