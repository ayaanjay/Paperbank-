<?php
session_start();
include("../includes/csrf.php");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register - PaperBank</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>
<?php include("../includes/navbar.php"); ?>



<div class="form-wrapper">
    <div class="form-card">
        <h2>Register for PaperBank</h2>

        <form id="loginForm" action="register_process.php" method="POST">
            <?php echo csrfField(); ?>

            <div class="input-box">
                <label for="full_name">Full Name:</label>
                <input type="text" name="full_name" placeholder="Full Name" required><br><br>
            </div>

            <div class="input-box">
                <label for="email">Email:</label>
                <input type="email" name="email" placeholder="Email" required><br><br>
            </div>

            <div class="input-box">
                <label for="password">Password:</label>
                <input type="password" name="password" placeholder="Password" required><br><br>
                <small style="color: #666; font-size: 12px;">You must use the universal password provided by your administrator.</small>
            </div>
                <button class="submit-btn" type="submit">Register</button>
        </form>

        <div class="form-footer">
            <a href="login.php">Already have an account ? Login now</a>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
</body>
</html>