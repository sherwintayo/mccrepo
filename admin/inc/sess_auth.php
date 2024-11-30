<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Build the current URL
$link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://";
$link .= $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

// Check if user is logged in
if (!isset($_SESSION['userdata']) || $_SESSION['userdata']['login_type'] != 1) {
    // Redirect unauthenticated users to login
    if (!strpos($link, 'login.php') && !strpos($link, 'register.php')) {
        redirect('admin/login.php');
    }
}

// Prevent logged-in users from accessing login/register pages
if (isset($_SESSION['userdata']) && strpos($link, 'login.php')) {
    redirect('admin/index.php');
}

// Restrict access based on `login_type`
$module = array('', 'admin', 'faculty', 'student');
if (isset($_SESSION['userdata']) && (strpos($link, 'index.php') || strpos($link, 'admin/')) && $_SESSION['userdata']['login_type'] != 1) {
    echo "<script>alert('Access Denied!');location.replace('" . base_url . $module[$_SESSION['userdata']['login_type']] . "');</script>";
    exit;
}
