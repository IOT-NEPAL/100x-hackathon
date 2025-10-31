<?php
require_once '../includes/auth.php';
requireRole('user');

$user = getCurrentUser();

// Get all job offers for the student
$job_offers = [];
try {
    $offers_stmt = $pdo->prepare("
        SELECT jo.*, 
               o.title as job_title, 
               o.type, 
               o.location,
               o.description,
               u.name as organizer_name, 
               u.org_name
        FROM job_offers jo
        JOIN opportunities o ON jo.opportunity_id = o.id
        JOIN users u ON jo.organizer_id = u.id
        WHERE jo.student_id = ?
        ORDER BY jo.created_at DESC
    ");
    $offers_stmt->execute([$user['id']]);
    $job_offers = $offers_stmt->fetchAll();
    
    // Mark offers as read when viewing
    if (!empty($job_offers)) {
        $update_stmt = $pdo->prepare("
            UPDATE job_offers 
            SET read_at = NOW() 
            WHERE student_id = ? 
            AND read_at IS NULL
        ");
        $update_stmt->execute([$user['id']]);
    }
} catch (PDOException $e) {
    // Table might not exist yet
    if (strpos($e->getMessage(), "doesn't exist") === false) {
        error_log("Job offers error: " . $e->getMessage());
    }
}

// Get accepted applications (notifications)
$accepted_applications = [];
try {
    $accepted_stmt = $pdo->prepare("
        SELECT a.*, 
               o.title as job_title, 
               o.type as job_type, 
               o.location,
               u.name as organizer_name, 
               u.org_name,
               u.email as organizer_email
        FROM applications a
        JOIN opportunities o ON a.opportunity_id = o.id
        LEFT JOIN users u ON o.organizer_id = u.id
        WHERE a.user_id = ? 
        AND a.status = 'accepted'
        AND (a.reviewed_at IS NULL OR a.reviewed_at > DATE_SUB(NOW(), INTERVAL 30 DAY))
        ORDER BY a.reviewed_at DESC, a.applied_at DESC
    ");
    $accepted_stmt->execute([$user['id']]);
    $accepted_applications = $accepted_stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Accepted applications error: " . $e->getMessage());
}

// Combine all notifications
$all_notifications = [];

// Add job offers
foreach ($job_offers as $offer) {
    $all_notifications[] = [
        'type' => 'job_offer',
        'id' => $offer['id'],
        'title' => $offer['job_title'],
        'message' => $offer['message'],
        'status' => $offer['status'],
        'organizer_name' => $offer['organizer_name'],
        'org_name' => $offer['org_name'],
        'location' => $offer['location'],
        'opportunity_id' => $offer['opportunity_id'],
        'created_at' => $offer['created_at'],
        'read_at' => $offer['read_at']
    ];
}

// Add accepted applications
foreach ($accepted_applications as $app) {
    $all_notifications[] = [
        'type' => 'accepted',
        'id' => $app['id'],
        'application_id' => $app['id'],
        'title' => $app['job_title'],
        'message' => 'Your application has been accepted! We will contact you via email about the interview schedule.',
        'status' => 'accepted',
        'organizer_name' => $app['organizer_name'],
        'org_name' => $app['org_name'],
        'location' => $app['location'],
        'opportunity_id' => $app['opportunity_id'],
        'created_at' => $app['reviewed_at'] ?: $app['applied_at'],
        'read_at' => null
    ];
}

// Sort all notifications by date (newest first)
usort($all_notifications, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

// Count pending/unread notifications
$pending_count = 0;
foreach ($all_notifications as $notif) {
    if (($notif['type'] === 'job_offer' && $notif['status'] === 'pending' && !$notif['read_at']) || 
        ($notif['type'] === 'accepted' && !$notif['read_at'])) {
        $pending_count++;
    }
}

$page_title = 'All Notifications';
include '../includes/header.php';

// Handle success/error messages
$success_msg = $_GET['success'] ?? '';
$error_msg = $_GET['error'] ?? '';
?>

<div class="container mt-4">
    <?php if ($success_msg): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>Job offer <?php echo escape($success_msg); ?> successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($error_msg): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php 
            $error_messages = [
                'invalid_request' => 'Invalid request. Please try again.',
                'invalid_action' => 'Invalid action.',
                'offer_not_found' => 'Job offer not found.',
                'response_failed' => 'Failed to update job offer. Please try again.'
            ];
            echo escape($error_messages[$error_msg] ?? 'An error occurred.');
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/inclusify/">Home</a></li>
                    <li class="breadcrumb-item"><a href="user-dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">All Notifications</li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="display-5 fw-bold">
                        <i class="fas fa-bell me-3"></i>All Notifications
                        <?php if ($pending_count > 0): ?>
                            <span class="badge bg-warning text-dark ms-2"><?php echo $pending_count; ?> New</span>
                        <?php endif; ?>
                    </h1>
                    <p class="lead text-muted">View all your job offers and application updates</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header" style="background: #1a1a1a; color: white; border-bottom: 3px solid #ffff00; font-weight: 700;">
                    <h5 class="mb-0">
                        <i class="fas fa-bell me-2"></i>All Notifications
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($all_notifications)): ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <h6>No notifications yet</h6>
                            <p>When employers send you job offers or accept your applications, they'll appear here.</p>
                            <a href="../opportunities.php" class="btn btn-primary btn-lg mt-3">
                                <i class="fas fa-search me-2"></i>Browse Jobs
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($all_notifications as $notif): ?>
                                <div class="list-group-item mb-3 border rounded <?php echo ($notif['type'] === 'accepted') ? 'border-success' : ''; ?>">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center mb-2">
                                                <h5 class="mb-0 me-3">
                                                    <?php if ($notif['type'] === 'accepted'): ?>
                                                        <a href="application-accepted.php?id=<?php echo $notif['application_id']; ?>" 
                                                           class="text-decoration-none text-success">
                                                            <i class="fas fa-check-circle me-2"></i><?php echo escape($notif['title']); ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="../view-opportunity.php?id=<?php echo $notif['opportunity_id']; ?>" 
                                                           class="text-decoration-none text-dark">
                                                            <?php echo escape($notif['title']); ?>
                                                        </a>
                                                    <?php endif; ?>
                                                </h5>
                                                <?php if ($notif['type'] === 'accepted'): ?>
                                                    <span class="badge bg-success text-white">
                                                        <i class="fas fa-check me-1"></i>Accepted
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-<?php 
                                                        echo $notif['status'] === 'pending' ? 'warning' : 
                                                            ($notif['status'] === 'accepted' ? 'success' : 
                                                            ($notif['status'] === 'declined' ? 'danger' : 'secondary')); 
                                                    ?> text-white">
                                                        <?php echo ucfirst(escape($notif['status'])); ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if (($notif['type'] === 'job_offer' && $notif['status'] === 'pending' && !$notif['read_at']) || 
                                                          ($notif['type'] === 'accepted' && !$notif['read_at'])): ?>
                                                    <span class="badge bg-danger ms-2">New</span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="mb-2">
                                                <p class="mb-1">
                                                    <i class="fas fa-building text-muted me-2"></i>
                                                    <strong>From:</strong> <?php echo escape($notif['org_name'] ?? $notif['organizer_name']); ?>
                                                </p>
                                                <?php if ($notif['location']): ?>
                                                    <p class="mb-1 text-muted small">
                                                        <i class="fas fa-map-marker-alt me-2"></i><?php echo escape($notif['location']); ?>
                                                    </p>
                                                <?php endif; ?>
                                                <p class="mb-1 text-muted small">
                                                    <i class="fas fa-clock me-2"></i>
                                                    <?php echo $notif['type'] === 'accepted' ? 'Accepted' : 'Received'; ?>: 
                                                    <?php echo date('M j, Y g:i A', strtotime($notif['created_at'])); ?>
                                                </p>
                                            </div>
                                            
                                            <div class="mb-3 p-3 <?php echo $notif['type'] === 'accepted' ? 'bg-success bg-opacity-10 border border-success' : 'bg-light border rounded'; ?>">
                                                <h6 class="mb-2">
                                                    <strong>
                                                        <?php if ($notif['type'] === 'accepted'): ?>
                                                            <i class="fas fa-check-circle text-success me-2"></i>Application Status:
                                                        <?php else: ?>
                                                            Message:
                                                        <?php endif; ?>
                                                    </strong>
                                                </h6>
                                                <p class="mb-0">
                                                    <?php if ($notif['type'] === 'accepted'): ?>
                                                        After reviewing your resume and considering your skills, we have decided to proceed with your interview. 
                                                        We will contact you via email about the date and time for your interview and further details.
                                                    <?php else: ?>
                                                        <?php echo nl2br(escape($notif['message'])); ?>
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                            
                                            <div class="d-flex gap-2">
                                                <?php if ($notif['type'] === 'accepted'): ?>
                                                    <a href="application-accepted.php?id=<?php echo $notif['application_id']; ?>" 
                                                       class="btn btn-success btn-sm">
                                                        <i class="fas fa-check-circle me-1"></i>View Details
                                                    </a>
                                                    <a href="../view-opportunity.php?id=<?php echo $notif['opportunity_id']; ?>" 
                                                       class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-eye me-1"></i>View Job
                                                    </a>
                                                <?php else: ?>
                                                    <a href="../view-opportunity.php?id=<?php echo $notif['opportunity_id']; ?>" 
                                                       class="btn btn-primary btn-sm">
                                                        <i class="fas fa-eye me-1"></i>View Job Details
                                                    </a>
                                                    <?php if ($notif['status'] === 'pending'): ?>
                                                        <form action="respond-job-offer.php" method="POST" class="d-inline">
                                                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                            <input type="hidden" name="offer_id" value="<?php echo $notif['id']; ?>">
                                                            <input type="hidden" name="action" value="accept">
                                                            <button type="submit" class="btn btn-success btn-sm">
                                                                <i class="fas fa-check me-1"></i>Accept
                                                            </button>
                                                        </form>
                                                        <form action="respond-job-offer.php" method="POST" class="d-inline">
                                                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                            <input type="hidden" name="offer_id" value="<?php echo $notif['id']; ?>">
                                                            <input type="hidden" name="action" value="decline">
                                                            <button type="submit" class="btn btn-danger btn-sm">
                                                                <i class="fas fa-times me-1"></i>Decline
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

