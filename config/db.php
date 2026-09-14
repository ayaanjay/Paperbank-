<?php
// database connection - make sure MySQL/MariaDB service is running
// If credentials or server are incorrect, the constructor will throw an exception.

mysqli_report(MYSQLI_REPORT_STRICT); // force mysqli to throw exceptions rather than warnings

try {
    $conn = new mysqli("localhost", "root", "", "paperbank");
} catch (mysqli_sql_exception $e) {
    // log the error if you have a logging system
    $msg = $e->getMessage();
    // give user a friendly hint
    die("Database connection failed. Please ensure the database server is running and your credentials are correct. (" . htmlspecialchars($msg) . ")");
}

// optionally you can set charset
$conn->set_charset('utf8mb4');
?>