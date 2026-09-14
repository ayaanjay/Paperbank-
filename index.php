<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <title>PaperBank</title>
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>

<?php include("includes/navbar.php"); ?>
<!--HEADLINE-->
<section class="headline" id="main-content">
    <div class="headline_text">
        <h1>All your ACA assessments <br> in one place</h1>
        <p>Access Past Papers Easily</p>
    </div>
    <div class="headline_btns">
        <a href="browse/subjects.php"><button class="material_btn">Materials</button></a>
        <a href="help.php"><button class="guide_btn">Guide</button></a>
    </div>

</section>

<!-- INTRO -->
<section class="intro">
    <h1>Prepare for exams with confidence</h2>
    <p>
    PaperBank is designed to provide students with convenient access to previous class assessments,
    including tests, classwork, homework, and exams uploaded by their teachers. The platform serves as
    a digital repository where academic materials are organized by subject, grade level, and teacher, enabling
    students to easily locate and review relevant past assessments.
    </p>
</section>

<!--CARDS-->
<section class="cards">
    <div class="card">
        <img src="/PaperBank/assets/css/images/Card_1.jpg" alt="">
        <div id="card-text">
            <h2>Past Exams</h2>
            <p>Access relevant previous exam papers to review question styles and practice under real exam conditions </p>
        </div>
        </div>
    
        <div class="card">
        <img src="/PaperBank/assets/css/images/Card_2.jpg" alt="">
        <div id="card-text">
            <h2>Class Assessements</h2>
            <p>View recent class tests and classworks to track your progress and strengthen understanding in key topics</p>
        </div>
        </div>

        <div class="card">
        <img src="/PaperBank/assets/css/images/Card_3.jpg" alt="">
        <div id="card-text">
            <h2>Homeworks</h2>
            <p>Review all assigned homework tasks and keep your daily learning goals on track with simple, organised guidance</p>
        </div>
        
    </div>
</section>


<!-- GRADE SECTION -->
<section class="grades-section">
    <div class="container">
        <h2>Select Your Grade</h2>

        <div class="grades-grid">
            <?php for($i = 6; $i <= 12; $i++): ?>
                <a href="browse/subjects.php?grade=<?php echo $i; ?>" 
                   class="grade-card">
                    Grade <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
</body>
</html>