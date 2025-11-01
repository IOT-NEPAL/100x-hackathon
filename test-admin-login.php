<?php
// Test admin login redirect
session_start();

$_SESSION['user_id'] = 0;
$_SESSION['user_name'] = 'Admin';
$_SESSION['user_email'] = 'admin@gmail.com';
$_SESSION['role'] = 'admin';
$_SESSION['org_name'] = null;

echo "Session set!<br>";
echo "Role: " . $_SESSION['role'] . "<br>";
echo "User ID: " . $_SESSION['user_id'] . "<br>";
echo "Email: " . $_SESSION['user_email'] . "<br>";
echo "<br><a href='admin/admin-dashboard.php'>Go to Admin Dashboard</a>";
?>

