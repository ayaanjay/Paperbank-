<?php
// Database migration script for adding password_changed column
// Run this once to update your existing database

session_start();
include("config/db.php");

// Check if column already exists
$check_column = $conn->query("SHOW COLUMNS FROM users LIKE 'password_changed'");
$column_exists = $check_column->num_rows > 0;

if ($column_exists) {
    echo "<h2>✅ Migration Already Applied</h2>";
    echo "<p>The password_changed column already exists in your database.</p>";
} else {
    // Add the column
    $sql = "ALTER TABLE `users` ADD COLUMN `password_changed` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0 = using universal password, 1 = changed to personal password' AFTER `status`";

    if ($conn->query($sql) === TRUE) {
        echo "<h2>✅ Migration Successful</h2>";
        echo "<p>The password_changed column has been added to the users table.</p>";

        // Update existing approved teachers to require password change
        $update_sql = "UPDATE `users` SET `password_changed` = 0 WHERE `role` = 'teacher' AND `status` = 'approved'";
        if ($conn->query($update_sql) === TRUE) {
            echo "<p>Existing approved teachers have been marked as needing password change.</p>";
        } else {
            echo "<p style='color: orange;'>Warning: Could not update existing teachers: " . $conn->error . "</p>";
        }
    } else {
        echo "<h2>❌ Migration Failed</h2>";
        echo "<p>Error: " . $conn->error . "</p>";
        echo "<p>Please run this SQL manually in phpMyAdmin:</p>";
        echo "<pre>" . $sql . "</pre>";
    }
}

$conn->close();
?>

<hr>
<h3>Universal Password Security Feature</h3>
<p><strong>Universal Password:</strong> paperbank147</p>
<p><strong>How it works:</strong></p>
<ul>
    <li>Teachers must use the universal password during registration</li>
    <li>After first login, they are forced to change their password</li>
    <li>The new password is stored securely in the database</li>
    <li>Future logins use the personal password</li>
</ul>

<p><a href="../index.php">← Back to PaperBank</a></p>