<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}
// global flash messages
include_once __DIR__ . '/flash.php';

// Check if teacher needs to change password and redirect if accessing other pages
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'teacher' && isset($_SESSION['force_password_change']) && $_SESSION['force_password_change'] === true) {
    // Allow access to account.php and logout.php during forced password change
    $current_page = basename($_SERVER['PHP_SELF']);
    if ($current_page !== 'account.php' && $current_page !== 'logout.php') {
        header("Location: /PaperBank/teacher/account.php");
        exit();
    }
}
?>

<a href="#main-content" class="skip-link">Skip to main content</a>

<header class="navbar">
    <div class="nav-container">
        <div class="bar">
            <!-- HAMBURGER MENU BUTTON -->
            <div class="hamburger" id="hamburger">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>

            <!-- LOGO -->
            <div class="logo">
                <img src="/PaperBank/assets/css/images/logo.png" alt="Logo" >
                <h1>PaperBank</h1>
            </div>

            <!-- NAV LINKS -->
            <ul class="nav-links" id="nav-links">
                <li><a href="/PaperBank/index.php">Home</a></li>
                <li><a href="/PaperBank/browse/subjects.php">Subjects</a></li>
                <li><a href="/PaperBank/browse/all_papers.php">Papers</a></li>
                <li><a href="/PaperBank/help.php">Help</a></li>
                <li><a href="/PaperBank/more.php">More</a></li>
            </ul>
        </div>

        <!-- RIGHT SIDE BUTTONS -->
        <div class="nav-btn">

            <?php if(isset($_SESSION['user_id'])): ?>

                <!-- SHOW USER NAME FROM DATABASE -->
                <span class="username">
                   Welcome <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                </span>

                <?php if($_SESSION['role'] === 'teacher'): ?>

                    <a href="/PaperBank/teacher/dashboard.php">
                        <button class="primary-btn">Upload</button>
                    </a>

                <?php elseif($_SESSION['role'] === 'admin'): ?>

                    <a href="/PaperBank/admin/dashboard.php">
                        <button class="primary-btn">Dashboard</button>
                    </a>

                <?php endif; ?>

                <a href="/PaperBank/auth/logout.php">
                    <button class="danger-btn">Logout</button>
                </a>

            <?php else: ?>

                <a href="/PaperBank/auth/login.php">
                    <button class="primary-btn">Login</button>
                </a>

                <a href="/PaperBank/auth/login.php">
                    <button class="danger-btn">Upload</button>
                </a>

            <?php endif; ?>

        </div>
    </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.getElementById('hamburger');
    const navLinks = document.getElementById('nav-links');

    if (hamburger && navLinks) {
        hamburger.addEventListener('click', function() {
            hamburger.classList.toggle('active');
            navLinks.classList.toggle('active');
        });

        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            if (!hamburger.contains(event.target) && !navLinks.contains(event.target)) {
                hamburger.classList.remove('active');
                navLinks.classList.remove('active');
            }
        });

        // Close menu when clicking on a link
        navLinks.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function() {
                hamburger.classList.remove('active');
                navLinks.classList.remove('active');
            });
        });
    }
});
</script>