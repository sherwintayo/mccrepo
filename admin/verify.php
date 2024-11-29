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
    $clearTokenStmt = $conn->prepare("UPDATE users SET reset_token_hash= NULL WHERE id = ?");
    $clearTokenStmt->bind_param("i", $res['id']);
    $clearTokenStmt->execute();

    // Set user session and redirect to admin
    $_SESSION['user_id'] = $res['id'];
    header("Location: ../admin/");
  } else {
    echo "Invalid or expired token.";
  }
} else {
  echo "No token provided.";
}
