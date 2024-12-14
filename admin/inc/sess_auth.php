<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check HTTPS
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    $link = "https";
} else {
    $link = "http";
}
$link .= "://";
$link .= $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

// If no session and not on login/register pages, redirect to login
if (!isset($_SESSION['userdata']) && !strpos($link, 'login') && !strpos($link, 'register.php')) {
    redirect('admin/login.php');
    exit;
}

// If already logged in and on login page, redirect to appropriate dashboard
if (isset($_SESSION['userdata']) && strpos($link, 'login.php')) {
    if ($_SESSION['userdata']['login_type'] == 1) {
        redirect('admin/index.php'); // Admin dashboard
    } else {
        redirect('user/dashboard.php'); // User dashboard
    }
    exit;
}

// Restrict unauthorized access to admin pages
$module = array('', 'admin', 'faculty', 'student');
if (isset($_SESSION['userdata']) && strpos($link, 'admin/') && $_SESSION['userdata']['login_type'] != 1) {
    echo "<script>alert('Access Denied!');location.replace('" . base_url . $module[$_SESSION['userdata']['login_type']] . "');</script>";
    exit;
}
?>