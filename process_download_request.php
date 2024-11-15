<?php
session_start();
require 'config.php';

// Verify user is logged in
if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

// Sanitize and validate POST data
$userId = $_SESSION['user_id'];
$fileId = isset($_POST['file_id']) ? intval($_POST['file_id']) : 0;
$reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';

if ($fileId <= 0 || empty($reason)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input data']);
    exit;
}

try {
    // Enable MySQLi error reporting
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    // Prepare and execute the query
    $stmt = $conn->prepare("INSERT INTO download_requests (user_id, file_id, reason, status, status_read) VALUES (?, ?, ?, 'pending', 'unread')");
    if (!$stmt) {
        throw new Exception("Statement preparation failed: " . $conn->error);
    }
    $stmt->bind_param("iis", $userId, $fileId, $reason);
    if (!$stmt->execute()) {
        throw new Exception("Query execution failed: " . $stmt->error);
    }

    // Success response
    echo json_encode(['status' => 'success', 'message' => 'Request submitted successfully']);
    $stmt->close();
} catch (Exception $e) {
    // Log detailed error for debugging
    error_log($e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error. Please try again later.']);
}
?>