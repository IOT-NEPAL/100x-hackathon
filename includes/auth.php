<?php
/**
 * Authentication and session management functions for Inclusify
 */

require_once 'db.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user data
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    // Handle hardcoded admin sessions (bypass database)
    if ($_SESSION['user_id'] === 1) {
        return [
            'id' => 1,
            'name' => 'Admin User',
            'email' => 'admin@inclusify.com',
            'phone' => '+1-555-0001',
            'role' => 'admin',
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'disability_text' => null,
            'skills' => null,
            'profile_pic' => null,
            'org_name' => null,
            'contact_person' => null,
            'verification_note' => null
        ];
    } elseif ($_SESSION['user_id'] === 2) {
        return [
            'id' => 2,
            'name' => 'Super Admin',
            'email' => 'superadmin@inclusify.com',
            'phone' => '+1-555-0002',
            'role' => 'admin',
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'disability_text' => null,
            'skills' => null,
            'profile_pic' => null,
            'org_name' => null,
            'contact_person' => null,
            'verification_note' => null
        ];
    }
    
    // Regular database lookup for all other users
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

/**
 * Check if user has specific role
 */
function hasRole($role) {
    $user = getCurrentUser();
    if (!$user) return false;
    
    // Special handling for career centre
    if ($role === 'career_centre') {
        return isCareerCentre();
    }
    
    return $user['role'] === $role;
}

/**
 * Check if current user is a career centre
 */
function isCareerCentre() {
    $user = getCurrentUser();
    return $user && $user['role'] === 'career_centre';
}

/**
 * Redirect based on user role
 */
function redirectByRole() {
    if (!isLoggedIn()) {
        header("Location: /inclusify/login.php");
        exit;
    }
    
    $user = getCurrentUser();
    
    switch($user['role']) {
        case 'admin':
            header("Location: /inclusify/admin/admin-dashboard.php");
            break;
        case 'organizer':
            header("Location: /inclusify/organizer/organizer-dashboard.php");
            break;
        case 'career_centre':
            header("Location: /inclusify/career_centre/career-centre-dashboard.php");
            break;
        case 'user':
        default:
            header("Location: /inclusify/user/user-dashboard.php");
            break;
    }
    exit;
}

/**
 * Require login - redirect to login if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: /inclusify/login.php");
        exit;
    }
}

/**
 * Require specific role
 */
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header("Location: /inclusify/index.php?error=access_denied");
        exit;
    }
}

/**
 * Login user
 */
function loginUser($user_id, $remember = false) {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user_id;
    $_SESSION['login_time'] = time();
    
    if ($remember) {
        // Set remember me cookie for 30 days
        setcookie('remember_token', hash('sha256', $user_id . time()), time() + (30 * 24 * 60 * 60), '/');
    }
}

/**
 * Logout user
 */
function logoutUser() {
    session_destroy();
    setcookie('remember_token', '', time() - 3600, '/');
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Sanitize output for HTML
 */
function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Check session timeout (30 minutes)
 */
function checkSessionTimeout() {
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > 1800) {
        logoutUser();
        header("Location: /inclusify/login.php?error=session_expired");
        exit;
    }
}
?>
