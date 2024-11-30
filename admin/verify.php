<?php
require_once('../config.php');
session_start();

if (isset($_GET['token'])) {
  $token = $_GET['token'];

  // Validate token
  $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token_hash = ? AND reset_token_expires_at >= NOW()");
  $stmt->bind_param("s", $token);
  $stmt->execute();
  $qry = $stmt->get_result();

  if ($qry->num_rows > 0) {
    $user = $qry->fetch_assoc();

    // Clear the token
    $clearTokenStmt = $conn->prepare("UPDATE users SET reset_token_hash = NULL, reset_token_expires_at = NULL WHERE id = ?");
    $clearTokenStmt->bind_param("i", $user['id']);
    $clearTokenStmt->execute();

    // Set user session data
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['firstname'] = $user['firstname'];
    $_SESSION['lastname'] = $user['lastname'];
    $_SESSION['login_type'] = 1; // Admin user

    // Redirect to admin dashboard
    header("Location: ../admin/");
    exit;
  } else {
    echo "Invalid or expired token.";
  }
} else {
  echo "No token provided.";
}
