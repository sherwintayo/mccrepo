<?php
session_start();
require 'config.php'; // Ensure this file connects to your database correctly

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

// Prepare the database query
try {
    $stmt = $conn->prepare("INSERT INTO download_requests (user_id, file_id, reason) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $userId, $fileId, $reason);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Request submitted successfully']);
    } else {
        throw new Exception('Failed to execute query: ' . $stmt->error);
    }
    $stmt->close();
} catch (Exception $e) {
    // Log the error if necessary (avoid exposing details to users)
    error_log($e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error. Please try again later.']);
}
?>