<?php
require_once 'config.php'; // Adjust the path as needed

$response = ['success' => false];

// Check if the notification ID is provided in the request
if (isset($_GET['id'])) {
  $notificationId = intval($_GET['id']);

  // Update `notifications` table
  $stmt = $conn->prepare("UPDATE notifications SET status = 'read' WHERE id = ?");
  $stmt->bind_param("i", $notificationId);
  if ($stmt->execute()) {
    $response['success'] = true;
  } else {
    $response['error'] = "Failed to update the notification status in notifications.";
  }
  $stmt->close();

  // Update `download_requests` table for related requests
  $downloadStmt = $conn->prepare("UPDATE download_requests SET status_read = 'read' WHERE id = ?");
  $downloadStmt->bind_param("i", $notificationId); // Assuming `id` in `notifications` corresponds to `id` in `download_requests`
  if ($downloadStmt->execute()) {
    $response['success'] = true;
  } else {
    $response['error'] = "Failed to update the status_read in download_requests.";
  }
  $downloadStmt->close();
}

$conn->close();
echo json_encode($response);
?>