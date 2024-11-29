<?php
require_once('../config.php');

if (isset($_GET['token'])) {
  $token = $_GET['token'];

  $stmt = $conn->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
  $stmt->bind_param("s", $token);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $reset = $result->fetch_assoc();

    // Mark the user as verified (optional)
    $updateStmt = $conn->prepare("UPDATE users SET status = 1 WHERE email = ?");
    $updateStmt->bind_param("s", $reset['email']);
    $updateStmt->execute();

    // Delete the token after use
    $deleteStmt = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
    $deleteStmt->bind_param("s", $token);
    $deleteStmt->execute();

    header('Location: ../admin/');
    exit;
  } else {
    echo "Invalid or expired token.";
  }
} else {
  echo "No token provided.";
}
?>