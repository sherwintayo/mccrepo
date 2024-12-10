<?php
require_once('../config.php');
require_once('../vendor/autoload.php'); // Include Composer's autoload for PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = $_POST['email'];

  // Check if the user exists
  $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
  $stmt->bind_param('s', $email);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows > 0) {
    // Generate a 6-digit OTP
    $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes')); // OTP valid for 10 minutes

    // Update OTP and expiration in the database
    $stmt->bind_result($user_id);
    $stmt->fetch();
    $update = $conn->prepare("UPDATE users SET otp_code = ?, otp_expires_at = ? WHERE id = ?");
    $update->bind_param('ssi', $otp, $expires_at, $user_id);
    $update->execute();

    // Send the OTP via email using PHPMailer
    $mail = new PHPMailer(true);

    try {
      // Configure PHPMailer
      $mail->isSMTP();
      $mail->Host = 'smtp.gmail.com';
      $mail->SMTPAuth = true;
      $mail->Username = 'sherwintayo08@gmail.com';
      $mail->Password = "jlbm iyke zqjv zwtr";
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      $mail->Port = 587;

      // Email details
      $mail->setFrom('your_email@gmail.com', 'Your Application Name'); // Replace with your sender email/name
      $mail->addAddress($email);

      $mail->isHTML(true);
      $mail->Subject = 'Your OTP for Password Reset';
      $mail->Body = "
                <p>Dear User,</p>
                <p>Your One-Time Password (OTP) for resetting your password is:</p>
                <h2>$otp</h2>
                <p>This OTP is valid for 10 minutes.</p>
                <p>If you did not request this, please ignore this email.</p>
                <br>
                <p>Regards,</p>
                <p>Your Application Team</p>
            ";

      // Send email
      $mail->send();

      echo json_encode(['success' => true, 'message' => 'OTP sent to your email.']);
    } catch (Exception $e) {
      // Handle email sending failure
      echo json_encode(['success' => false, 'message' => 'Failed to send OTP. Please try again later.', 'error' => $mail->ErrorInfo]);
    }
  } else {
    echo json_encode(['success' => false, 'message' => 'Email address not found.']);
  }
} else {
  echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>