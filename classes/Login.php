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
        header('Content-Type: application/json; charset=utf-8');
        extract($_POST);

        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $currentTime = time();

        // Validate geolocation fields
        if (empty($latitude) || empty($longitude)) {
            echo json_encode(['status' => 'error', 'message' => 'Geolocation is required.']);
            return;
        }

        try {
            // Validate reCAPTCHA response
            $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
            $secretKey = '6LcvKpIqAAAAAERzz2_imzASHXTELXAjpOEGSoQT';
            $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $verifyUrl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['secret' => $secretKey, 'response' => $recaptchaResponse]));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);

            $responseKeys = json_decode($response, true);

            if (!$responseKeys['success'] || $responseKeys['score'] < 0.5) {
                return json_encode([
                    'status' => 'failed',
                    'msg' => 'reCAPTCHA validation failed. Please try again.'
                ]);
            }

            // Step 2: Check IP block status
            $checkAttemptStmt = $this->conn->prepare("SELECT attempts, blocked_until FROM Login_Attempt WHERE ip_address = ?");
            $checkAttemptStmt->bind_param("s", $ipAddress);
            $checkAttemptStmt->execute();
            $attemptResult = $checkAttemptStmt->get_result();
            $attemptData = $attemptResult->fetch_assoc();

            if ($attemptData && $attemptData['blocked_until'] > $currentTime) {
                $remainingTime = $attemptData['blocked_until'] - $currentTime;
                echo json_encode([
                    'status' => 'blocked',
                    'message' => 'Too many failed attempts. Please try again later.',
                    'remaining_time' => $remainingTime
                ]);
                return;
            }


            // Step 3: Check user credentials
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $qry = $stmt->get_result();

            if ($qry->num_rows > 0) {
                $res = $qry->fetch_assoc();

                if (password_verify($password, $res['password'])) {
                    // Account verification check
                    if ($res['status'] != 1) {
                        echo json_encode(['status' => 'notverified', 'message' => 'Your account is not verified.']);
                        return;
                    }

                    // Reset login attempts on successful login
                    $resetAttemptStmt = $this->conn->prepare("DELETE FROM Login_Attempt WHERE ip_address = ?");
                    $resetAttemptStmt->bind_param("s", $ipAddress);
                    $resetAttemptStmt->execute();

                    // Log login activity
                    $userAgent = $_SERVER['HTTP_USER_AGENT'];
                    $logStmt = $this->conn->prepare("
                        INSERT INTO login_activity (user_id, ip_address, user_agent) 
                        VALUES (?, ?, ?)
                    ");
                    $logStmt->bind_param("iss", $res['id'], $ipAddress, $userAgent);
                    $logStmt->execute();



                    // Log login activity
                    $ipAddress = $_SERVER['REMOTE_ADDR'];
                    $userAgent = $_SERVER['HTTP_USER_AGENT'];

                    $logStmt = $this->conn->prepare("
                            INSERT INTO login_activity (user_id, ip_address, user_agent) 
                            VALUES (?, ?, ?)");
                    $logStmt->bind_param("iss", $res['id'], $ipAddress, $userAgent);
                    $logStmt->execute();

                    // Generate a unique verification token
                    $token = bin2hex(random_bytes(16));
                    $updateTokenStmt = $this->conn->prepare("UPDATE users SET reset_token_hash = ? WHERE id = ?");
                    $updateTokenStmt->bind_param('si', $token, $res['id']);
                    $updateTokenStmt->execute();

                    // Send verification email
                    $verificationLink = base_url . "admin/verify.php?token=" . urlencode($token);

                    if ($this->sendVerificationEmail($res['username'], $res['firstname'], $verificationLink, $res)) {
                        error_log("Verification email sent successfully to {$res['username']}");
                        echo json_encode(['status' => 'verify_email_sent']);
                        return;
                    } else {
                        echo json_encode(['status' => 'error', 'message' => 'Unable to send verification email.']);
                    }
                } else {
                    echo json_encode(['status' => 'incorrect', 'message' => 'Invalid username or password.']);
                }
            } else {
                echo json_encode(['status' => 'incorrect', 'message' => 'Invalid username or password.']);
            }
            // Step 4: Handle failed login attempt
            $attempts = ($attemptData['attempts'] ?? 0) + 1;

            if ($attemptData) {
                // Update existing attempt record
                $updateAttemptStmt = $this->conn->prepare("
                            UPDATE Login_Attempt 
                            SET attempts = ?, blocked_until = ?, latitude = ?, longitude = ? 
                            WHERE ip_address = ?
                        ");
                $blockedUntil = $attempts >= 3 ? $currentTime + 60 : $attemptData['blocked_until'];
                $updateAttemptStmt->bind_param("issss", $attempts, $blockedUntil, $latitude, $longitude, $ipAddress);
                $updateAttemptStmt->execute();
            } else {
                // Insert new attempt record
                $insertAttemptStmt = $this->conn->prepare("
                            INSERT INTO Login_Attempt (ip_address, attempts, blocked_until, latitude, longitude) 
                            VALUES (?, ?, ?, ?, ?)
                        ");
                $blockedUntil = $attempts >= 3 ? $currentTime + 60 : 0;
                $insertAttemptStmt->bind_param("sisss", $ipAddress, $attempts, $blockedUntil, $latitude, $longitude);
                $insertAttemptStmt->execute();
            }

            // Update block_count in login_attempt_history every 3 attempts
            if ($attempts % 3 === 0) {
                $historyStmt = $this->conn->prepare("
                            INSERT INTO login_attempt_history (ip_address, block_count) 
                            VALUES (?, 1)
                            ON DUPLICATE KEY UPDATE block_count = block_count + 1
                        ");
                $historyStmt->bind_param("s", $ipAddress);
                $historyStmt->execute();
            }

            // Return appropriate response
            if ($attempts >= 3) {
                echo json_encode([
                    'status' => 'blocked',
                    'message' => 'Too many failed attempts. Please try again later.',
                    'remaining_time' => 60
                ]);
            } else {
                echo json_encode(['status' => 'incorrect', 'message' => 'Invalid username or password.', 'attempts' => $attempts]);
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'An unexpected error occurred.']);
        }
    }

    private function sendVerificationEmail($email, $name, $verificationLink, $userData)
    {
        $mail = new PHPMailer(true);

        try {
            // SMTP Configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP host
            $mail->SMTPAuth = true;
            $mail->Username = 'sherwintayo08@gmail.com'; // Replace with your email
            $mail->Password = 'jlbm iyke zqjv zwtr'; // Replace with your email password
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
                <hr>
                <p><strong>Your Details:</strong></p>
                <ul>
                    <li><strong>Name:</strong> {$userData['firstname']} {$userData['lastname']}</li>
                    <li><strong>Username:</strong> {$userData['username']}</li>
                </ul>
            </div>
        ";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Mailer Error: " . $mail->ErrorInfo);
            return false;
        }
    }


    // public function login()
    // {
    //     extract($_POST);

    //     $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
    //     $secretKey = '6LfFJYcqAAAAANKGBiV1AlFMLMwj2wgAGifniAKO';
    //     $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';

    //     $response = file_get_contents($verifyUrl . '?secret=' . $secretKey . '&response=' . $recaptchaResponse);
    //     $responseKeys = json_decode($response, true);

    //     if (!$responseKeys['success']) {
    //         echo json_encode(['status' => 'captcha_failed', 'message' => 'reCAPTCHA validation failed.']);
    //         return;
    //     }

    //     $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ?");
    //     $stmt->bind_param("s", $username);
    //     $stmt->execute();
    //     $qry = $stmt->get_result();

    //     if ($qry->num_rows > 0) {
    //         $res = $qry->fetch_assoc();

    //         if (password_verify($password, $res['password'])) {
    //             if ($res['status'] != 1) {
    //                 echo json_encode(['status' => 'notverified', 'message' => 'Your account is not verified.']);
    //                 return;
    //             }

    //             foreach ($res as $k => $v) {
    //                 if (!is_numeric($k) && $k != 'password') {
    //                     $this->settings->set_userdata($k, $v);
    //                 }
    //             }
    //             $this->settings->set_userdata('login_type', 1);

    //             if (password_needs_rehash($res['password'], PASSWORD_DEFAULT)) {
    //                 $newHash = password_hash($password, PASSWORD_DEFAULT);
    //                 $updateStmt = $this->conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    //                 $updateStmt->bind_param('si', $newHash, $res['id']);
    //                 $updateStmt->execute();
    //             }

    //             echo json_encode(['status' => 'success']);
    //         } else {
    //             echo json_encode(['status' => 'incorrect', 'message' => 'Invalid username or password.']);
    //         }
    //     } else {
    //         echo json_encode(['status' => 'incorrect', 'message' => 'Invalid username or password.']);
    //     }
    // }


    // public function login()
    // {
    //     extract($_POST);

    //     // Step 1: Validate reCAPTCHA
    //     $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
    //     $secretKey = '6LfFJYcqAAAAANKGBiV1AlFMLMwj2wgAGifniAKO';
    //     $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';

    //     $response = file_get_contents($verifyUrl . '?secret=' . $secretKey . '&response=' . $recaptchaResponse);
    //     $responseKeys = json_decode($response, true);

    //     if (!$responseKeys['success']) {
    //         echo json_encode(['status' => 'captcha_failed', 'message' => 'reCAPTCHA validation failed.']);
    //         return;
    //     }

    //     // Step 2: Check user credentials
    //     $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ?");
    //     $stmt->bind_param("s", $username);
    //     $stmt->execute();
    //     $qry = $stmt->get_result();

    //     if ($qry->num_rows > 0) {
    //         $res = $qry->fetch_assoc();

    //         if (password_verify($password, $res['password'])) {
    //             // Account verification check
    //             if ($res['status'] != 1) {
    //                 echo json_encode(['status' => 'notverified', 'message' => 'Your account is not verified.']);
    //                 return;
    //             }

    //             // Generate a unique verification token
    //             $token = bin2hex(random_bytes(16));
    //             $updateTokenStmt = $this->conn->prepare("UPDATE users SET reset_token_hash = ? WHERE id = ?");
    //             $updateTokenStmt->bind_param('si', $token, $res['id']);
    //             $updateTokenStmt->execute();

    //             // Send verification email
    //             $verificationLink = base_url . "admin/verify.php?token=" . urlencode($token);

    //             if ($this->sendVerificationEmail($res['username'], $res['name'], $verificationLink)) {
    //                 echo json_encode(['status' => 'verify_email_sent']);
    //             } else {
    //                 echo json_encode(['status' => 'error', 'message' => 'Unable to send verification email.']);
    //             }
    //         } else {
    //             echo json_encode(['status' => 'incorrect', 'message' => 'Invalid username or password.']);
    //         }
    //     } else {
    //         echo json_encode(['status' => 'incorrect', 'message' => 'Invalid username or password.']);
    //     }
    // }

    // private function sendVerificationEmail($email, $name, $verificationLink)
    // {
    //     $mail = new PHPMailer(true);

    //     try {
    //         // SMTP Configuration
    //         $mail->isSMTP();
    //         $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP host
    //         $mail->SMTPAuth = true;
    //         $mail->Username = 'sherwintayo08@gmail.com'; // Replace with your email
    //         $mail->Password = 'jlbm iyke zqjv zwtr'; // Replace with your email password
    //         $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    //         $mail->Port = 587;

    //         // Email Headers
    //         $mail->setFrom('no-reply@example.com', 'Admin Verification');
    //         $mail->addAddress($email, $name);

    //         // Email Content
    //         $mail->isHTML(true);
    //         $mail->Subject = 'Verify Your Login Attempt';
    //         $mail->Body = "
    //         <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9; max-width: 600px; margin: auto;'>
    //             <h2 style='color: #333;'>Hi $name,</h2>
    //             <p>A login attempt was made on your account. Please verify this attempt to secure your account.</p>
    //             <p>Click the button below to verify:</p>
    //             <a href='$verificationLink' style='display: inline-block; padding: 10px 20px; color: #fff; background-color: #007bff; text-decoration: none; border-radius: 5px;'>Verify Login Attempt</a>
    //             <p>If you did not attempt to log in, please ignore this email.</p>
    //         </div>
    //     ";

    //         $mail->send();
    //         return true;
    //     } catch (Exception $e) {
    //         error_log("Mailer Error: " . $mail->ErrorInfo);
    //         return false;
    //     }
    // }


    public function logout()
    {
        if ($this->settings->sess_des()) {
            redirect('admin/login');
        }
    }
    public function student_login()
    {
        session_start();
        extract($_POST);

        try {
            // Validate reCAPTCHA response
            $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
            $secretKey = '6LcvKpIqAAAAAERzz2_imzASHXTELXAjpOEGSoQT'; // Replace with your secret key
            $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $verifyUrl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['secret' => $secretKey, 'response' => $recaptchaResponse]));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);

            $responseKeys = json_decode($response, true);

            if (!$responseKeys['success'] || $responseKeys['score'] < 0.5) {
                return json_encode([
                    'status' => 'failed',
                    'msg' => 'reCAPTCHA validation failed. Please try again.'
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
        } catch (Exception $e) {
            error_log("Error during login: " . $e->getMessage());
            return json_encode([
                'status' => 'failed',
                'msg' => 'An unexpected error occurred. Please try again later.'
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