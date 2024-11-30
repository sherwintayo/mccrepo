<?php
require_once('../config.php');

if (isset($_GET['token'])) {
  $token = $_GET['token'];

  // Validate token
  $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token_hash = ? AND login_token IS NOT NULL");
  $stmt->bind_param("s", $token);
  $stmt->execute();
  $qry = $stmt->get_result();

  if ($qry->num_rows > 0) {
    $res = $qry->fetch_assoc();

    // Clear the token from the database
    $clearTokenStmt = $conn->prepare("UPDATE users SET login_token = NULL WHERE id = ?");
    $clearTokenStmt->bind_param("i", $res['id']);
    $clearTokenStmt->execute();

    // Process user session
    foreach ($res as $k => $v) {
      if (!is_numeric($k) && $k != 'password') {
        $_SESSION[$k] = $v;
      }
    }
    $_SESSION['login_type'] = 1; // Set login type as admin

    // Redirect to the admin dashboard
    header("Location: ../admin/");
  } else {
    echo "Invalid or expired token.";
  }
} else {
  echo "No token provided.";
}
?>