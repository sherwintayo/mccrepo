<!-- <?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Construct current URL
$current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

// Check for user session
if (!isset($_SESSION['userdata'])) {
    // Redirect to login if session is missing and not on login/register pages
    if (!strpos($current_url, 'login.php') && !strpos($current_url, 'register.php')) {
        header("Location: admin/login.php");
        exit;
    }
} else {
    // Redirect to admin/index.php if accessing login while logged in
    if (strpos($current_url, 'login.php')) {
        header("Location: admin/index.php");
        exit;
    }

    // Validate access permissions for admin pages
    $module = array('', 'admin', 'faculty', 'student');
    if ((strpos($current_url, 'index.php') || strpos($current_url, 'admin/')) && $_SESSION['userdata']['login_type'] != 1) {
        echo "<script>alert('Access Denied!');location.replace('" . base_url . $module[$_SESSION['userdata']['login_type']] . "');</script>";
        exit;
    }
}
?> -->

<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Construct current URL
$current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

// Check for user session
if (!isset($_SESSION['userdata'])) {
    // Redirect to login if session is missing and not on login/register pages
    if (!strpos($current_url, 'login.php') && !strpos($current_url, 'register.php')) {
        header("Location: admin/login.php");
        exit;
    }
} else {
    // Redirect to admin index if accessing login while logged in
    if (strpos($current_url, 'login.php')) {
        header("Location: admin/index.php");
        exit;
    }

    // Validate access permissions for admin pages
    $module = array('', 'admin', 'faculty', 'student');
    if ((strpos($current_url, 'index.php') || strpos($current_url, 'admin/')) && $_SESSION['userdata']['login_type'] != 1) {
        echo "<script>alert('Access Denied!');location.replace('" . base_url . $module[$_SESSION['userdata']['login_type']] . "');</script>";
        exit;
    }
}
