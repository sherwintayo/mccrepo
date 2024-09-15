<?php
// Enable error reporting
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

require_once('./config.php');
require_once('./vendor/autoload.php'); // Composer autoloader

// Use PHPMailer with namespaces
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');  // Set JSON response type

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $domain = "@mcclawis.edu.ph";

    // Server-side validation for email domain
    if (!str_ends_with($email, $domain)) {
        echo json_encode(['status' => 'error', 'message' => "Invalid email address. Only emails ending with $domain are allowed."]);
        exit();
    }

    // Check if email exists in the users table
    $stmt = $conn->prepare("SELECT id, username FROM msaccount WHERE username = ?");
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
        $update = $conn->prepare("UPDATE msaccount SET reset_token_hash = ?, reset_token_hash_expires_at = ? WHERE id = ?");
        $update->bind_param('ssi', $token_hash, $expires_at, $user_id);
        $update->execute();

        // Send the reset link via PHPMailer
        $reset_link = base_url . "register.php?token=$token";
        
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'your_email@gmail.com';
            $mail->Password = 'your_password';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('your_email@gmail.com', 'MCC Repositories');
            $mail->addAddress($email);

            // Email content
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body    = "Hi $username,<br><br>Click the link below to reset your password:<br><a href='$reset_link'>$reset_link</a><br><br>The link is valid for 1 hour.";

            // Send email
            $mail->send();
            echo json_encode(['status' => 'success', 'message' => 'Reset link sent to your email.']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => "Mailer Error: {$mail->ErrorInfo}"]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Email not found.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
