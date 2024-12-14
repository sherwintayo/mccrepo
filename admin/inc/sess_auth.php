<?php

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Determine the current URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
$currentURL = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

// Redirect unauthorized users to the login page
if (!isset($_SESSION['userdata']) && !strpos($currentURL, 'login') && !strpos($currentURL, 'register.php')) {
    header("Location: " . base_url . "admin/login.php");
    exit;
}

// Prevent logged-in users from accessing the login page
if (isset($_SESSION['userdata']) && strpos($currentURL, 'login.php')) {
    header("Location: " . base_url . "admin/index.php");
    exit;
}

// Module access control based on login type
$modules = ['', 'admin', 'faculty', 'student'];
if (isset($_SESSION['userdata']) && strpos($currentURL, 'admin/') && $_SESSION['userdata']['login_type'] != 1) {
    echo "<script>
            alert('Access Denied!');
            location.replace('" . base_url . $modules[$_SESSION['userdata']['login_type']] . "');
          </script>";
    exit;
}
