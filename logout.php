<?php
require_once 'db_config.php';

// Store role before clearing session (to determine redirect)
$user_role = $_SESSION['role'] ?? null;

// Clear session variables
$_SESSION = array();

// Destroy session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Clear remember me cookie
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Destroy session
session_destroy();

// Redirect based on role - admin goes to login, others to index
if ($user_role === 'admin') {
    header("Location: login.php");
} else {
    header("Location: index.php");
}
exit();
?>

