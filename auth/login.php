<?php
session_start();
include("../includes/csrf.php");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - PaperBank</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>
<?php include("../includes/navbar.php"); ?>




<div class="form-wrapper">
    <div class="form-card">
        <h2>Teacher Login</h2>

        <form id="loginForm" action="login_process.php" method="POST">
            <?php echo csrfField(); ?>
            
            <div class="input-box">
                <label for="email">Email:</label>
                <input type="email" name="email" placeholder="Email" required><br><br>
            </div>

            <div class="input-box">
                <label for="password">Password:</label>
                <input type="password" name="password" placeholder="Password" required><br><br>
            </div>
                <button class="submit-btn" type="submit">Login</button>
        </form>

        <div class="form-footer">
            <a href="register.php">Don't have an account yet? Register</a>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>

</body>
</html>