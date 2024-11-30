<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Construct current URL
$current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

// Include database connection if required for token validation
require_once('../config.php');

// Check for verification token in the URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Validate token in the database
    $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token_hash = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $qry = $stmt->get_result();

    if ($qry->num_rows > 0) {
        $res = $qry->fetch_assoc();

        // Clear token and set session
        $clearTokenStmt = $conn->prepare("UPDATE users SET reset_token_hash = NULL WHERE id = ?");
        $clearTokenStmt->bind_param("i", $res['id']);
        $clearTokenStmt->execute();

        // Set session data
        $_SESSION['userdata'] = [
            'id' => $res['id'],
            'login_type' => $res['login_type'] ?? 1 // Defaulting login type to 1 if not set
        ];

        // Redirect to admin index
        header("Location: admin/index.php");
        exit;
    } else {
        echo "Invalid or expired token.";
        exit;
    }
}

// Check for active session
if (!isset($_SESSION['userdata'])) {
    // Redirect to login if no session and not accessing login or register
    if (!strpos($current_url, 'login.php') && !strpos($current_url, 'register.php')) {
        header("Location: admin/login.php");
        exit;
    }
} else {
    // Redirect to index if logged-in user accesses login page
    if (strpos($current_url, 'login.php')) {
        header("Location: admin/index.php");
        exit;
    }

    // Validate user role for admin pages
    $module = array('', 'admin', 'faculty', 'student');
    if ((strpos($current_url, 'index.php') || strpos($current_url, 'admin/')) && $_SESSION['userdata']['login_type'] != 1) {
        echo "<script>alert('Access Denied!');location.replace('" . base_url . $module[$_SESSION['userdata']['login_type']] . "');</script>";
        exit;
    }
}
?>