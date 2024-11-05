<?php
session_start();
require_once('config.php');

// Check if the user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

// Validate POST data
if (empty($_POST['fileId']) || empty($_POST['reason'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing fileId or reason']);
    exit;
}

// Capture and sanitize input
$fileId = intval($_POST['fileId']);
$reason = htmlspecialchars($_POST['reason'], ENT_QUOTES, 'UTF-8');
$userId = $_SESSION['user_id'] ?? null; // Assuming user ID is stored in the session

if (!$userId) {
    echo json_encode(['status' => 'error', 'message' => 'User ID not found in session']);
    exit;
}

// Verify database connection and query
try {
    // Confirm connection status
    if (!$conn) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }

    // Insert request into the database
    $stmt = $conn->prepare("INSERT INTO download_requests (user_id, file_id, reason, status) VALUES (?, ?, ?, 'pending')");
    if ($stmt === false) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }

    // Bind parameters and execute statement
    $stmt->bind_param("iis", $userId, $fileId, $reason);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    echo json_encode(['status' => 'success', 'message' => 'Request sent successfully']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error', 'error' => $e->getMessage()]);
}
?>