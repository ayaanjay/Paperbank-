<?php
// Database migration script for preserving papers when teachers are deleted
// Run this once to update your existing database

session_start();
include("config/db.php");

echo "<h2>🔄 Database Migration: Preserve Papers on Teacher Deletion</h2>";

// Check current foreign key constraint
$check_fk = $conn->query("
    SELECT CONSTRAINT_NAME
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE TABLE_NAME = 'papers'
    AND TABLE_SCHEMA = DATABASE()
    AND CONSTRAINT_TYPE = 'FOREIGN KEY'
");

$fk_exists = $check_fk->num_rows > 0;

if ($fk_exists) {
    $fk_row = $check_fk->fetch_assoc();
    $fk_name = $fk_row['CONSTRAINT_NAME'];

    echo "<p>Found existing foreign key constraint: <code>$fk_name</code></p>";

    // Drop the existing foreign key
    $drop_fk_sql = "ALTER TABLE `papers` DROP FOREIGN KEY `$fk_name`";
    if ($conn->query($drop_fk_sql) === TRUE) {
        echo "<p>✅ Dropped old foreign key constraint</p>";
    } else {
        echo "<p>❌ Failed to drop foreign key: " . $conn->error . "</p>";
        exit;
    }
}

// Make teacher_id nullable
$alter_column_sql = "ALTER TABLE `papers` MODIFY COLUMN `teacher_id` int(11) DEFAULT NULL";
if ($conn->query($alter_column_sql) === TRUE) {
    echo "<p>✅ Made teacher_id column nullable</p>";
} else {
    echo "<p>❌ Failed to modify teacher_id column: " . $conn->error . "</p>";
    exit;
}

// Add new foreign key with SET NULL
$add_fk_sql = "ALTER TABLE `papers` ADD CONSTRAINT `fk_papers_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE SET NULL";
if ($conn->query($add_fk_sql) === TRUE) {
    echo "<p>✅ Added new foreign key constraint with SET NULL behavior</p>";
} else {
    echo "<p>❌ Failed to add foreign key: " . $conn->error . "</p>";
    exit;
}

echo "<hr>";
echo "<h3>✅ Migration Complete!</h3>";
echo "<p><strong>What changed:</strong></p>";
echo "<ul>";
echo "<li>Teacher papers will now be preserved when teachers are deleted</li>";
echo "<li>Deleted teachers' papers will show 'N/A' as the teacher name</li>";
echo "<li>All existing papers remain intact</li>";
echo "</ul>";

echo "<p><a href='index.php'>← Back to PaperBank</a></p>";
?>