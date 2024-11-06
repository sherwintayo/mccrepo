<?php
session_start();
require_once('../config.php');


$response = ['status' => 'error', 'message' => 'Invalid request'];

// Check if user is logged in and user_id is set in the session
if (!isset($_SESSION['user_id'])) {
  $response['message'] = 'User not logged in.';
  echo json_encode($response);
  exit;
}

if (isset($_POST['id'], $_POST['status']) && in_array($_POST['status'], ['approved', 'rejected'])) {
  $requestId = intval($_POST['id']);
  $status = $_POST['status'];
  $reviewedBy = $_SESSION['user_id'];

  // Ensure the database connection is established
  if (!$conn) {
    $response['message'] = 'Database connection failed.';
    echo json_encode($response);
    exit;
  }

  // Prepare update query with error handling
  $stmt = $conn->prepare("UPDATE download_requests SET status = ?, reviewed_by = ?, reviewed_at = NOW() WHERE id = ?");
  if ($stmt === false) {
    $response['message'] = 'Statement preparation failed: ' . $conn->error;
    echo json_encode($response);
    exit;
  }

  $stmt->bind_param("sii", $status, $reviewedBy, $requestId);

  if ($stmt->execute()) {
    $response = ['status' => 'success', 'message' => 'Request status updated successfully.'];
  } else {
    $response['message'] = 'Failed to execute query: ' . $stmt->error;
  }

  $stmt->close();
} else {
  $response['message'] = 'Invalid input parameters.';
}

header('Content-Type: application/json');
echo json_encode($response);

?>