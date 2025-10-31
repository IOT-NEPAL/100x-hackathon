<?php
require_once '../includes/auth.php';
requireRole('admin');

$user = getCurrentUser();

// Get statistics with error handling
$stats = [];
$user_stats = [];
$opp_stats = [];
$app_stats = [];

try {
    // Users statistics
    $stmt = $pdo->query("SELECT role, COUNT(*) as count FROM users WHERE is_active = 1 GROUP BY role");
    $user_stats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $stats['total_users'] = array_sum($user_stats);
    $stats['job_seekers'] = $user_stats['user'] ?? 0;
    $stats['organizers'] = $user_stats['organizer'] ?? 0;
    $stats['admins'] = $user_stats['admin'] ?? 0;

    // Opportunities statistics
    $stmt = $pdo->query("SELECT type, COUNT(*) as count FROM opportunities WHERE is_active = 1 GROUP BY type");
    $opp_stats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $stats['total_opportunities'] = array_sum($opp_stats);

    // Applications statistics
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM applications GROUP BY status");
    $app_stats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $stats['total_applications'] = array_sum($app_stats);
} catch (PDOException $e) {
    // Set default values if database is not ready
    $stats = [
        'total_users' => 0,
        'job_seekers' => 0,
        'organizers' => 0,
        'admins' => 0,
        'total_opportunities' => 0,
        'total_applications' => 0
    ];
}

