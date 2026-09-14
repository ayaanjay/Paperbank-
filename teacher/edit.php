<?php
session_start();
include("../config/db.php");
include("../config/subjects.php");
include("../includes/csrf.php");

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher'){
    header("Location: ../auth/login.php");
    exit();
}

$id = intval($_GET['id']);
$teacher_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM papers WHERE id=? AND teacher_id=?");
$stmt->bind_param("ii", $id, $teacher_id);
$stmt->execute();
$paper = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$paper){
    header("Location: my_papers.php");
    exit();
}

if(isset($_POST['update'])){
    
    // Validate CSRF token
    if (!validateCSRFToken()) {
        $_SESSION['message'] = ['text'=>'Security validation failed. Please try again.','type'=>'error'];
        header("Location: edit.php?id=$id");
        exit();
    }
    
    $title = trim($_POST['title']);
    $grade = $_POST['grade'];
    $subject = $_POST['subject'];
    $category = $_POST['category'];
    $year = $_POST['year'];
    $track = $_POST['track'] ?? '';

    $stmt = $conn->prepare("
        UPDATE papers 
        SET title=?, grade=?, subject=?, category=?, year=?, track=? 
        WHERE id=? AND teacher_id=?
    ");

    $stmt->bind_param("sissisii",
        $title,
        $grade,
        $subject,
        $category,
        $year,
        $track,
        $id,
        $teacher_id
    );

    $stmt->execute();
    $_SESSION['message'] = ['text'=>'Paper updated successfully.','type'=>'success'];
    header("Location: my_papers.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Paper</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/login.css">
    <link rel="stylesheet" href="../assets/css/teacher.css">
</head>
<body>
<?php include("../includes/navbar.php"); ?>

<div class="page-container">
    <a href="my_papers.php" class="btn btn-secondary">⬅ Back</a>
    <h2 class="page-title" style="margin-top:10px;">Edit Paper</h2>

    <form method="POST" enctype="multipart/form-data" class="form-card" style="margin-top:20px;">
        <?php echo csrfField(); ?>
        <div class="form-group">
            <label>Title:</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($paper['title']); ?>" required>
        </div>
        <div class="form-group">
            <label>Category:</label>
            <select name="category" required>
                <option value="">Select Category</option>
                <option value="Test" <?php if($paper['category']=='Test') echo 'selected'; ?>>Test</option>
                <option value="Homework" <?php if($paper['category']=='Homework') echo 'selected'; ?>>Homework</option>
                <option value="Classwork" <?php if($paper['category']=='Classwork') echo 'selected'; ?>>Classwork</option>
                <option value="Exam" <?php if($paper['category']=='Exam') echo 'selected'; ?>>Exam</option>
            </select>
        </div>
        <div class="form-group">
            <label>Grade:</label>
            <select name="grade" id="grade" required>
                <option value="">Select Grade</option>
                <?php foreach($subjects as $grade => $list): ?>
                    <option value="<?php echo $grade; ?>" <?php if($paper['grade']==$grade) echo 'selected'; ?>><?php echo $grade; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group" id="track-group" style="<?php echo ($paper['grade'] >= 9 && $paper['grade'] <= 12) ? 'display:block;' : 'display:none;'; ?>">
            <label>Track:</label>
            <select name="track">
                <option value="">Select Track</option>
                <option value="UK" <?php if(($paper['track'] ?? '')=='UK') echo 'selected'; ?>>UK Track</option>
                <option value="US" <?php if(($paper['track'] ?? '')=='US') echo 'selected'; ?>>US Track</option>
            </select>
        </div>
        <div class="form-group">
            <label>Subject:</label>
            <select name="subject" id="subject" required>
                <option value="">Select Subject</option>
                <?php if(isset($subjects[$paper['grade']])): ?>
                    <?php foreach($subjects[$paper['grade']] as $sub): ?>
                        <option value="<?php echo $sub; ?>" <?php if($paper['subject']==$sub) echo 'selected'; ?>><?php echo $sub; ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Year:</label>
            <select name="year" required>
                <option value="">Select Year</option>
                <option value="2023" <?php if($paper['year']=='2023') echo 'selected'; ?>>2023</option>
                <option value="2024" <?php if($paper['year']=='2024') echo 'selected'; ?>>2024</option>
                <option value="2025" <?php if($paper['year']=='2025') echo 'selected'; ?>>2025</option>
                <option value="2026" <?php if($paper['year']=='2026') echo 'selected'; ?>>2026</option>
            </select>
        </div>
        <button class="btn btn-primary" type="submit" name="update">Update Paper</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>

<script>
document.addEventListener("DOMContentLoaded", function(){

    const subjects = <?php echo json_encode($subjects); ?>;

    const gradeSelect = document.getElementById("grade");
    const subjectDropdown = document.getElementById("subject");

    gradeSelect.addEventListener("change", function() {

        const grade = this.value;

        subjectDropdown.innerHTML = '<option value="">Select Subject</option>';

        if (subjects[grade]) {
            subjects[grade].forEach(function(subject) {
                const option = document.createElement("option");
                option.value = subject;
                option.textContent = subject;
                subjectDropdown.appendChild(option);
            });
        }

        // Show track for grades 9-12
        const trackGroup = document.getElementById("track-group");
        if (grade >= 9 && grade <= 12) {
            trackGroup.style.display = 'block';
        } else {
            trackGroup.style.display = 'none';
        }
    });

});
</script>

</body>
</html>