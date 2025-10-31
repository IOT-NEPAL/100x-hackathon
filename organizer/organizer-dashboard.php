<?php
require_once '../includes/auth.php';
requireRole('organizer');

$user = getCurrentUser();

// Get organizer's statistics
$stats = [];

// Opportunities statistics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_opportunities,
        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_opportunities,
        SUM(views_count) as total_views
    FROM opportunities 
    WHERE organizer_id = ?
");
$stmt->execute([$user['id']]);
$opp_stats = $stmt->fetch();
$stats = array_merge($stats, $opp_stats);

// Applications statistics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_applications,
        SUM(CASE WHEN a.status = 'applied' THEN 1 ELSE 0 END) as new_applications,
        SUM(CASE WHEN a.status = 'under_review' THEN 1 ELSE 0 END) as under_review,
        SUM(CASE WHEN a.status = 'accepted' THEN 1 ELSE 0 END) as accepted,
        SUM(CASE WHEN a.status = 'rejected' THEN 1 ELSE 0 END) as rejected
    FROM applications a
    JOIN opportunities o ON a.opportunity_id = o.id
    WHERE o.organizer_id = ?
");
$stmt->execute([$user['id']]);
$app_stats = $stmt->fetch();
$stats = array_merge($stats, $app_stats);

// Recent opportunities
$stmt = $pdo->prepare("
    SELECT * FROM opportunities 
    WHERE organizer_id = ? 
    ORDER BY date_posted DESC 
    LIMIT 5
");
$stmt->execute([$user['id']]);
$recent_opportunities = $stmt->fetchAll();

// Recent applications
$stmt = $pdo->prepare("
    SELECT a.*, o.title, u.name as applicant_name, u.email as applicant_email
    FROM applications a
    JOIN opportunities o ON a.opportunity_id = o.id
    JOIN users u ON a.user_id = u.id
    WHERE o.organizer_id = ?
    ORDER BY a.applied_at DESC
    LIMIT 10
");
$stmt->execute([$user['id']]);
$recent_applications = $stmt->fetchAll();

// Opportunities by type
$stmt = $pdo->prepare("
    SELECT type, COUNT(*) as count 
    FROM opportunities 
    WHERE organizer_id = ? AND is_active = 1 
    GROUP BY type
");
$stmt->execute([$user['id']]);
$type_stats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$page_title = 'Organization Dashboard';
include '../includes/header.php';
?>

<div class="container mt-4">
    <!-- Welcome Section -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="welcome-banner">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div class="mb-3 mb-md-0">
                        <h1 class="display-6 fw-bold mb-2">
                            <?php echo escape($user['org_name'] ?? $user['name']); ?>
                        </h1>
                        <p class="lead mb-0">Organization Dashboard</p>
                    </div>
                    <div>
                        <a href="add-opportunity.php" class="btn btn-light btn-lg">
                            <i class="fas fa-plus me-2"></i>Post New Job
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-3 stagger-item">
            <div class="card stats-card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="display-number"><?php echo number_format($stats['total_opportunities']); ?></h4>
                            <p class="mb-0 fw-semibold">Total Jobs</p>
                            <small class="opacity-75"><?php echo $stats['active_opportunities']; ?> active</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-briefcase fa-3x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 stagger-item">
            <div class="card stats-card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="display-number"><?php echo number_format($stats['total_applications']); ?></h4>
                            <p class="mb-0 fw-semibold">Total Applications</p>
                            <small class="opacity-75"><?php echo $stats['new_applications']; ?> new</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-paper-plane fa-3x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 stagger-item">
            <div class="card stats-card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="display-number"><?php echo number_format($stats['total_views']); ?></h4>
                            <p class="mb-0 fw-semibold">Total Views</p>
                            <small class="opacity-75">Across all posts</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-eye fa-3x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 stagger-item">
            <div class="card stats-card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="display-number"><?php echo number_format($stats['accepted']); ?></h4>
                            <p class="mb-0 fw-semibold">Accepted</p>
                            <small class="opacity-75"><?php echo $stats['under_review']; ?> under review</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-3x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Recent Applications -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-inbox me-2"></i>Recent Applications</h5>
                    <a href="view-applications.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_applications)): ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <h6>No applications yet</h6>
                            <p>Applications will appear here when candidates apply to your jobs.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Applicant</th>
                                        <th>Job</th>
                                        <th>Status</th>
                                        <th>Applied</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_applications as $app): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo escape($app['applicant_name']); ?></strong><br>
                                                <small class="text-muted"><?php echo escape($app['applicant_email']); ?></small>
                                            </td>
                                            <td>
                                                <a href="../view-opportunity.php?id=<?php echo $app['opportunity_id']; ?>" 
                                                   class="text-decoration-none">
                                                    <?php echo escape($app['title']); ?>
                                                </a>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo getStatusColor($app['status']); ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', escape($app['status']))); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small><?php echo date('M j, Y', strtotime($app['applied_at'])); ?></small>
                                            </td>
                                            <td>
                                                <a href="view-application.php?id=<?php echo $app['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary">View</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Jobs Performance -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Job Performance</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_opportunities)): ?>
                        <div class="empty-state">
                            <i class="fas fa-chart-line fa-3x mb-3"></i>
                            <h6>No jobs posted yet</h6>
                            <p>Post your first job to see performance metrics here.</p>
                            <a href="add-opportunity.php" class="btn btn-primary btn-lg mt-3">
                                <i class="fas fa-plus me-2"></i>Post Job
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Job</th>
                                        <th>Type</th>
                                        <th>Views</th>
                                        <th>Applications</th>
                                        <th>Posted</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_opportunities as $opp): ?>
                                        <?php
                                        // Get application count for this opportunity
                                        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM applications WHERE opportunity_id = ?");
                                        $stmt->execute([$opp['id']]);
                                        $app_count = $stmt->fetch()['count'];
                                        ?>
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong><?php echo escape($opp['title']); ?></strong>
                                                    <?php if (!$opp['is_active']): ?>
                                                        <span class="badge bg-secondary ms-2">Inactive</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo getTypeColor($opp['type']); ?>">
                                                    <?php echo ucfirst(escape($opp['type'])); ?>
                                                </span>
                                            </td>
                                            <td><?php echo number_format($opp['views_count']); ?></td>
                                            <td><?php echo number_format($app_count); ?></td>
                                            <td>
                                                <small><?php echo date('M j, Y', strtotime($opp['date_posted'])); ?></small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="../view-opportunity.php?id=<?php echo $opp['id']; ?>" 
                                                       class="btn btn-outline-primary">View</a>
                                                    <a href="edit-opportunity.php?id=<?php echo $opp['id']; ?>" 
                                                       class="btn btn-outline-secondary">Edit</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="my-opportunities.php" class="btn btn-outline-primary">
                                View All Jobs
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="add-opportunity.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Post New Job
                        </a>
                        <a href="view-applications.php" class="btn btn-success">
                            <i class="fas fa-inbox me-2"></i>Review Applications
                        </a>
                        <a href="my-opportunities.php" class="btn btn-info">
                            <i class="fas fa-briefcase me-2"></i>Manage Jobs
                        </a>
                        <a href="../profile.php" class="btn btn-outline-secondary">
                            <i class="fas fa-edit me-2"></i>Edit Profile
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Organization Profile -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-building me-2"></i>Organization Profile</h5>
                </div>
                <div class="card-body">
                    <h6><?php echo escape($user['org_name'] ?? $user['name']); ?></h6>
                    
                    <?php if ($user['contact_person']): ?>
                        <p class="mb-1">
                            <i class="fas fa-user text-muted me-2"></i>
                            <?php echo escape($user['contact_person']); ?>
                        </p>
                    <?php endif; ?>
                    
                    <p class="mb-1">
                        <i class="fas fa-envelope text-muted me-2"></i>
                        <?php echo escape($user['email']); ?>
                    </p>
                    
                    <?php if ($user['phone']): ?>
                        <p class="mb-1">
                            <i class="fas fa-phone text-muted me-2"></i>
                            <?php echo escape($user['phone']); ?>
                        </p>
                    <?php endif; ?>
                    
                    <div class="mt-3">
                        <small class="text-muted">
                            Member since <?php echo date('M Y', strtotime($user['created_at'])); ?>
                        </small>
                    </div>
                    
                    <div class="d-grid mt-3">
                        <a href="../profile.php" class="btn btn-sm btn-outline-primary">
                            Update Profile
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Application Statistics -->
            <?php if ($stats['total_applications'] > 0): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Application Breakdown</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <h4 class="text-primary"><?php echo $stats['new_applications']; ?></h4>
                                <small class="text-muted">New</small>
                            </div>
                            <div class="col-6 mb-3">
                                <h4 class="text-warning"><?php echo $stats['under_review']; ?></h4>
                                <small class="text-muted">Under Review</small>
                            </div>
                            <div class="col-6">
                                <h4 class="text-success"><?php echo $stats['accepted']; ?></h4>
                                <small class="text-muted">Accepted</small>
                            </div>
                            <div class="col-6">
                                <h4 class="text-danger"><?php echo $stats['rejected']; ?></h4>
                                <small class="text-muted">Rejected</small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Opportunity Types -->
            <?php if (!empty($type_stats)): ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-tags me-2"></i>Your Jobs</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($type_stats as $type => $count): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-<?php echo getTypeColor($type); ?>">
                                    <?php echo ucfirst($type); ?>
                                </span>
                                <strong><?php echo $count; ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
function getStatusColor($status) {
    $colors = [
        'applied' => 'primary',
        'under_review' => 'warning',
        'accepted' => 'success',
        'rejected' => 'danger'
    ];
    return $colors[$status] ?? 'secondary';
}

function getTypeColor($type) {
    $colors = [
        'employment' => 'primary',
        'internship' => 'info'
    ];
    return $colors[$type] ?? 'secondary';
}

include '../includes/footer.php';
?>
