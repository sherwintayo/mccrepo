<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Construct the current URL
$current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

// Check if user session exists
if (!isset($_SESSION['userdata'])) {
    // Redirect to login page if session not found and not on login or register pages
    if (!strpos($current_url, 'login.php') && !strpos($current_url, 'register.php')) {
        header("Location: admin/login.php");
        exit;
    }
} else {
    // Redirect logged-in users accessing login page to admin index
    if (strpos($current_url, 'login.php')) {
        header("Location: admin/index.php");
        exit;
    }

    // Check user permissions for admin area
    $module = array('', 'admin', 'faculty', 'student');
    if ((strpos($current_url, 'index.php') || strpos($current_url, 'admin/')) && $_SESSION['userdata']['login_type'] != 1) {
        echo "<script>alert('Access Denied!');location.replace('" . base_url . $module[$_SESSION['userdata']['login_type']] . "');</script>";
        exit;
    }
}
?>