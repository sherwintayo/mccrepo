<?php
session_start();
require 'config.php';

$student_id = $_SESSION['user_id'] ?? 0;
$notification_id = intval($_GET['id'] ?? 0);
$isDownload = isset($_GET['isDownload']) && $_GET['isDownload'] === 'true';

if (!$student_id || !$notification_id) {
  echo json_encode(['success' => false, 'message' => 'Invalid request.']);
  exit;
}

$table = $isDownload ? 'download_requests' : 'notifications';
$column = $isDownload ? 'status_read' : 'status';

$stmt = $conn->prepare("UPDATE $table SET $column = 'read' WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $notification_id, $student_id);
$success = $stmt->execute();
$stmt->close();

echo json_encode(['success' => $success]);
exit;
?>