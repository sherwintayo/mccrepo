<?php
require_once('../config.php');

if (isset($_GET['token'])) {
  $token = $_GET['token'];

  // Validate token
  $stmt = $this->conn->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at >= NOW()");
  $stmt->bind_param("s", $token);
  $stmt->execute();
  $qry = $stmt->get_result();

  if ($qry->num_rows > 0) {
    $res = $qry->fetch_assoc();

    // Get user details using the email
    $userStmt = $this->conn->prepare("SELECT * FROM users WHERE username = ?");
    $userStmt->bind_param("s", $res['email']);
    $userStmt->execute();
    $userResult = $userStmt->get_result();

    if ($userResult->num_rows > 0) {
      $user = $userResult->fetch_assoc();

      // Log in the user by setting session data
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['username'] = $user['username'];

      // Clear the token from password_resets table
      $deleteStmt = $this->conn->prepare("DELETE FROM password_resets WHERE token = ?");
      $deleteStmt->bind_param("s", $token);
      $deleteStmt->execute();

      // Redirect to admin dashboard
      header("Location: ../admin/");
    } else {
      echo "User not found.";
    }
  } else {
    echo "Invalid or expired token.";
  }
} else {
  echo "No token provided.";
}
