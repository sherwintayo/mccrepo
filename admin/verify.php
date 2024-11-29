<?php
require_once('../config.php'); // Ensure this file is included for $conn initialization

if (isset($_GET['token'])) {
  $token = $_GET['token'];

  // Validate token
  $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token_hash = ? AND reset_token_expires_at >= NOW()");
  $stmt->bind_param("s", $token);
  $stmt->execute();
  $qry = $stmt->get_result();

  if ($qry->num_rows > 0) {
    $user = $qry->fetch_assoc();

    // Clear the token from the users table
    $clearTokenStmt = $conn->prepare("UPDATE users SET reset_token_hash = NULL, reset_token_expires_at = NULL WHERE id = ?");
    $clearTokenStmt->bind_param("i", $user['id']);
    $clearTokenStmt->execute();

    // Set session data
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];

    // Redirect to admin dashboard
    header("Location: ../admin/");
    exit;
  } else {
    echo "Invalid or expired token.";
  }
} else {
  echo "No token provided.";
}
