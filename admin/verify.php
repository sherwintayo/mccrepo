<?php
if (isset($_GET['token'])) {
  $token = $_GET['token'];

  // Validate token
  $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token_hash = ?");
  $stmt->bind_param("s", $token);
  $stmt->execute();
  $qry = $stmt->get_result();

  if ($qry->num_rows > 0) {
    $res = $qry->fetch_assoc();

    // Clear the token from the database
    $clearTokenStmt = $conn->prepare("UPDATE users SET reset_token_hash = NULL WHERE id = ?");
    $clearTokenStmt->bind_param("i", $res['id']);
    $clearTokenStmt->execute();

    // Start session if not started
    if (session_status() == PHP_SESSION_NONE) {
      session_start();
    }

    // Set session data for the user
    foreach ($res as $k => $v) {
      if (!is_numeric($k) && $k != 'password') {
        $_SESSION['userdata'][$k] = $v;
      }
    }

    // Check login type and redirect
    if ($_SESSION['userdata']['login_type'] == 1) { // Admin
      header("Location: ../admin/index.php");
      // } else {
      //   header("Location: ../user/dashboard.php"); // Non-admin dashboard
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