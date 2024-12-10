<?php
require_once('../config.php');
require_once('../vendor/autoload.php'); // Composer autoloader

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    // Check if email exists in the users table
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE username = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $username);
        $stmt->fetch();

        // Generate reset token and expiration
        $token = bin2hex(random_bytes(32));
        $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $token_hash = hash('sha256', $token);

        // Store token and expiration in the database
        $update = $conn->prepare("UPDATE users SET reset_token_hash = ?, reset_token_expires_at = ? WHERE id = ?");
        $update->bind_param('ssi', $token_hash, $expires_at, $user_id);
        $update->execute();

        // Send the reset link via PHPMailer
        $reset_link = base_url . "admin/reset_password.php?token=$token";
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'sherwintayo08@gmail.com';
            $mail->Password = "jlbm iyke zqjv zwtr";
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('sherwintayo08@gmail.com', 'MCC Repositories');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body = "Hi $username,<br><br>Click the link below to reset your password:<br><a href='$reset_link'>$reset_link</a><br><br>The link is valid for 1 hour.";

            $mail->send();
            echo json_encode(['success' => true, 'message' => 'Reset link sent to your email.']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to send email. Please try again later.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Email not found.']);
    }
}
?>