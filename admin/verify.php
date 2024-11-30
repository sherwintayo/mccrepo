<?php
require_once('../config.php');

if (isset($_GET['token'])) {
  $token = $_GET['token'];

  // Validate token
  $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token_hash = ? AND reset_token_hash IS NOT NULL");
  $stmt->bind_param("s", $token);
  $stmt->execute();
  $qry = $stmt->get_result();

  if ($qry->num_rows > 0) {
    $res = $qry->fetch_assoc();

    // Clear the token from the database
    $clearTokenStmt = $conn->prepare("UPDATE users SET reset_token_hash = NULL WHERE id = ?");
    $clearTokenStmt->bind_param("i", $res['id']);
    $clearTokenStmt->execute();

    // Populate the session data
    $_SESSION['userdata'] = [];
    foreach ($res as $k => $v) {
      if (!is_numeric($k) && $k != 'password') {
        $_SESSION['userdata'][$k] = $v;
      }
    }
    $_SESSION['login_type'] = 1; // Set login type as admin

    // Redirect to the admin dashboard
    header("Location: ../admin/index.php");
  } else {
    echo "Invalid or expired token.";
  }
} else {
  echo "No token provided.";
}
