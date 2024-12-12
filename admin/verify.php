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

    // Clear the token from the database
    $clearTokenStmt = $conn->prepare("UPDATE users SET reset_token_hash = NULL WHERE id = ?");
    $clearTokenStmt->bind_param("i", $res['id']);
    $clearTokenStmt->execute();

    // Generate a unique session token
    $sessionToken = bin2hex(random_bytes(32));
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $ipAddress = $_SERVER['REMOTE_ADDR'];

    // Store the session in the database
    $sessionStmt = $conn->prepare("
            INSERT INTO sessions (user_id, session_token, user_agent, ip_address, is_valid) 
            VALUES (?, ?, ?, ?, 1)
        ");
    $sessionStmt->bind_param("isss", $res['id'], $sessionToken, $userAgent, $ipAddress);
    $sessionStmt->execute();

    // Start session if not already started
    if (session_status() == PHP_SESSION_NONE) {
      session_start();
    }

    // Set session data
    $_SESSION['userdata'] = $res;
    $_SESSION['userdata']['session_token'] = $sessionToken; // Store session token
    $_SESSION['userdata']['login_type'] = 1; // Set login type as admin

    // Redirect to the admin dashboard
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
?>