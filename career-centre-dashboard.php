<?php
require_once '../includes/auth.php';

// Check if user is a career centre
if (!isCareerCentre()) {
    header("Location: /inclusify/index.php?error=access_denied");
    exit;
}

$user = getCurrentUser();

// Get career centre statistics
$stats = [];

// Total registered students (users)
$stmt = $pdo->prepare("SELECT COUNT(*) as total_students FROM users WHERE role = 'user' AND is_active = 1");
$stmt->execute();
$student_stats = $stmt->fetch();
$stats['total_students'] = $student_stats['total_students'];

// Total opportunities available for students
$stmt = $pdo->prepare("SELECT COUNT(*) as total_opportunities FROM opportunities WHERE is_active = 1");
$stmt->execute();
$opp_stats = $stmt->fetch();
$stats['total_opportunities'] = $opp_stats['total_opportunities'];

// Total applications (all students)
$stmt = $pdo->prepare("SELECT COUNT(*) as total_applications FROM applications");
$stmt->execute();
$app_stats = $stmt->fetch();
$stats['total_applications'] = $app_stats['total_applications'];

// Active students (students with applications)
$stmt = $pdo->prepare("SELECT COUNT(DISTINCT user_id) as active_students FROM applications");
$stmt->execute();
$active_stats = $stmt->fetch();
$stats['active_students'] = $active_stats['active_students'];

// Recent student registrations
$stmt = $pdo->prepare("
    SELECT id, name, email, created_at 
    FROM users 
    WHERE role = 'user' 
    ORDER BY created_at DESC 
    LIMIT 10
");
$stmt->execute();
$recent_students = $stmt->fetchAll();

// Recent opportunities posted
$stmt = $pdo->prepare("
    SELECT o.*, u.org_name as employer_name
    FROM opportunities o
    LEFT JOIN users u ON o.organizer_id = u.id
    WHERE o.is_active = 1
    ORDER BY o.date_posted DESC 
    LIMIT 5
");
$stmt->execute();
$recent_opportunities = $stmt->fetchAll();

// Application trends (by status)
$stmt = $pdo->prepare("
    SELECT status, COUNT(*) as count 
    FROM applications 
    GROUP BY status
");
$stmt->execute();
$status_stats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Opportunities by type
$stmt = $pdo->prepare("
    SELECT type, COUNT(*) as count 
    FROM opportunities 
    WHERE is_active = 1 
    GROUP BY type
");
$stmt->execute();
$type_stats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$page_title = 'Career Centre Dashboard';
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
                            <i class="fas fa-graduation-cap me-2"></i>
                            <?php echo escape($user['org_name'] ?? $user['name']); ?>
                        </h1>
                        <p class="lead mb-0">Career Centre Dashboard - Empowering Nepal's Future Workforce</p>
                    </div>
                    <div>
                        <a href="../opportunities.php" class="btn btn-light btn-lg">
                            <i class="fas fa-search me-2"></i>Browse Jobs
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
                            <h4 class="display-number"><?php echo number_format($stats['total_students']); ?></h4>
                            <p class="mb-0 fw-semibold">Registered Students</p>
                            <small class="opacity-75"><?php echo $stats['active_students']; ?> actively applying</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-3x opacity-75"></i>
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
                            <h4 class="display-number"><?php echo number_format($stats['total_opportunities']); ?></h4>
                            <p class="mb-0 fw-semibold">Available Jobs</p>
                            <small class="opacity-75">Jobs & internships</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-briefcase fa-3x opacity-75"></i>
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
                            <h4 class="display-number"><?php echo number_format($stats['total_applications']); ?></h4>
                            <p class="mb-0 fw-semibold">Total Applications</p>
                            <small class="opacity-75">All student applications</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-paper-plane fa-3x opacity-75"></i>
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
                            <h4 class="display-number"><?php echo number_format($stats['active_students']); ?></h4>
                            <p class="mb-0 fw-semibold">Active Students</p>
                            <small class="opacity-75">With applications</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-check fa-3x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Recent Opportunities -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-briefcase me-2"></i>Latest Opportunities</h5>
                    <a href="../opportunities.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_opportunities)): ?>
                        <div class="empty-state">
                            <i class="fas fa-briefcase fa-3x mb-3"></i>
                            <h6>No jobs available</h6>
                            <p>Jobs will appear here as employers post them.</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recent_opportunities as $opp): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">
                                                <a href="../view-opportunity.php?id=<?php echo $opp['id']; ?>" 
                                                   class="text-decoration-none">
                                                    <?php echo escape($opp['title']); ?>
                                                </a>
                                            </h6>
                                            <p class="mb-1 text-muted small">
                                                <?php echo escape($opp['employer_name'] ?? 'Employer'); ?> • 
                                                <span class="badge bg-<?php echo getTypeColor($opp['type']); ?>">
                                                    <?php echo ucfirst(escape($opp['type'])); ?>
                                                </span>
                                            </p>
                                            <small class="text-muted">
                                                Posted <?php echo date('M j, Y', strtotime($opp['date_posted'])); ?>
                                            </small>
                                        </div>
                                        <div>
                                            <span class="badge bg-info"><?php echo number_format($opp['views_count']); ?> views</span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Recent Student Registrations -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user-graduate me-2"></i>Recent Student Registrations</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_students)): ?>
                        <div class="empty-state">
                            <i class="fas fa-users fa-3x mb-3"></i>
                            <h6>No students registered yet</h6>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Student Name</th>
                                        <th>Email</th>
                                        <th>Registered</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_students as $student): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo escape($student['name']); ?></strong>
                                            </td>
                                            <td>
                                                <small><?php echo escape($student['email']); ?></small>
                                            </td>
                                            <td>
                                                <small><?php echo date('M j, Y', strtotime($student['created_at'])); ?></small>
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
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="../opportunities.php" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Browse Jobs
                        </a>
                        <a href="../students.php" class="btn btn-success">
                            <i class="fas fa-graduation-cap me-2"></i>Student Resources
                        </a>
                        <a href="../profile.php" class="btn btn-outline-secondary">
                            <i class="fas fa-edit me-2"></i>Edit Profile
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Career Centre Profile -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-building me-2"></i>Career Centre Profile</h5>
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
            
            <!-- Application Status Breakdown -->
            <?php if (!empty($status_stats)): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Application Status</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($status_stats as $status => $count): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-<?php echo getStatusColor($status); ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
                                </span>
                                <strong><?php echo number_format($count); ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Opportunity Types -->
            <?php if (!empty($type_stats)): ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-tags me-2"></i>Opportunity Types</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($type_stats as $type => $count): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-<?php echo getTypeColor($type); ?>">
                                    <?php echo ucfirst($type); ?>
                                </span>
                                <strong><?php echo number_format($count); ?></strong>
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
        'internship' => 'success'
    ];
    return $colors[$type] ?? 'secondary';
}

include '../includes/footer.php';
?>

