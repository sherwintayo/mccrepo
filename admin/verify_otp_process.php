<?php
require_once('../config.php');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $otp = $_POST['otp'];

  // Check if the OTP exists and is valid
  $stmt = $conn->prepare("SELECT id FROM users WHERE otp_code = ? AND otp_expires_at > NOW()");
  $stmt->bind_param('s', $otp);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows > 0) {
    $stmt->bind_result($user_id);
    $stmt->fetch();

    // Generate a reset token for the user
    $token = bin2hex(random_bytes(32));
    $token_hash = hash('sha256', $token);
    $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

    $update = $conn->prepare("UPDATE users SET reset_token_hash = ?, reset_token_expires_at = ?, otp_code = NULL, otp_expires_at = NULL WHERE id = ?");
    $update->bind_param('ssi', $token_hash, $expires_at, $user_id);
    $update->execute();

    echo json_encode(['success' => true, 'message' => 'OTP verified successfully.', 'token' => $token]);
  } else {
    echo json_encode(['success' => false, 'message' => 'Invalid or expired OTP.']);
  }
} else {
  echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>