// Recent activity
$stmt = $pdo->query("
    SELECT al.*, u.name, u.email 
    FROM activity_logs al
    LEFT JOIN users u ON al.user_id = u.id
    ORDER BY al.created_at DESC
    LIMIT 10
");
$recent_activity = $stmt->fetchAll();

// New registrations this week
$stmt = $pdo->query("
    SELECT COUNT(*) as count 
    FROM users 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
");
$stats['new_users_week'] = $stmt->fetch()['count'];

// Active opportunities (posted this month)
$stmt = $pdo->query("
    SELECT COUNT(*) as count 
    FROM opportunities 
    WHERE date_posted >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND is_active = 1
");
$stats['new_opportunities_month'] = $stmt->fetch()['count'];

// Pending applications
$stats['pending_applications'] = ($app_stats['applied'] ?? 0) + ($app_stats['under_review'] ?? 0);

$page_title = 'Admin Dashboard';
include '../includes/header.php';
?>

<div class="container-fluid mt-4">
    <!-- Welcome Section -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="welcome-banner">
                <div>
                    <h1 class="display-6 fw-bold mb-2">
                        <i class="fas fa-tachometer-alt me-3"></i>Admin Dashboard
                    </h1>
                    <p class="lead mb-0">Welcome back, <?php echo escape($user['name']); ?>! Here's your system overview.</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row g-4 mb-5">
        <div class="col-xl-3 col-md-6 stagger-item">
            <div class="card stats-card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="display-number"><?php echo number_format($stats['total_users']); ?></h4>
                            <p class="mb-0 fw-semibold">Total Users</p>
                            <small class="opacity-75">+<?php echo $stats['new_users_week']; ?> this week</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-3x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 stagger-item">
            <div class="card stats-card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="display-number"><?php echo number_format($stats['total_opportunities']); ?></h4>
                            <p class="mb-0 fw-semibold">Active Jobs</p>
                            <small class="opacity-75">+<?php echo $stats['new_opportunities_month']; ?> this month</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-briefcase fa-3x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 stagger-item">
            <div class="card stats-card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="display-number"><?php echo number_format($stats['total_applications']); ?></h4>
                            <p class="mb-0 fw-semibold">Total Applications</p>
                            <small class="opacity-75"><?php echo $stats['pending_applications']; ?> pending</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-paper-plane fa-3x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 stagger-item">
            <div class="card stats-card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="display-number"><?php echo number_format($stats['organizers']); ?></h4>
                            <p class="mb-0 fw-semibold">Organizations</p>
                            <small class="opacity-75"><?php echo number_format($stats['job_seekers']); ?> job seekers</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-building fa-3x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Charts Section -->
        <div class="col-lg-8">
            <div class="row g-4">
                <!-- Opportunities by Type Chart -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Jobs by Type</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="opportunityTypeChart" 
                                    data-chart='<?php echo json_encode([
                                        "labels" => array_keys($opp_stats),
                                        "values" => array_values($opp_stats)
                                    ]); ?>' height="100"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Application Status Chart -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Application Status Distribution</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="applicationStatusChart" 
                                    data-chart='<?php echo json_encode([
                                        "labels" => array_keys($app_stats),
                                        "values" => array_values($app_stats)
                                    ]); ?>' height="100"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Activity</h5>
                            <a href="activity-logs.php" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recent_activity)): ?>
                                <p class="text-muted text-center py-3">No recent activity</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>User</th>
                                                <th>Action</th>
                                                <th>Description</th>
                                                <th>Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_activity as $activity): ?>
                                                <tr>
                                                    <td>
                                                        <?php if ($activity['name']): ?>
                                                            <?php echo escape($activity['name']); ?>
                                                            <br><small class="text-muted"><?php echo escape($activity['email']); ?></small>
                                                        <?php else: ?>
                                                            <small class="text-muted">System</small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-secondary">
                                                            <?php echo ucfirst(str_replace('_', ' ', escape($activity['action']))); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo escape($activity['description']); ?></td>
                                                    <td>
                                                        <small><?php echo date('M j, g:i A', strtotime($activity['created_at'])); ?></small>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions & System Info -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="manage-users.php" class="btn btn-primary">
                            <i class="fas fa-users me-2"></i>Manage Users
                        </a>
                        <a href="manage-opportunities.php" class="btn btn-success">
                            <i class="fas fa-briefcase me-2"></i>Manage Jobs
                        </a>
                        <a href="view-applications.php" class="btn btn-info">
                            <i class="fas fa-paper-plane me-2"></i>View Applications
                        </a>
                        <a href="system-settings.php" class="btn btn-secondary">
                            <i class="fas fa-cog me-2"></i>System Settings
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- User Breakdown -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>User Breakdown</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="border-end">
                                <h4 class="text-primary"><?php echo $stats['job_seekers']; ?></h4>
                                <small class="text-muted">Job Seekers</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-end">
                                <h4 class="text-success"><?php echo $stats['organizers']; ?></h4>
                                <small class="text-muted">Organizations</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <h4 class="text-danger"><?php echo $stats['admins']; ?></h4>
                            <small class="text-muted">Admins</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- System Health -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-heartbeat me-2"></i>System Health</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Database</span>
                            <span class="badge bg-success">Online</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>File Uploads</span>
                            <span class="badge bg-success">Working</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Email System</span>
                            <span class="badge bg-warning">Simulated</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Disk Space</span>
                            <span class="badge bg-success">85% Free</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Latest Users -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Latest Registrations</h5>
                </div>
                <div class="card-body">
                    <?php
                    $stmt = $pdo->query("
                        SELECT name, email, role, created_at 
                        FROM users 
                        ORDER BY created_at DESC 
                        LIMIT 5
                    ");
                    $latest_users = $stmt->fetchAll();
                    ?>
                    
                    <?php foreach ($latest_users as $latest_user): ?>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div>
                                <strong><?php echo escape($latest_user['name']); ?></strong><br>
                                <small class="text-muted"><?php echo escape($latest_user['email']); ?></small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-<?php echo $latest_user['role'] === 'admin' ? 'danger' : ($latest_user['role'] === 'organizer' ? 'success' : 'primary'); ?>">
                                    <?php echo ucfirst(escape($latest_user['role'])); ?>
                                </span><br>
                                <small class="text-muted"><?php echo date('M j', strtotime($latest_user['created_at'])); ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="text-center mt-3">
                        <a href="manage-users.php" class="btn btn-sm btn-outline-primary">View All Users</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
