<?php
require_once '../config.php';
require_once '../initialize.php';
require_once('../vendor/autoload.php'); // Composer autoloader

// Use PHPMailer with namespaces
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Login extends DBConnection
{
    private $settings;
    public function __construct()
    {
        global $_settings;
        $this->settings = $_settings;

        parent::__construct();
        ini_set('display_error', 1);
    }
    public function __destruct()
    {
        parent::__destruct();
    }
    public function index()
    {
        echo "<h1>Access Denied</h1> <a href='" . base_url . "'>Go Back.</a>";
    }
    public function login()
    {
        extract($_POST);

        // Step 1: Validate reCAPTCHA
        $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
        $secretKey = '6LfFJYcqAAAAANKGBiV1AlFMLMwj2wgAGifniAKO';
        $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';

        $response = file_get_contents($verifyUrl . '?secret=' . $secretKey . '&response=' . $recaptchaResponse);
        $responseKeys = json_decode($response, true);

        if (!$responseKeys['success']) {
            echo json_encode(['status' => 'captcha_failed', 'message' => 'reCAPTCHA validation failed.']);
            return;
        }

        // Step 2: Check user credentials
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $qry = $stmt->get_result();

        if ($qry->num_rows > 0) {
            $res = $qry->fetch_assoc();

            if (password_verify($password, $res['password'])) {
                // Check if account is verified
                if ($res['status'] != 1) {
                    echo json_encode(['status' => 'notverified', 'message' => 'Your account is not verified.']);
                    return;
                }

                // Generate token and expiry
                $token = bin2hex(random_bytes(32));
                $expiry = date('Y-m-d H:i:s', strtotime('+30 minutes'));

                // Insert into password_resets table
                $insertStmt = $this->conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE token = VALUES(token), expires_at = VALUES(expires_at)");
                $insertStmt->bind_param("sss", $res['username'], $token, $expiry);
                $insertStmt->execute();

                if ($insertStmt->affected_rows > 0) {
                    // Send verification email
                    $verificationLink = _base_url_ . "verify_login.php?token=" . urlencode($token);

                    if ($this->sendVerificationEmail($res['username'], $res['name'], $verificationLink)) {
                        echo json_encode(['status' => 'verify_email_sent']);
                    } else {
                        echo json_encode(['status' => 'error', 'message' => 'Unable to send verification email.']);
                    }
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Unable to generate verification token.']);
                }
            } else {
                echo json_encode(['status' => 'incorrect', 'message' => 'Invalid username or password.']);
            }
        } else {
            echo json_encode(['status' => 'incorrect', 'message' => 'Invalid username or password.']);
        }
    }

    private function sendVerificationEmail($email, $name, $verificationLink)
    {
        $mail = new PHPMailer(true);

        try {
            // SMTP Configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.example.com'; // Replace with your SMTP host
            $mail->SMTPAuth = true;
            $mail->Username = 'your_email@example.com'; // Replace with your email
            $mail->Password = 'your_password'; // Replace with your email password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Email Headers
            $mail->setFrom('no-reply@example.com', 'Admin Verification');
            $mail->addAddress($email, $name);

            // Email Content
            $mail->isHTML(true);
            $mail->Subject = 'Verify Your Login Attempt';
            $mail->Body = "
            <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9; max-width: 600px; margin: auto;'>
                <h2 style='color: #333;'>Hi $name,</h2>
                <p>A login attempt was made on your account. Please verify this attempt to secure your account.</p>
                <p>Click the button below to verify:</p>
                <a href='$verificationLink' style='display: inline-block; padding: 10px 20px; color: #fff; background-color: #007bff; text-decoration: none; border-radius: 5px;'>Verify Login Attempt</a>
                <p>If you did not attempt to log in, please ignore this email.</p>
            </div>
        ";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Mailer Error: " . $mail->ErrorInfo);
            return false;
        }
    }


    public function logout()
    {
        if ($this->settings->sess_des()) {
            redirect('admin/login.php');
        }
    }
    public function student_login()
    {
        session_start();
        extract($_POST);

        // Verify reCAPTCHA
        $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
        $secretKey = '6LfFJYcqAAAAANKGBiV1AlFMLMwj2wgAGifniAKO';
        $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';
        $response = file_get_contents($verifyUrl . '?secret=' . $secretKey . '&response=' . $recaptchaResponse);
        $responseKeys = json_decode($response, true);

        if (!$responseKeys['success']) {
            return json_encode([
                'status' => 'captcha_failed',
                'msg' => 'reCAPTCHA validation failed. Please try again or reload the page.'
            ]);
        }

        // Fetch user details by email
        $qry = $this->conn->prepare("SELECT *, CONCAT(lastname, ', ', firstname, ' ', middlename) AS fullname FROM student_list WHERE email = ?");
        $qry->bind_param("s", $email);
        $qry->execute();
        $result = $qry->get_result();

        if ($this->conn->error) {
            return json_encode([
                'status' => 'failed',
                'msg' => "An error occurred while fetching data. Error: " . $this->conn->error
            ]);
        }

        if ($result->num_rows > 0) {
            $res = $result->fetch_assoc();

            // Verify the password using bcrypt
            if (password_verify($password, $res['password'])) {
                if ($res['status'] == 1) {
                    // Set session variables for logged-in user
                    $_SESSION['user_logged_in'] = true;
                    $_SESSION['user_id'] = $res['id'];
                    foreach ($res as $k => $v) {
                        $this->settings->set_userdata($k, $v);
                    }
                    $this->settings->set_userdata('login_type', 2);

                    return json_encode(['status' => 'success']);
                } else {
                    return json_encode([
                        'status' => 'failed',
                        'msg' => 'Your account is not verified yet.'
                    ]);
                }
            } else {
                // Invalid password
                return json_encode([
                    'status' => 'failed',
                    'msg' => 'Invalid email or password.'
                ]);
            }
        } else {
            // No user found with the given email
            return json_encode([
                'status' => 'failed',
                'msg' => 'Invalid email or password.'
            ]);
        }
    }



    public function student_logout()
    {
        session_start(); // Start session if not already active
        $this->settings->sess_des(); // Call the updated session destroy method
        header("Location: ../login.php"); // Redirect to login page
        exit; // Ensure no further execution
    }




}
$action = !isset($_GET['f']) ? 'none' : strtolower($_GET['f']);
$auth = new Login();
switch ($action) {
    case 'login':
        echo $auth->login();
        break;
    case 'logout':
        echo $auth->logout();
        break;
    case 'student_login':
        echo $auth->student_login();
        break;
    case 'student_logout':
        echo $auth->student_logout();
        break;
    default:
        echo $auth->index();
        break;
}