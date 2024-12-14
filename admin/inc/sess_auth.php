<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Debugging logs
error_log("Session userdata: " . print_r($_SESSION['userdata'], true));

// Get the current URL
$link = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$link .= "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";

// Debugging log for current URL
error_log("Current URL: " . $link);

// Redirect unauthenticated users to login page
if (!isset($_SESSION['userdata']) && !strpos($link, 'login') && !strpos($link, 'register.php')) {
    header("Location: " . base_url . "admin/login.php");
    exit;
}

// Prevent authenticated users from accessing the login page
if (isset($_SESSION['userdata']) && strpos($link, 'login.php')) {
    header("Location: " . base_url . "admin/index.php");
    exit;
}

// Restrict access to admin area based on user type
$module = array('', 'admin', 'faculty', 'student');
if (isset($_SESSION['userdata']) && strpos($link, 'admin/') && $_SESSION['userdata']['login_type'] != 1) {
    echo "<script>
            alert('Access Denied!');
            location.replace('" . base_url . $module[$_SESSION['userdata']['login_type']] . "');
          </script>";
    exit;
}
