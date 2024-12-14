<?php
require_once('../config.php');

if (isset($_GET['token'])) {
  $token = htmlspecialchars($_GET['token'], ENT_QUOTES, 'UTF-8');

  // Validate token and check expiry
  $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token_hash = ? AND reset_token_expiry > NOW()");
  $stmt->bind_param("s", $token);
  $stmt->execute();
  $qry = $stmt->get_result();

  if ($qry->num_rows > 0) {
    $res = $qry->fetch_assoc();

    // Clear token and expiry
    $clearTokenStmt = $conn->prepare("UPDATE users SET reset_token_hash = NULL, reset_token_expiry = NULL WHERE id = ?");
    $clearTokenStmt->bind_param("i", $res['id']);
    $clearTokenStmt->execute();

    // Start session if not already started
    if (session_status() == PHP_SESSION_NONE) {
      session_start();
    }

    // Set session data excluding sensitive fields
    $_SESSION['userdata'] = [];
    foreach ($res as $key => $value) {
      if (!is_numeric($key) && $key != 'password') {
        $_SESSION['userdata'][$key] = $value;
      }
    }

    // Redirect based on user role
    if ($res['role'] === 'admin') {
      $_SESSION['userdata']['login_type'] = 1; // Admin
      header("Location: ../admin/index.php");
    } else {
      header("Location: ../dashboard.php"); // Non-admin dashboard
    }
    exit;
  } else {
    echo "Invalid or expired token.";
    exit;
  }
} else {
  echo "No token provided.";
  exit;
}
?>