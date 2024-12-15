<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Debugging: Log current session data
error_log("Session Data in sess_auth: " . print_r($_SESSION, true));

// Build current URL for checking conditions
$link = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$link .= "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";

// Redirect logic for unauthenticated users
if (!isset($_SESSION['userdata']) && !strpos($link, 'login') && !strpos($link, 'register.php')) {
    redirect('admin/login');
}

// Redirect logic for already authenticated users trying to access login
if (isset($_SESSION['userdata']) && strpos($link, 'login.php')) {
    redirect('admin/index.php');
}

// Restrict access based on login type
$module = array('', 'admin', 'faculty', 'student');
if (isset($_SESSION['userdata']) && strpos($link, 'admin/') && $_SESSION['userdata']['login_type'] != 1) {
    echo "<script>alert('Access Denied!');location.replace('" . base_url . $module[$_SESSION['userdata']['login_type']] . "');</script>";
    exit;
}
?>