<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once('../config.php');

if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
    $link = "https";
else
    $link = "http";
$link .= "://";
$link .= $_SERVER['HTTP_HOST'];
$link .= $_SERVER['REQUEST_URI'];

// Redirect if user is not logged in
if (!isset($_SESSION['userdata']) && !strpos($link, 'login') && !strpos($link, 'register.php')) {
    redirect('admin/login');
}

// Validate session token
if (isset($_SESSION['userdata']['session_token'])) {
    $sessionToken = $_SESSION['userdata']['session_token'];
    $stmt = $conn->prepare("SELECT * FROM sessions WHERE session_token = ? AND is_valid = 1");
    $stmt->bind_param("s", $sessionToken);
    $stmt->execute();
    $session = $stmt->get_result()->fetch_assoc();

    // Invalidate session if token is invalid
    if (!$session) {
        session_destroy();
        redirect('admin/login');
    }
} else {
    redirect('admin/login');
}

// Redirect logged-in users away from the login page
if (isset($_SESSION['userdata']) && strpos($link, 'login.php')) {
    redirect('admin/index.php');
}

// Restrict access based on user type
$module = array('', 'admin', 'faculty', 'student');
if (isset($_SESSION['userdata']) && (strpos($link, 'index.php') || strpos($link, 'admin/')) && $_SESSION['userdata']['login_type'] != 1) {
    echo "<script>alert('Access Denied!');location.replace('" . base_url . $module[$_SESSION['userdata']['login_type']] . "');</script>";
    exit;
}
?>