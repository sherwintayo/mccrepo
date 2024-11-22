<?php
session_start();
require 'config.php';

$student_id = $_SESSION['user_id'] ?? 0; // Ensure user ID is valid
$notification_id = intval($_GET['id'] ?? 0); // Fetch the notification ID
$isDownload = isset($_GET['isDownload']) && $_GET['isDownload'] === 'true';

if (!$student_id || !$notification_id) {
  echo json_encode(['success' => false, 'message' => 'Invalid request.']);
  exit;
}

// Determine table and column based on notification type
$table = $isDownload ? 'download_requests' : 'notifications';
$column = $isDownload ? 'status_read' : 'status';

// Adjust user_id column name for download_requests
$userColumn = $isDownload ? 'user_id' : 'student_id';

$stmt = $conn->prepare("UPDATE $table SET $column = 'read' WHERE id = ? AND $userColumn = ?");
$stmt->bind_param("ii", $notification_id, $student_id);

$success = $stmt->execute();
$stmt->close();

echo json_encode(['success' => $success]);
exit;
?>