<?php
require_once '../config.php';
require '../vendor/autoload.php'; // Adjust path to your PHPMailer

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

        // Verify reCAPTCHA
        $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
        $secretKey = '6LfFJYcqAAAAANKGBiV1AlFMLMwj2wgAGifniAKO'; // Replace with your secret key
        $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';

        // Send request to Google API
        $response = file_get_contents($verifyUrl . '?secret=' . $secretKey . '&response=' . $recaptchaResponse);
        $responseKeys = json_decode($response, true);

        if (!$responseKeys['success']) {
            echo json_encode(['status' => 'error', 'message' => 'reCAPTCHA validation failed.']);
            return;
        }

        // Proceed with normal login logic
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $qry = $stmt->get_result();

        if ($qry->num_rows > 0) {
            $res = $qry->fetch_assoc();

            if (password_verify($password, $res['password'])) {
                if ($res['status'] != 1) {
                    echo json_encode(['status' => 'error', 'message' => 'Your account is not verified.']);
                    return;
                }

                // Generate OTP
                $otp = random_int(100000, 999999);
                $_SESSION['otp'] = $otp;
                $_SESSION['otp_expiry'] = time() + 90; // OTP expires in 90 seconds
                $_SESSION['temp_username'] = $username;

                // Send OTP via email
                $to = $res['email'];
                $subject = "Your Admin OTP Code";
                $message = "Your OTP code is: $otp. It is valid for 1 minute and 30 seconds.";
                $headers = "From: admin@yourdomain.com\r\n";
                $headers .= "Content-Type: text/plain; charset=UTF-8";



                $mail = new PHPMailer();
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'sherwintayo08@gmail.com'; // Replace with your email
                $mail->Password = 'jlbm iyke zqjv zwtr'; // Replace with your password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('MADRIDEJOS COMMUNITY COLLEGE', 'Admin');
                $mail->addAddress($to);
                $mail->Subject = $subject;
                $mail->Body = $message;

                if (!$mail->send()) {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to send OTP.']);
                } else {
                    echo json_encode(['status' => 'otp_sent']);
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

                echo json_encode(['status' => 'success', 'message' => 'Welcome to the admin panel!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Incorrect username or password.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'User not found.']);
        }
    }

    public function verify_otp()
    {
        session_start();
        $userInputOtp = $_POST['otp'] ?? '';

        if (!isset($_SESSION['otp']) || time() > $_SESSION['otp_expiry']) {
            // OTP expired
            session_destroy();
            echo json_encode(['status' => 'expired', 'message' => 'OTP has expired. Please login again.']);
            return;
        }

        if ($_SESSION['otp'] == $userInputOtp) {
            // OTP verified
            unset($_SESSION['otp']);
            unset($_SESSION['otp_expiry']);
            $_SESSION['username'] = $_SESSION['temp_username'];
            unset($_SESSION['temp_username']);

            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid OTP.']);
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