<?php
session_start();
require 'config.php';

$student_id = $_SESSION['user_id'] ?? 0;
if (!$student_id) {
  echo json_encode(['success' => false, 'message' => 'Unauthorized']);
  exit;
}

$notificationId = intval($_GET['id'] ?? 0);
$isDownload = isset($_GET['isDownload']) && $_GET['isDownload'] === 'true';

if ($notificationId > 0) {
  $table = $isDownload ? 'download_requests' : 'notifications';
  $column = $isDownload ? 'status_read' : 'status';

  $stmt = $conn->prepare("UPDATE $table SET $column = 'read' WHERE id = ? AND student_id = ?");
  $stmt->bind_param("ii", $notificationId, $student_id);
  $success = $stmt->execute();
  $stmt->close();

  echo json_encode(['success' => $success]);
  exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid notification ID']);
exit;
?>