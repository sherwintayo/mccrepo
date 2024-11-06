<?php
session_start();
require_once('../config.php');

$response = ['status' => 'error', 'message' => 'Invalid request'];

if (isset($_POST['id'], $_POST['status']) && in_array($_POST['status'], ['approved', 'rejected'])) {
  $requestId = intval($_POST['id']);
  $status = $_POST['status'];
  $reviewedBy = $_SESSION['user_id']; // Assuming admin's user ID is stored in session

  // Prepare update query
  $stmt = $conn->prepare("UPDATE download_requests SET status = ?, reviewed_by = ?, reviewed_at = NOW() WHERE id = ?");
  $stmt->bind_param("sii", $status, $reviewedBy, $requestId);

  if ($stmt->execute()) {
    $response = ['status' => 'success', 'message' => 'Request status updated successfully.'];
  } else {
    $response['message'] = 'Failed to update request status.';
  }
}

header('Content-Type: application/json');
echo json_encode($response);
