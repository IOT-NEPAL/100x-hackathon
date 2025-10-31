<?php
require_once '../includes/auth.php';
requireRole('user');

$user = getCurrentUser();

// Get user's applications with opportunity details
$applications_stmt = $pdo->prepare("
    SELECT a.*, o.title, o.type, o.location, u.org_name, u.name as organizer_name
    FROM applications a
    JOIN opportunities o ON a.opportunity_id = o.id
    LEFT JOIN users u ON o.organizer_id = u.id
    WHERE a.user_id = ?
    ORDER BY a.applied_at DESC
    LIMIT 10
");
$applications_stmt->execute([$user['id']]);
$recent_applications = $applications_stmt->fetchAll();

// Get user's skills
$user_skills = $user['skills'] ?? '';
$skills_array = [];
if (!empty($user_skills)) {
    $skills_array = array_map('trim', explode(',', $user_skills));
    $skills_array = array_filter($skills_array);
    // Make skills case-insensitive for matching
    $skills_array = array_map('strtolower', $skills_array);
}

// Get opportunities that match user's skills
$all_matching_opportunities = [];
if (!empty($skills_array)) {
    // Build a query to find jobs where requirements match user's skills
    // We'll use a LIKE query to match skills in the requirements field
    $skills_conditions = [];
    $execute_params = [$user['id']];
    
    foreach ($skills_array as $skill) {
        $skills_conditions[] = "LOWER(o.requirements) LIKE ?";
        $execute_params[] = '%' . $skill . '%';
    }
    
    $skills_where = implode(' OR ', $skills_conditions);
    
    // First, get all matching opportunities
    $opportunities_stmt = $pdo->prepare("
        SELECT o.*, u.org_name, u.name as organizer_name
        FROM opportunities o
        LEFT JOIN users u ON o.organizer_id = u.id
        WHERE o.is_active = 1 
        AND o.id NOT IN (
            SELECT opportunity_id FROM applications WHERE user_id = ?
        )
        AND ($skills_where)
        ORDER BY o.date_posted DESC
    ");
    
    $opportunities_stmt->execute($execute_params);
    $all_matching_opportunities = $opportunities_stmt->fetchAll();
    
    // Calculate match score for each opportunity and sort by relevance
    foreach ($all_matching_opportunities as &$opp) {
        $opp['match_score'] = 0;
        $requirements = strtolower($opp['requirements'] ?? '');
        
        // Count how many skills match
        foreach ($skills_array as $skill) {
            if (strpos($requirements, $skill) !== false) {
                $opp['match_score']++;
            }
        }
    }
    unset($opp);
    
    // Sort by match score (descending) and date posted
    usort($all_matching_opportunities, function($a, $b) {
        if ($a['match_score'] == $b['match_score']) {
            return strtotime($b['date_posted']) - strtotime($a['date_posted']);
        }
        return $b['match_score'] - $a['match_score'];
    });
    
    // Take top 6
    $recommended_opportunities = array_slice($all_matching_opportunities, 0, 6);
} else {
    // If user has no skills, show recent opportunities
    $opportunities_stmt = $pdo->prepare("
        SELECT o.*, u.org_name, u.name as organizer_name
        FROM opportunities o
        LEFT JOIN users u ON o.organizer_id = u.id
        WHERE o.is_active = 1 
        AND o.id NOT IN (
            SELECT opportunity_id FROM applications WHERE user_id = ?
        )
        ORDER BY o.date_posted DESC
        LIMIT 6
    ");
    $opportunities_stmt->execute([$user['id']]);
    $recommended_opportunities = $opportunities_stmt->fetchAll();
}

// Get application statistics
$stats_stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_applications,
        SUM(CASE WHEN status = 'applied' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'under_review' THEN 1 ELSE 0 END) as under_review,
        SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
    FROM applications WHERE user_id = ?
");
$stats_stmt->execute([$user['id']]);
$application_stats = $stats_stmt->fetch();


$page_title = 'Dashboard';
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
                            Welcome back, <?php echo escape($user['name']); ?>!
                        </h1>
                        <p class="lead mb-0">Here's your job search dashboard</p>
                    </div>
                    <div>
                        <a href="../profile.php" class="btn btn-light btn-lg">
                            <i class="fas fa-user-edit me-2"></i>Edit Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Job Offers / Notifications -->
    <?php if (!empty($job_offers)): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header" style="background: #1a1a1a; color: white; border-bottom: 3px solid #ffff00; font-weight: 700;">
                        <h5 class="mb-0">
                            <i class="fas fa-bell me-2"></i>Job Offers
                            <?php if (count($job_offers) > 0): ?>
                                <span class="badge bg-warning text-dark ms-2"><?php echo count($job_offers); ?> New</span>
                            <?php endif; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($job_offers as $offer): ?>
                            <div class="alert alert-info d-flex justify-content-between align-items-start mb-3">
                                <div class="flex-grow-1">
                                    <h6 class="alert-heading mb-2">
                                        <i class="fas fa-briefcase me-2"></i>
                                        Job Offer: <?php echo escape($offer['job_title']); ?>
                                    </h6>
                                    <p class="mb-2">
                                        <strong>From:</strong> <?php echo escape($offer['org_name'] ?? $offer['organizer_name']); ?>
                                    </p>
                                    <p class="mb-2"><?php echo nl2br(escape($offer['message'])); ?></p>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        Received: <?php echo date('M j, Y g:i A', strtotime($offer['created_at'])); ?>
                                    </small>
                                    <div class="mt-3">
                                        <a href="../view-opportunity.php?id=<?php echo $offer['opportunity_id']; ?>" 
                                           class="btn btn-sm btn-primary me-2">
                                            <i class="fas fa-eye me-1"></i>View Job
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Application Statistics -->
    <div class="row g-4 mb-5">
        <div class="col-md-3 stagger-item">
            <div class="card stats-card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="display-number"><?php echo $application_stats['total_applications']; ?></h4>
                            <p class="mb-0 fw-semibold">Total Applications</p>
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
                            <h4 class="display-number"><?php echo $application_stats['pending'] + $application_stats['under_review']; ?></h4>
                            <p class="mb-0 fw-semibold">Pending Review</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-3x opacity-75"></i>
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
                            <h4 class="display-number"><?php echo $application_stats['accepted']; ?></h4>
                            <p class="mb-0 fw-semibold">Accepted</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-3x opacity-75"></i>
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
                            <h4 class="display-number">
                                <?php 
                                if (!empty($skills_array) && isset($all_matching_opportunities)) {
                                    echo count($all_matching_opportunities);
                                } else {
                                    echo count($recommended_opportunities);
                                }
                                ?>
                            </h4>
                            <p class="mb-0 fw-semibold">Skill Matches</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-star fa-3x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Recent Applications -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Applications</h5>
                    <a href="my-applications.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_applications)): ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <h6>No applications yet</h6>
                            <p>Start applying to jobs to see them here.</p>
                            <a href="../opportunities.php" class="btn btn-primary btn-lg mt-3">
                                <i class="fas fa-search me-2"></i>Browse Jobs
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Opportunity</th>
                                        <th>Organization</th>
                                        <th>Status</th>
                                        <th>Applied</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_applications as $app): ?>
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong><?php echo escape($app['title']); ?></strong><br>
                                                    <small class="text-muted">
                                                        <span class="badge bg-secondary"><?php echo ucfirst(escape($app['type'])); ?></span>
                                                        <?php if ($app['location']): ?>
                                                            <i class="fas fa-map-marker-alt ms-2 me-1"></i>
                                                            <?php echo escape($app['location']); ?>
                                                        <?php endif; ?>
                                                    </small>
                                                </div>
                                            </td>
                                            <td><?php echo escape($app['org_name'] ?? $app['organizer_name']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo getStatusColor($app['status']); ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', escape($app['status']))); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small><?php echo date('M j, Y', strtotime($app['applied_at'])); ?></small>
                                            </td>
                                            <td>
                                                <a href="../view-opportunity.php?id=<?php echo $app['opportunity_id']; ?>" 
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
        </div>
        
        <!-- Profile Summary & Quick Actions -->
        <div class="col-lg-4">
            <!-- Profile Summary -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Profile Summary</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <?php if ($user['profile_pic']): ?>
                            <img src="../uploads/profile_pics/<?php echo escape($user['profile_pic']); ?>" 
                                 alt="Profile Picture" class="rounded-circle" width="80" height="80">
                        <?php else: ?>
                            <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" 
                                 style="width: 80px; height: 80px;">
                                <i class="fas fa-user fa-2x text-white"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <h6 class="text-center"><?php echo escape($user['name']); ?></h6>
                    <p class="text-center text-muted small"><?php echo escape($user['email']); ?></p>
                    
                    <?php if ($user['skills']): ?>
                        <div class="mb-2">
                            <strong class="small">Skills:</strong>
                            <p class="small text-muted"><?php echo escape(substr($user['skills'], 0, 100)); ?>
                                <?php if (strlen($user['skills']) > 100): ?>...<?php endif; ?>
                            </p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="d-grid">
                        <a href="../profile.php" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-edit me-1"></i>Update Profile
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="../opportunities.php" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Browse Jobs
                        </a>
                        <a href="my-applications.php" class="btn btn-outline-primary">
                            <i class="fas fa-list me-2"></i>My Applications
                        </a>
                        <a href="../profile.php" class="btn btn-outline-secondary">
                            <i class="fas fa-user-edit me-2"></i>Edit Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recommended Jobs Based on Skills -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-star me-2"></i>
                        <?php if (!empty($skills_array)): ?>
                            Recommended for You (Based on Your Skills)
                        <?php else: ?>
                            Recommended for You
                        <?php endif; ?>
                    </h5>
                    <a href="../opportunities.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($recommended_opportunities)): ?>
                        <?php if (!empty($skills_array)): ?>
                            <p class="text-muted small mb-3">
                                <i class="fas fa-info-circle me-1"></i>
                                Showing jobs that match your skills: 
                                <strong><?php echo implode(', ', array_map('ucfirst', array_slice($skills_array, 0, 5))); ?>
                                <?php if (count($skills_array) > 5): ?>...<?php endif; ?></strong>
                            </p>
                        <?php endif; ?>
                        <div class="row g-3">
                            <?php foreach ($recommended_opportunities as $opp): ?>
                                <div class="col-md-6">
                                    <div class="card h-100 border">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <span class="badge bg-<?php echo getTypeColor($opp['type']); ?> text-white">
                                                    <?php echo ucfirst(escape($opp['type'])); ?>
                                                </span>
                                                <?php if (!empty($skills_array) && isset($opp['match_score'])): ?>
                                                    <small class="text-muted">
                                                        <i class="fas fa-check-circle text-success me-1"></i>
                                                        <?php echo $opp['match_score']; ?> skill<?php echo $opp['match_score'] > 1 ? 's' : ''; ?> match
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                            <h6 class="card-title"><?php echo escape($opp['title']); ?></h6>
                                            <p class="text-muted small mb-2">
                                                <?php echo escape($opp['org_name'] ?? $opp['organizer_name']); ?>
                                            </p>
                                            <p class="card-text small">
                                                <?php echo escape(substr($opp['description'], 0, 100)); ?>...
                                            </p>
                                            <div class="d-flex gap-2 mt-auto">
                                                <a href="../view-opportunity.php?id=<?php echo $opp['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary">View</a>
                                                <a href="../apply.php?id=<?php echo $opp['id']; ?>" 
                                                   class="btn btn-sm btn-primary">Apply</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-search fa-3x mb-3"></i>
                            <h6>No matching jobs found</h6>
                            <?php if (!empty($skills_array)): ?>
                                <p>No jobs currently match your skills. Try browsing all available jobs or add more skills to your profile.</p>
                            <?php else: ?>
                                <p>No jobs available right now. Check back later!</p>
                            <?php endif; ?>
                            <a href="../opportunities.php" class="btn btn-primary btn-lg mt-3">
                                <i class="fas fa-search me-2"></i>Browse All Jobs
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
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
