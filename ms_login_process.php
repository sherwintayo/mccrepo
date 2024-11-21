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

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';

    // reCAPTCHA Secret Key
    $secretKey = '6LdkGoUqAAAAABTZgD529DslANXkDOxDb0-8mV0T'; // Replace with your secret key
    $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';

    // Validate reCAPTCHA response
    $response = file_get_contents("$verifyUrl?secret=$secretKey&response=$recaptchaResponse");
    $responseKeys = json_decode($response, true);

    // Debugging reCAPTCHA response
    if (!$responseKeys['success']) {
        echo json_encode([
            'status' => 'error',
            'message' => 'reCAPTCHA validation failed.',
            'debug' => $responseKeys // Include this for debugging
        ]);
        exit();
    }


    // Server-side validation
    $domain = "@mcclawis.edu.ph";
    if (!str_ends_with($email, $domain)) {
        echo json_encode(['status' => 'error', 'message' => "Invalid email address. Only emails ending with $domain are allowed."]);
        exit();
    }

    // Check if email exists
    $stmt = $conn->prepare("SELECT id, username FROM msaccount WHERE username = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Generate token
        $token = bin2hex(random_bytes(32));
        $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $token_hash = hash('sha256', $token);

        $stmt->bind_result($user_id, $username);
        $stmt->fetch();
        $update = $conn->prepare("UPDATE msaccount SET reset_token_hash = ?, reset_token_hash_expires_at = ? WHERE id = ?");
        $update->bind_param('ssi', $token_hash, $expires_at, $user_id);
        $update->execute();

        // PHPMailer
        $register_link = base_url . "registration.php?token=$token";
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'sherwintayo08@gmail.com';
            $mail->Password = 'tbez conr sxbn fwuk';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Email content
            $mail->setFrom('no-reply@yourdomain.com', 'MCC Repositories');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Registration Form';
            $mail->Body = "Hi $username,<br><br>Please click the link below to Register:<br><a href='$register_link'>$register_link</a><br><br>If you did not request this, please ignore this email.<br><br>Thanks,<br>Your Company";
            $mail->AltBody = "Hi $username,\n\nPlease click the link below to Register:\n$register_link\n\nIf you did not request this, please ignore this email.\n\nThanks,\nYour Company";
            $mail->send();
            echo json_encode(['status' => 'success', 'message' => 'Register link sent to your email.']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => "Mailer Error: {$mail->ErrorInfo}"]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Email not found.']);
    }
}
?>