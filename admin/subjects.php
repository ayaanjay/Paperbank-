<?php
session_start();
include("../config/db.php"); // might not need db, but include for session/logging

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// load current subjects
include(__DIR__ . "/../config/subjects.php"); // defines $subjects

// helper to persist
function save_subjects($subjects) {
    $path = __DIR__ . "/../config/subjects.php";
    $export = var_export($subjects, true);
    $content = "<?php\n\n\$subjects = $export;\n\n?>";
    // try to write safely
    file_put_contents($path, $content);
}

// handle add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_grade'], $_POST['add_subject'])) {
    $g = trim($_POST['add_grade']);
    $s = trim($_POST['add_subject']);
    if ($g !== '' && $s !== '') {
        if (!isset($subjects[$g])) {
            $subjects[$g] = [];
        }
        if (!in_array($s, $subjects[$g])) {
            $subjects[$g][] = $s;
            save_subjects($subjects);
            $_SESSION['message'] = ['text' => 'Subject added.','type'=>'success'];
        } else {
            $_SESSION['message'] = ['text' => 'Subject already exists for that grade.','type'=>'error'];
        }
    }
    header('Location: subjects.php');
    exit();
}

// handle remove
if (isset($_GET['remove_grade'], $_GET['remove_subject'])) {
    $g = $_GET['remove_grade'];
    $s = $_GET['remove_subject'];
    if (isset($subjects[$g])) {
        $subjects[$g] = array_filter($subjects[$g], function($x) use($s){ return $x !== $s; });
        if (empty($subjects[$g])) {
            unset($subjects[$g]);
        }
        save_subjects($subjects);
        $_SESSION['message'] = ['text' => 'Subject removed.','type'=>'success'];
    }
    header('Location: subjects.php');
    exit();
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Subjects</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<?php include("../includes/navbar.php"); ?>

<div class="page-container">
    <h2 class="page-title">Subjects by Grade</h2>
    <a href="dashboard.php" class="btn btn-secondary mb-20">← Back to Dashboard</a>

    <h3 class="mt-20">Subjects by Grade</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Grade</th>
                <th>Subjects</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($subjects as $grade => $list): ?>
                <tr>
                    <td>Grade <?php echo htmlspecialchars($grade); ?></td>
                    <td>
                        <?php if (!empty($list)): ?>
                            <div class="subjects-buttons">
                                <?php foreach($list as $sub): ?>
                                    <a href="subjects.php?remove_grade=<?php echo urlencode($grade); ?>&remove_subject=<?php echo urlencode($sub); ?>" class="btn btn-danger btn-sm subject-delete-btn" onclick="return confirm('Are you sure you want to delete the subject \"<?php echo htmlspecialchars($sub); ?>\" from grade <?php echo htmlspecialchars($grade); ?>?');">
                                        <?php echo htmlspecialchars($sub); ?> ×
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <span class="no-subjects">No subjects added yet.</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h3 class="mt-20">Add Subject</h3>
    <form method="POST" class="form-card" style="margin-top:0; max-width:560px;">
        <div class="form-group">
            <label>Grade</label>
            <input type="number" name="add_grade" min="1" max="12" placeholder="Grade (e.g. 6)" required>
        </div>
        <div class="form-group">
            <label>Subject</label>
            <input type="text" name="add_subject" placeholder="Subject name" required>
        </div>
        <button class="btn btn-primary" type="submit">Add Subject</button>
    </form>
</div>

<?php include("../includes/footer.php"); ?>
</body>
</html>