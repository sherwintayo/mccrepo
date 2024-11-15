<?php
session_start();
require 'config.php';  // Ensure this connects to your database

if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$userId = $_SESSION['user_id'];
$fileId = isset($_POST['file_id']) ? intval($_POST['file_id']) : 0;
$reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';

if ($fileId > 0 && $reason !== '') {
    $stmt = $conn->prepare("INSERT INTO download_requests (user_id, file_id, reason) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $userId, $fileId, $reason);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
    }
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request data']);
}
?>