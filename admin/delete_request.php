<?php
// Include database connection
require_once('../config.php'); // Adjust the path to your database configuration file

$response = ['status' => 'error', 'message' => 'Invalid request'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
  $id = intval($_POST['id']);

  // Prepare SQL query to delete the request
  $stmt = $conn->prepare("DELETE FROM download_requests WHERE id = ?");
  $stmt->bind_param("i", $id);

  if ($stmt->execute()) {
    $response = ['status' => 'success', 'message' => 'Request deleted successfully'];
  } else {
    $response['message'] = 'Failed to delete request';
  }

  $stmt->close();
} else {
  $response['message'] = 'Request ID is missing or invalid';
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>