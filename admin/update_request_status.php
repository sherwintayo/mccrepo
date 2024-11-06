<?php
session_start();
require_once('../config.php');

$response = ['status' => 'error', 'message' => 'Invalid request'];

// Debugging: Check session and connection
if (!isset($_SESSION['user_id'])) {
  error_log("User ID not found in session.");
  $response['message'] = 'User not logged in.';
  echo json_encode($response);
  exit;
}

if (!$conn) {
  error_log("Database connection error: " . mysqli_connect_error());
  $response['message'] = 'Database connection failed.';
  echo json_encode($response);
  exit;
}

if (isset($_POST['id'], $_POST['status']) && in_array($_POST['status'], ['approved', 'rejected'])) {
  $requestId = intval($_POST['id']);
  $status = $_POST['status'];
  $reviewedBy = $_SESSION['user_id'];

  // Prepare the SQL statement
  $stmt = $conn->prepare("UPDATE download_requests SET status = ?, reviewed_by = ?, reviewed_at = NOW() WHERE id = ?");
  if ($stmt === false) {
    error_log("Statement preparation failed: " . $conn->error);
    $response['message'] = 'Statement preparation failed.';
    echo json_encode($response);
    exit;
  }

  $stmt->bind_param("sii", $status, $reviewedBy, $requestId);

  // Execute the statement and handle errors
  if ($stmt->execute()) {
    $response = ['status' => 'success', 'message' => 'Request status updated successfully.'];
  } else {
    error_log("Failed to execute query: " . $stmt->error);
    $response['message'] = 'Failed to execute query: ' . $stmt->error;
  }

  $stmt->close();
} else {
  $response['message'] = 'Invalid input parameters.';
}

header('Content-Type: application/json');
echo json_encode($response);
