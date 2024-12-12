<?php
require_once('../config.php');

if (isset($_GET['token'])) {
  $token = $_GET['token'];

  $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token_hash = ?");
  $stmt->bind_param("s", $token);
  $stmt->execute();
  $qry = $stmt->get_result();

  if ($qry->num_rows > 0) {
    $res = $qry->fetch_assoc();

    $clearTokenStmt = $conn->prepare("UPDATE users SET reset_token_hash = NULL WHERE id = ?");
    $clearTokenStmt->bind_param("i", $res['id']);
    $clearTokenStmt->execute();

    if (session_status() == PHP_SESSION_NONE) {
      session_start();
    }

    $sessionStmt = $conn->prepare("SELECT session_token FROM sessions WHERE user_id = ? AND is_valid = 1");
    $sessionStmt->bind_param("i", $res['id']);
    $sessionStmt->execute();
    $session = $sessionStmt->get_result()->fetch_assoc();

    if ($session) {
      $_SESSION['userdata'] = $res;
      $_SESSION['userdata']['session_token'] = $session['session_token'];
      $_SESSION['userdata']['login_type'] = 1;

      header("Location: ../admin/index.php");
      exit;
    } else {
      echo "Invalid session token.";
      exit;
    }
  } else {
    echo "Invalid or expired token.";
    exit;
  }
} else {
  echo "No token provided.";
  exit;
}
?>