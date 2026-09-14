<?php
session_start();
include("config/db.php");

/**
 * Secure file download handler
 * Validates user permissions and serves files with proper headers
 * Usage: download.php?id=PAPER_ID
 */

// Get paper ID from query parameter
if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    die("Invalid paper ID.");
}

$paperId = intval($_GET['id']);
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$userRole = isset($_SESSION['role']) ? $_SESSION['role'] : null;

// Fetch paper from database
$stmt = $conn->prepare("
    SELECT p.file_path, p.link_url, p.upload_type, p.title, p.status, p.teacher_id
    FROM papers p
    WHERE p.id = ?
");
$stmt->bind_param("i", $paperId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows != 1) {
    http_response_code(404);
    die("Paper not found.");
}

$paper = $result->fetch_assoc();

// Access control: only approved papers are available for download
if ($paper['status'] !== 'approved') {
    http_response_code(403);
    die("This paper is not available for download.");
}

// Handle link downloads - redirect to URL
if ($paper['upload_type'] === 'link') {
    if (empty($paper['link_url'])) {
        http_response_code(404);
        die("Link URL not found.");
    }

    // Log the access (optional)
    // You could insert into a downloads_log table here

    // Redirect to the link
    header('Location: ' . $paper['link_url']);
    exit();
}

// Handle file downloads
if ($paper['upload_type'] === 'file') {
    // Validate file exists
    $fullFilePath = __DIR__ . "/uploads/papers/" . $paper['file_path'];
    $filePath = realpath($fullFilePath);
    $uploadsDir = realpath(__DIR__ . "/uploads/papers");

    // Security check: ensure file is within uploads directory
    if ($filePath === false || strpos($filePath, $uploadsDir) !== 0) {
        http_response_code(403);
        die("Invalid file path.");
    }

    if (!file_exists($filePath) || !is_file($filePath)) {
        http_response_code(404);
        die("File not found on server.");
    }

    // Get file details
    $fileName = basename($filePath);
    $fileSize = filesize($filePath);
    $fileMimeType = mime_content_type($filePath);

    // If MIME type detection fails, use application/octet-stream
    if ($fileMimeType === false) {
        $fileMimeType = 'application/octet-stream';
    }

    // Set headers for file download
    header('Content-Description: File Transfer');
    header('Content-Type: ' . $fileMimeType);
    header('Content-Disposition: attachment; filename="' . urlencode($paper['title'] . '.' . pathinfo($fileName, PATHINFO_EXTENSION)) . '"');
    header('Content-Length: ' . $fileSize);
    header('Cache-Control: max-age=0');
    header('Pragma: public');
    header('Expires: 0');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

    // Log the download (optional - for audit trail)
    // You could insert into a downloads_log table here

    // Serve the file
    if (!readfile($filePath)) {
        http_response_code(500);
        die("Error serving file.");
    }
    exit();
}

// Invalid upload type
http_response_code(400);
die("Invalid paper type.");

$stmt->close();
$conn->close();
exit();
?>
