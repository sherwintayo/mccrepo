<?php
require_once 'config.php';

if (isset($_GET['id'])) {
  $notificationId = intval($_GET['id']);
  $response = ['success' => false];

  // Update the notifications table
  $stmt = $conn->prepare("UPDATE notifications SET status = 'read' WHERE id = ?");
  $stmt->bind_param("i", $notificationId);

  if ($stmt->execute()) {
    $response['success'] = true;

    // Check if this notification is for an approved download request
    $downloadStmt = $conn->prepare("
        UPDATE download_requests dr
        JOIN notifications n ON dr.id = n.request_id
        SET dr.status_read = 'read'
        WHERE n.id = ? AND dr.status = 'approved'
    ");
    $downloadStmt->bind_param("i", $notificationId);
    $downloadStmt->execute();
    $downloadStmt->close();
  }

  $stmt->close();
  echo json_encode($response);
}

$conn->close();
?>