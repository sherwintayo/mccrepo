<?php
session_start();
require 'config.php'; // Ensure this connects to your database

header('Content-Type: application/json'); // Set the content type to JSON

// Ensure user is logged in
if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

// Initialize variables
$userId = $_SESSION['user_id'] ?? 0;
$fileId = isset($_POST['file_id']) ? intval($_POST['file_id']) : 0;
$reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';

// Check input validity
if ($fileId <= 0 || empty($reason)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input data']);
    exit;
}

// Ensure database connection is established
if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection error']);
    exit;
}

// Insert download request into the database
$stmt = $conn->prepare("INSERT INTO download_requests (user_id, file_id, reason) VALUES (?, ?, ?)");
if ($stmt) {
    $stmt->bind_param("iis", $userId, $fileId, $reason);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Request submitted successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to prepare statement: ' . $conn->error]);
}
exit;
?>