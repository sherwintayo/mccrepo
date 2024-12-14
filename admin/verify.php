<?php
require_once('../config.php');

// Check if token is provided
if (isset($_GET['token'])) {
  $token = htmlspecialchars($_GET['token'], ENT_QUOTES, 'UTF-8');

  // Validate token and check expiry
  $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token_hash = ? AND reset_token_expiry > NOW()");
  $stmt->bind_param("s", $token);
  $stmt->execute();
  $qry = $stmt->get_result();

  if ($qry->num_rows > 0) {
    $res = $qry->fetch_assoc();

    // Clear the token and its expiry from the database
    $clearTokenStmt = $conn->prepare("UPDATE users SET reset_token_hash = NULL, reset_token_expiry = NULL WHERE id = ?");
    $clearTokenStmt->bind_param("i", $res['id']);
    $clearTokenStmt->execute();

    // Start session if not started
    if (session_status() == PHP_SESSION_NONE) {
      session_start();
    }

    // Set session data for user, excluding sensitive fields
    $_SESSION['userdata'] = [];
    foreach ($res as $key => $value) {
      if (!is_numeric($key) && $key != 'password') { // Exclude numeric keys and the password field
        $_SESSION['userdata'][$key] = $value;
      }
    }

    // Set login type as admin
    $_SESSION['userdata']['login_type'] = 1;

    // Redirect to the admin dashboard
    header("Location: ../admin/index.php");
    exit;
  } else {
    // Invalid or expired token
    echo "Invalid or expired token.";
    exit;
  }
} else {
  // No token provided
  echo "No token provided.";
  exit;
}
