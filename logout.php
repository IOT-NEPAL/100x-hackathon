<?php
require_once 'includes/auth.php';

// Log the logout activity
if (isLoggedIn()) {
    $user = getCurrentUser();
    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, 'logout', 'User logged out', ?)");
    $stmt->execute([$user['id'], $_SERVER['REMOTE_ADDR'] ?? '']);
}

// Logout user
logoutUser();

// Redirect to login page with success message
header("Location: login.php?success=logged_out");
exit;
?>
