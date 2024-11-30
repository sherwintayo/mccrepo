<?php
require_once('../config.php');

if (isset($_GET['token'])) {
  $token = $_GET['token'];

  // Validate token
  $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token_hash = ?");
  $stmt->bind_param("s", $token);
  $stmt->execute();
  $qry = $stmt->get_result();

  if ($qry->num_rows > 0) {
    $res = $qry->fetch_assoc();

    // Clear the token and log in the user
    $clearTokenStmt = $conn->prepare("UPDATE users SET reset_token_hash = NULL WHERE id = ?");
    $clearTokenStmt->bind_param("i", $res['id']);
    $clearTokenStmt->execute();

    // Start session and set userdata
    session_start(); // Ensure session is started
    $_SESSION['userdata'] = [
      'id' => $res['id'],
      'username' => $res['username'], // Optional for convenience
      'login_type' => $res['login_type'] ?? 1 // Default to admin type
    ];

    // Redirect to admin/index.php
    header("Location: ../admin/index.php");
    exit; // Ensure no further execution
  } else {
    echo "Invalid or expired token.";
    exit;
  }
} else {
  echo "No token provided.";
  exit;
}
