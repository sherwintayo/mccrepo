<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
  echo json_encode(['success' => false, 'message' => 'User not logged in.']);
  exit;
}

$requestId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($requestId > 0) {
  $stmt = $conn->prepare("UPDATE download_requests SET status_read = 'read' WHERE id = ?");
  $stmt->bind_param("i", $requestId);
  if ($stmt->execute()) {
    echo json_encode(['success' => true]);
  } else {
    echo json_encode(['success' => false, 'message' => 'Failed to mark as read.']);
  }
  $stmt->close();
} else {
  echo json_encode(['success' => false, 'message' => 'Invalid request ID.']);
}
?>