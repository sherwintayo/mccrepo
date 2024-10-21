<?php
session_start();
require_once('config.php');

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

if (isset($_POST['fileId']) && isset($_POST['reason'])) {
    $fileId = $_POST['fileId'];
    $reason = htmlspecialchars($_POST['reason'], ENT_QUOTES, 'UTF-8');
    $userId = $_SESSION['user_id']; // Assuming you store the user ID in session

    // Insert the request into the database for admin review
    $stmt = $conn->prepare("INSERT INTO download_requests (user_id, file_id, reason, status) VALUES (?, ?, ?, 'pending')");
    $stmt->bind_param("iis", $userId, $fileId, $reason);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Request sent successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to process request']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>
