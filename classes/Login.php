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

        $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
        $secretKey = '6LfFJYcqAAAAANKGBiV1AlFMLMwj2wgAGifniAKO';
        $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';

        $response = file_get_contents($verifyUrl . '?secret=' . $secretKey . '&response=' . $recaptchaResponse);
        $responseKeys = json_decode($response, true);

        if (!$responseKeys['success']) {
            echo json_encode(['status' => 'captcha_failed', 'message' => 'reCAPTCHA validation failed.']);
            return;
        }

        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $qry = $stmt->get_result();

        if ($qry->num_rows > 0) {
            $res = $qry->fetch_assoc();

            if (password_verify($password, $res['password'])) {
                if ($res['status'] != 1) {
                    echo json_encode(['status' => 'notverified', 'message' => 'Your account is not verified.']);
                    return;
                }

                foreach ($res as $k => $v) {
                    if (!is_numeric($k) && $k != 'password') {
                        $this->settings->set_userdata($k, $v);
                    }
                }
                $this->settings->set_userdata('login_type', 1);

                if (password_needs_rehash($res['password'], PASSWORD_DEFAULT)) {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $updateStmt = $this->conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $updateStmt->bind_param('si', $newHash, $res['id']);
                    $updateStmt->execute();
                }

                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'incorrect', 'message' => 'Invalid username or password.']);
            }
        } else {
            echo json_encode(['status' => 'incorrect', 'message' => 'Invalid username or password.']);
        }
    }


    public function email_login()
    {
        global $conn;
        extract($_POST);

        // Validate reCAPTCHA
        $secretKey = '6LfFJYcqAAAAANKGBiV1AlFMLMwj2wgAGifniAKO'; // Replace with your secret key
        $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';

        $response = file_get_contents($verifyUrl . '?secret=' . $secretKey . '&response=' . $recaptchaResponse);
        $responseKeys = json_decode($response, true);

        if (!$responseKeys['success']) {
            echo json_encode(['status' => 'error', 'message' => 'reCAPTCHA validation failed.']);
            return;
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid email address.']);
            return;
        }

        // Check if the email exists in the database
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $qry = $stmt->get_result();

        if ($qry->num_rows > 0) {
            $user = $qry->fetch_assoc();

            // Generate a secure token and expiry timestamp
            $token = bin2hex(random_bytes(32)); // 64-character secure random token
            $expiry = date('Y-m-d H:i:s', strtotime('+1 minute 30 seconds')); // Token valid for 1:30 minutes

            // Update the token and expiry in the database
            $updateStmt = $conn->prepare("UPDATE users SET reset_token_hash = ?, reset_token_expires_at = ? WHERE id = ?");
            $updateStmt->bind_param("ssi", $token, $expiry, $user['id']);
            $updateStmt->execute();

            if ($updateStmt->affected_rows > 0) {
                // Generate the login link
                $loginLink = base_url . "admin/login.php?token=" . urlencode($token);

                // Send the login link via email
                if ($this->sendEmailLoginLink($email, $user['firstname'], $loginLink)) {
                    echo json_encode(['status' => 'success', 'message' => 'Login link sent successfully.']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to send the login email.']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Unable to generate login token.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Email not registered.']);
        }
    }


    private function sendEmailLoginLink($email, $name, $loginLink)
    {
        $mail = new PHPMailer(true);

        try {
            // SMTP Configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'sherwintayo08@gmail.com';
            $mail->Password = 'jlbm iyke zqjv zwtr';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Email Details
            $mail->setFrom('no-reply@example.com', 'Admin Login');
            $mail->addAddress($email, $name);

            // Email Content
            $mail->isHTML(true);
            $mail->Subject = 'Your Login Link';
            $mail->Body = "
            <div>
                <p>Hi $name,</p>
                <p>Click the link below to log in:</p>
                <a href='$loginLink'>Login Now</a>
                <p>This link will expire in 1 minute 30 seconds.</p>
            </div>
        ";

            $mail->send();
            return true;
        } catch (Exception $e) {
            // Log the error for debugging
            error_log("PHPMailer Error: " . $mail->ErrorInfo);
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
    case 'email_login':
        echo $auth->email_login();
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