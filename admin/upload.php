<?php
session_start();
include("../config/db.php");
include("../config/subjects.php");
include("../includes/csrf.php");

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../auth/login.php");
    exit();
}

if(isset($_POST['upload'])){
    if (!validateCSRFToken()) {
        $_SESSION['message'] = ['text'=>'Security validation failed. Please try again.','type'=>'error'];
        header("Location: upload.php");
        exit();
    }

    $teacher_id = $_SESSION['user_id'];
    $title = trim($_POST['title']);
    $grade = $_POST['grade'];
    $subject = $_POST['subject'];
    $category = $_POST['category'];
    $year = $_POST['year'];
    $track = $_POST['track'] ?? '';
    $upload_type = $_POST['upload_type']; // 'file' or 'link'

    // Validate upload type
    if (!in_array($upload_type, ['file', 'link'])) {
        $_SESSION['message'] = ['text'=>'Invalid upload type selected.','type'=>'error'];
        header("Location: upload.php");
        exit();
    }

    $file_path = '';
    $file_size = 0;
    $link_url = '';

    if ($upload_type === 'file') {
        $file = $_FILES['file'];
        $fileName = $file['name'];
        $fileTmp = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileError = $file['error'];

        // 1️⃣ FILE SIZE LIMIT (50MB)
        if($fileSize > 50 * 1024 * 1024){
            $_SESSION['message'] = ['text'=>'File too large. Maximum 50MB allowed.','type'=>'error'];
            header("Location: upload.php");
            exit();
        }

        // 2️⃣ ALLOWED TYPES
        $allowed = ['pdf','doc','docx','txt','jpg','jpeg','png'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if(!in_array($fileExt, $allowed)){
            $_SESSION['message'] = ['text'=>'File type not allowed.','type'=>'error'];
            header("Location: upload.php");
            exit();
        }

        // 4️⃣ SAFE FILE NAME
        $newName = time() . "_" . uniqid() . "." . $fileExt;
        $file_path = $newName;

        $uploadPath = "../uploads/papers/";
        if(!is_dir($uploadPath)){
            mkdir($uploadPath, 0777, true);
        }

        if($fileError === 0){
            move_uploaded_file($fileTmp, $uploadPath . $newName);
        } else {
            $_SESSION['message'] = ['text'=>'File upload failed.','type'=>'error'];
            header("Location: upload.php");
            exit();
        }

    } elseif ($upload_type === 'link') {
        $link_url = trim($_POST['link_url']);

        // Validate URL format
        if (empty($link_url) || !filter_var($link_url, FILTER_VALIDATE_URL)) {
            $_SESSION['message'] = ['text'=>'Please provide a valid URL.','type'=>'error'];
            header("Location: upload.php");
            exit();
        }

        // Check if URL is accessible (optional but recommended)
        $headers = @get_headers($link_url);
        if (!$headers || strpos($headers[0], '200') === false) {
            $_SESSION['message'] = ['text'=>'Warning: The provided URL may not be accessible. Please verify the link.','type'=>'warning'];
            // Don't exit, allow upload with warning
        }
    }

    $stmt = $conn->prepare("SELECT id FROM papers WHERE title=? AND year=? AND subject=?");
    $stmt->bind_param("sis", $title, $year, $subject);
    $stmt->execute();
    $stmt->store_result();

    if($stmt->num_rows > 0){
        $_SESSION['message'] = ['text'=>'Duplicate paper already exists.','type'=>'error'];
        header("Location: upload.php");
        exit();
    }

    // Only process file upload if it's a file type
    if ($upload_type === 'file') {
        $newName = time() . "_" . uniqid() . "." . $fileExt;

        $uploadPath = "../uploads/papers/";
        if(!is_dir($uploadPath)){
            mkdir($uploadPath, 0777, true);
        }

        if($fileError === 0){
            move_uploaded_file($fileTmp, $uploadPath . $newName);
        } else {
            $_SESSION['message'] = ['text'=>'File upload failed.','type'=>'error'];
            header("Location: upload.php");
            exit();
        }
    }

    $insert = $conn->prepare("INSERT INTO papers (title, grade, subject, category, year, track, upload_type, file_path, link_url, teacher_id, file_size, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'approved')");
    $insert->bind_param("sississssii", $title, $grade, $subject, $category, $year, $track, $upload_type, $file_path, $link_url, $teacher_id, $file_size);
    $insert->execute();

    if($insert->error){
        $_SESSION['message'] = ['text'=>'Upload failed: ' . $insert->error,'type'=>'error'];
        header("Location: upload.php");
        exit();
    }

    $_SESSION['message'] = ['text'=>'Paper uploaded successfully.','type'=>'success'];
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Upload Paper</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/login.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<?php include("../includes/navbar.php"); ?>

<div class="page-container">
    <a href="dashboard.php" class="btn btn-secondary">⬅ Back</a>
    <h2 class="page-title" style="margin-top:10px;">Upload Paper (Admin)</h2>

    <form method="POST" enctype="multipart/form-data" class="form-card" style="margin-top:20px;">
        <?php echo csrfField(); ?>
        <div class="form-group">
            <label>Title:</label>
            <input type="text" name="title" required>
        </div>
        <div class="form-group">
            <label>Category:</label>
            <select name="category" required>
                <option value="">Select Category</option>
                <option value="Test">Test</option>
                <option value="Homework">Homework</option>
                <option value="Classwork">Classwork</option>
                <option value="Exam">Exam</option>
            </select>
        </div>
        <div class="form-group">
            <label>Grade:</label>
            <select name="grade" id="grade" required>
                <option value="">Select Grade</option>
                <?php foreach($subjects as $grade => $list): ?>
                    <option value="<?php echo $grade; ?>">Grade <?php echo $grade; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group" id="track-group" style="display:none;">
            <label>Track:</label>
            <select name="track">
                <option value="">Select Track</option>
                <option value="UK">UK Track</option>
                <option value="US">US Track</option>
            </select>
        </div>
        <div class="form-group">
            <label>Subject:</label>
            <select name="subject" id="subject" required>
                <option value="">Select Subject</option>
            </select>
        </div>
        <div class="form-group">
            <label>Year:</label>
            <select name="year" required>
                <option value="">Select Year</option>
                <option value="2023">2023</option>
                <option value="2024">2024</option>
                <option value="2025">2025</option>
                <option value="2026">2026</option>
            </select>
        </div>
        <div class="form-group">
            <label>Upload Type:</label>
            <select name="upload_type" id="upload_type" required>
                <option value="file">Upload File</option>
                <option value="link">Upload Link</option>
            </select>
        </div>
        <div class="form-group" id="file_group">
            <label>Select File:</label>
            <input type="file" name="file">
            <small style="color: #666;">Maximum file size: 50MB. Allowed types: PDF, DOC, DOCX, TXT, JPG, PNG</small>
        </div>
        <div class="form-group" id="link_group" style="display: none;">
            <label>Link URL:</label>
            <input type="url" name="link_url" placeholder="https://example.com/paper-link">
            <small style="color: #666;">Provide a direct link to the paper or resource</small>
        </div>
        <button class="btn btn-primary" type="submit" name="upload">Upload</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>

<script>
document.addEventListener("DOMContentLoaded", function(){
    const subjects = <?php echo json_encode($subjects); ?>;

    const gradeSelect = document.getElementById("grade");
    const subjectDropdown = document.getElementById("subject");
    const uploadTypeSelect = document.getElementById("upload_type");
    const fileGroup = document.getElementById("file_group");
    const linkGroup = document.getElementById("link_group");
    const fileInput = document.querySelector('input[name="file"]');
    const linkInput = document.querySelector('input[name="link_url"]');

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

        const trackGroup = document.getElementById("track-group");
        if (grade >= 9 && grade <= 12) {
            trackGroup.style.display = 'block';
        } else {
            trackGroup.style.display = 'none';
        }
    });

    // Toggle between file and link upload
    uploadTypeSelect.addEventListener("change", function() {
        const uploadType = this.value;

        if (uploadType === 'file') {
            fileGroup.style.display = 'block';
            linkGroup.style.display = 'none';
            fileInput.required = true;
            linkInput.required = false;
        } else if (uploadType === 'link') {
            fileGroup.style.display = 'none';
            linkGroup.style.display = 'block';
            fileInput.required = false;
            linkInput.required = true;
        }
    });
});
</script>

</body>
</html>
