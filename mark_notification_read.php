<?php
require_once 'config.php'; // Adjust the path as needed

if (isset($_GET['id'])) {
  $notificationId = intval($_GET['id']);
  $stmt = $conn->prepare("UPDATE notifications SET status = 'read' WHERE id = ?");
  $stmt->bind_param("i", $notificationId);

  $response = [];
  if ($stmt->execute()) {
    $response['success'] = true;
  } else {
    $response['success'] = false;
    $response['error'] = "Failed to update the notification.";
  }

  $stmt->close();
  $conn->close();

  echo json_encode($response);
}
?>