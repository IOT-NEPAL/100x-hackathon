<?php
require_once '../includes/auth.php';
requireRole('user');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: job-offers.php");
    exit;
}

$user = getCurrentUser();
$csrf_token = $_POST['csrf_token'] ?? '';
$offer_id = intval($_POST['offer_id'] ?? 0);
$action = $_POST['action'] ?? '';

// Validate CSRF token
if (!verifyCSRFToken($csrf_token)) {
    header("Location: job-offers.php?error=invalid_request");
    exit;
}

// Validate inputs
if (!$offer_id || !in_array($action, ['accept', 'decline'])) {
    header("Location: job-offers.php?error=invalid_action");
    exit;
}

// Verify the offer belongs to the current user
$stmt = $pdo->prepare("SELECT * FROM job_offers WHERE id = ? AND student_id = ?");
$stmt->execute([$offer_id, $user['id']]);
$offer = $stmt->fetch();

if (!$offer) {
    header("Location: job-offers.php?error=offer_not_found");
    exit;
}

// Update offer status
$new_status = $action === 'accept' ? 'accepted' : 'declined';
try {
    $stmt = $pdo->prepare("UPDATE job_offers SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $offer_id]);
    
    // If accepted, optionally create an application
    if ($action === 'accept') {
        // Check if application already exists
        $check_stmt = $pdo->prepare("SELECT id FROM applications WHERE user_id = ? AND opportunity_id = ?");
        $check_stmt->execute([$user['id'], $offer['opportunity_id']]);
        
        if (!$check_stmt->fetch()) {
            // Create application automatically
            $app_stmt = $pdo->prepare("
                INSERT INTO applications (user_id, opportunity_id, status, cover_letter)
                VALUES (?, ?, 'accepted', ?)
            ");
            $cover_letter = "I'm accepting the job offer sent by the employer.";
            $app_stmt->execute([$user['id'], $offer['opportunity_id'], $cover_letter]);
        }
    }
    
    // Log activity
    $log_stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, 'job_offer_responded', ?, ?)");
    $log_stmt->execute([
        $user['id'],
        "{$action}ed job offer #{$offer_id}",
        $_SERVER['REMOTE_ADDR'] ?? ''
    ]);
    
    header("Location: job-offers.php?success=" . $action . "ed");
    exit;
} catch (PDOException $e) {
    error_log("Job offer response error: " . $e->getMessage());
    header("Location: job-offers.php?error=response_failed");
    exit;
}
?>

