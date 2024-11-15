<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
  echo json_encode(['success' => false, 'message' => 'User not logged in.']);
  exit;
}

$notificationId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$notificationType = isset($_GET['type']) ? $_GET['type'] : 'general';

$response = ['success' => false];

if ($notificationId > 0) {
  if ($notificationType === 'download') {
    // Update the status_read column in the download_requests table
    $stmt = $conn->prepare("UPDATE download_requests SET status_read = 'read' WHERE id = ?");
    $stmt->bind_param("i", $notificationId);
  } else {
    // Update the status column in the notifications table
    $stmt = $conn->prepare("UPDATE notifications SET status = 'read' WHERE id = ?");
    $stmt->bind_param("i", $notificationId);
  }

  if ($stmt->execute()) {
    $response['success'] = true;
  } else {
    $response['message'] = "Failed to update notification status.";
  }

  $stmt->close();
}

echo json_encode($response);
?>