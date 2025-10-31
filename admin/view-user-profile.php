<?php
require_once '../includes/auth.php';
requireRole('admin');

$admin_user = getCurrentUser();
$user_id = intval($_GET['id'] ?? 0);

if (!$user_id) {
    header("Location: manage-users.php");
    exit;
}

// Get user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: manage-users.php?error=user_not_found");
    exit;
}

// Get user statistics
$stats = [];

try {
    if ($user['role'] === 'user') {
        // Get application statistics
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_applications,
                SUM(CASE WHEN status = 'applied' THEN 1 ELSE 0 END) as pending_applications,
                SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted_applications,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_applications
            FROM applications 
            WHERE user_id = ?
        ");
        $stmt->execute([$user_id]);
        $stats = $stmt->fetch();
        
        // Get recent applications
        $stmt = $pdo->prepare("
            SELECT a.*, o.title as opportunity_title, o.type, org.name as organizer_name, org.org_name
            FROM applications a
            JOIN opportunities o ON a.opportunity_id = o.id
            LEFT JOIN users org ON o.organizer_id = org.id
            WHERE a.user_id = ?
            ORDER BY a.applied_at DESC
            LIMIT 5
        ");
        $stmt->execute([$user_id]);
        $recent_applications = $stmt->fetchAll();
        
    } elseif ($user['role'] === 'organizer') {
        // Get opportunity statistics
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_opportunities,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_opportunities,
                SUM(views_count) as total_views
            FROM opportunities 
            WHERE organizer_id = ?
        ");
        $stmt->execute([$user_id]);
        $stats = $stmt->fetch();
        
        // Get application statistics for this organizer's opportunities
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total_applications_received
            FROM applications a
            JOIN opportunities o ON a.opportunity_id = o.id
            WHERE o.organizer_id = ?
        ");
        $stmt->execute([$user_id]);
        $app_stats = $stmt->fetch();
        $stats = array_merge($stats, $app_stats);
        
        // Get recent opportunities
        $stmt = $pdo->prepare("
            SELECT * FROM opportunities 
            WHERE organizer_id = ? 
            ORDER BY date_posted DESC 
            LIMIT 5
        ");
        $stmt->execute([$user_id]);
        $recent_opportunities = $stmt->fetchAll();
    }
    
    // Get activity logs
    $stmt = $pdo->prepare("
        SELECT * FROM activity_logs 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    $stmt->execute([$user_id]);
    $activity_logs = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $stats = [];
    $recent_applications = [];
    $recent_opportunities = [];
    $activity_logs = [];
}

$page_title = 'User Profile - ' . $user['name'];
include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="admin-dashboard.php">Admin Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="manage-users.php">Manage Users</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo escape($user['name']); ?></li>
                </ol>
            </nav>
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-user me-2"></i>User Profile</h2>
                <a href="manage-users.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Manage Users
                </a>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-4">
            <!-- User Information -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-id-card me-2"></i>User Information</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <?php if ($user['profile_pic']): ?>
                            <img src="../<?php echo escape($user['profile_pic']); ?>" 
                                 alt="Profile Picture" class="rounded-circle" width="120" height="120">
                        <?php else: ?>
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center mx-auto" 
                                 style="width: 120px; height: 120px;">
                                <i class="fas fa-user fa-3x text-white"></i>
                            </div>
                        <?php endif; ?>
                        <h4 class="mt-3"><?php echo escape($user['name']); ?></h4>
                        <span class="badge bg-<?php 
                            echo match($user['role']) {
                                'admin' => 'danger',
                                'organizer' => 'info',
                                'user' => 'success',
                                default => 'secondary'
                            };
                        ?> fs-6">
                            <?php echo ucfirst($user['role']); ?>
                        </span>
                        <span class="badge bg-<?php echo $user['is_active'] ? 'success' : 'secondary'; ?> fs-6 ms-2">
                            <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </div>
                    
                    <div class="mb-3">
                        <strong><i class="fas fa-envelope me-2"></i>Email:</strong><br>
                        <a href="mailto:<?php echo escape($user['email']); ?>"><?php echo escape($user['email']); ?></a>
                    </div>
                    
                    <?php if ($user['phone']): ?>
                        <div class="mb-3">
                            <strong><i class="fas fa-phone me-2"></i>Phone:</strong><br>
                            <a href="tel:<?php echo escape($user['phone']); ?>"><?php echo escape($user['phone']); ?></a>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <strong><i class="fas fa-calendar me-2"></i>Member Since:</strong><br>
                        <?php echo date('F j, Y', strtotime($user['created_at'])); ?>
                    </div>
                    
                    <?php if ($user['updated_at'] && $user['updated_at'] != $user['created_at']): ?>
                        <div class="mb-3">
                            <strong><i class="fas fa-clock me-2"></i>Last Updated:</strong><br>
                            <?php echo date('F j, Y g:i A', strtotime($user['updated_at'])); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($user['role'] === 'organizer'): ?>
                        <hr>
                        <h6><i class="fas fa-building me-2"></i>Organization Details</h6>
                        
                        <?php if ($user['org_name']): ?>
                            <div class="mb-3">
                                <strong>Organization:</strong><br>
                                <?php echo escape($user['org_name']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($user['contact_person']): ?>
                            <div class="mb-3">
                                <strong>Contact Person:</strong><br>
                                <?php echo escape($user['contact_person']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($user['verification_note']): ?>
                            <div class="mb-3">
                                <strong>Verification Note:</strong><br>
                                <small class="text-muted"><?php echo nl2br(escape($user['verification_note'])); ?></small>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php if ($user['role'] === 'user'): ?>
                        <hr>
                        <h6><i class="fas fa-universal-access me-2"></i>Accessibility Information</h6>
                        
                        <?php if ($user['disability_text']): ?>
                            <div class="mb-3">
                                <strong>Accessibility Needs:</strong><br>
                                <small><?php echo nl2br(escape($user['disability_text'])); ?></small>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($user['skills']): ?>
                            <div class="mb-3">
                                <strong>Skills:</strong><br>
                                <?php 
                                $skills = explode(',', $user['skills']);
                                foreach ($skills as $skill): 
                                ?>
                                    <span class="badge bg-light text-dark me-1 mb-1"><?php echo escape(trim($skill)); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <!-- Statistics -->
            <?php if (!empty($stats)): ?>
                <div class="row mb-4">
                    <?php if ($user['role'] === 'user'): ?>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h4 class="text-primary"><?php echo $stats['total_applications'] ?? 0; ?></h4>
                                    <p class="card-text">Total Applications</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h4 class="text-warning"><?php echo $stats['pending_applications'] ?? 0; ?></h4>
                                    <p class="card-text">Pending</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h4 class="text-success"><?php echo $stats['accepted_applications'] ?? 0; ?></h4>
                                    <p class="card-text">Accepted</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h4 class="text-danger"><?php echo $stats['rejected_applications'] ?? 0; ?></h4>
                                    <p class="card-text">Rejected</p>
                                </div>
                            </div>
                        </div>
                    <?php elseif ($user['role'] === 'organizer'): ?>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h4 class="text-primary"><?php echo $stats['total_opportunities'] ?? 0; ?></h4>
                                    <p class="card-text">Total Opportunities</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h4 class="text-success"><?php echo $stats['active_opportunities'] ?? 0; ?></h4>
                                    <p class="card-text">Active</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h4 class="text-info"><?php echo $stats['total_views'] ?? 0; ?></h4>
                                    <p class="card-text">Total Views</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h4 class="text-warning"><?php echo $stats['total_applications_received'] ?? 0; ?></h4>
                                    <p class="card-text">Applications Received</p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <!-- Recent Activity -->
            <?php if ($user['role'] === 'user' && !empty($recent_applications)): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-paper-plane me-2"></i>Recent Applications</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($recent_applications as $application): ?>
                            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                <div>
                                    <strong><?php echo escape($application['opportunity_title']); ?></strong>
                                    <br><small class="text-muted">
                                        To: <?php echo escape($application['organizer_name']); ?>
                                        <?php if ($application['org_name']): ?>
                                            (<?php echo escape($application['org_name']); ?>)
                                        <?php endif; ?>
                                    </small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-<?php 
                                        echo match($application['status']) {
                                            'applied' => 'warning',
                                            'under_review' => 'info',
                                            'accepted' => 'success',
                                            'rejected' => 'danger',
                                            default => 'secondary'
                                        };
                                    ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $application['status'])); ?>
                                    </span>
                                    <br><small class="text-muted"><?php echo date('M j, Y', strtotime($application['applied_at'])); ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="mt-3 text-center">
                            <a href="../admin/view-applications.php?applicant=<?php echo $user_id; ?>" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-eye me-2"></i>View All Applications
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($user['role'] === 'organizer' && !empty($recent_opportunities)): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-briefcase me-2"></i>Recent Opportunities</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($recent_opportunities as $opportunity): ?>
                            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                <div>
                                    <strong><?php echo escape($opportunity['title']); ?></strong>
                                    <br><small class="text-muted">
                                        <?php echo ucfirst($opportunity['type']); ?> • <?php echo escape($opportunity['location']); ?>
                                    </small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-<?php echo $opportunity['is_active'] ? 'success' : 'secondary'; ?>">
                                        <?php echo $opportunity['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                    <br><small class="text-muted"><?php echo date('M j, Y', strtotime($opportunity['date_posted'])); ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="mt-3 text-center">
                            <a href="../admin/manage-opportunities.php?organizer=<?php echo $user_id; ?>" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-eye me-2"></i>View All Opportunities
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Activity Logs -->
            <?php if (!empty($activity_logs)): ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Activity</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($activity_logs as $log): ?>
                            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                <div>
                                    <strong><?php echo escape($log['action']); ?></strong>
                                    <?php if ($log['description']): ?>
                                        <br><small class="text-muted"><?php echo escape($log['description']); ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted"><?php echo date('M j, Y g:i A', strtotime($log['created_at'])); ?></small>
                                    <?php if ($log['ip_address']): ?>
                                        <br><small class="text-muted">IP: <?php echo escape($log['ip_address']); ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
