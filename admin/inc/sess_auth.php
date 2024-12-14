<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get the current URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
$currentURL = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

// Redirect unauthorized users
if (!isset($_SESSION['userdata'])) {
    if (!strpos($currentURL, 'login') && !strpos($currentURL, 'register.php')) {
        header("Location: " . base_url . "admin/login.php");
        exit;
    }
}

// Prevent logged-in users from accessing login/register pages
if (isset($_SESSION['userdata']) && strpos($currentURL, 'login.php')) {
    header("Location: " . base_url . "admin/index.php");
    exit;
}

// Restrict access to specific modules
$modules = ['', 'admin', 'faculty', 'student'];
if (isset($_SESSION['userdata']) && strpos($currentURL, 'admin/') && $_SESSION['userdata']['login_type'] != 1) {
    echo "<script>
        alert('Access Denied!');
        location.replace('" . base_url . $modules[$_SESSION['userdata']['login_type']] . "');
    </script>";
    exit;
}
?>