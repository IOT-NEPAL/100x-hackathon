<?php
require_once __DIR__ . '/auth.php';
$current_user = getCurrentUser();

// Get job offers count for students
$job_offers_count = 0;
if (isLoggedIn() && hasRole('user')) {
    try {
        $count_stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM job_offers 
            WHERE student_id = ? 
            AND status = 'pending'
        ");
        $count_stmt->execute([$current_user['id']]);
        $result = $count_stmt->fetch();
        $job_offers_count = $result['count'] ?? 0;
    } catch (PDOException $e) {
        // Table might not exist yet, ignore error
        $job_offers_count = 0;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? escape($page_title) . ' - ' : ''; ?>Awasar Nepal</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Main Styles -->
    <link href="/inclusify/styles.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="/inclusify/css/custom.css" rel="stylesheet">
    
    <!-- Dashboard CSS -->
    <link href="/inclusify/css/dashboard.css" rel="stylesheet">
    
    <!-- Chart.js for analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Accessibility Controls (moved to user dropdown) -->

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center" href="<?php 
                if (hasRole('user')) {
                    echo '/inclusify/index.php';
                } elseif (hasRole('organizer')) {
                    echo '/inclusify/organizer/organizer-dashboard.php';
                } else {
                    echo '/inclusify/';
                }
            ?>">
                <?php if (hasRole('user') || hasRole('organizer')): ?>
                    <img src="/inclusify/logo.png" alt="Awasar Nepal Logo" height="32">
                <?php else: ?>
                    <img src="/inclusify/images/logo.png" alt="Awasar Nepal Logo" height="32" class="me-2">
                    Awasar Nepal
                <?php endif; ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if (!hasRole('user') && !hasRole('organizer')): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/inclusify/">Home</a>
                        </li>
                    <?php endif; ?>
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/inclusify/opportunities.php">Jobs</a>
                        </li>
                        <?php if (hasRole('user')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/inclusify/user/user-dashboard.php">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/inclusify/user/my-skills.php">My Skills</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/inclusify/user/career-guidance-ai.php">
                                    <i class="fas fa-robot me-1"></i>Career Guidance AI
                                </a>
                            </li>
                        <?php elseif (hasRole('organizer')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/inclusify/organizer/organizer-dashboard.php">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/inclusify/organizer/add-opportunity.php">
                                    <i class="fas fa-plus me-1"></i>Post New Job
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/inclusify/organizer/view-applications.php">
                                    <i class="fas fa-clipboard-list me-1"></i>Review Applications
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/inclusify/organizer/my-opportunities.php">
                                    <i class="fas fa-briefcase me-1"></i>Manage Jobs
                                </a>
                            </li>
                        <?php elseif (hasRole('admin')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/inclusify/admin/admin-dashboard.php">Admin</a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <!-- Notifications -->
                        <li class="nav-item dropdown">
                            <a class="nav-link position-relative" href="#" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                                <i class="fas fa-bell"></i>
                                <?php 
                                // Get notification count (job offers + accepted applications for students, new applications for organizers)
                                $notification_count = 0;
                                $accepted_applications_count = 0;
                                if (hasRole('user')) {
                                    $notification_count = $job_offers_count; // Already fetched above - job offers
                                    
                                    // Also count accepted applications
                                    try {
                                        $accepted_stmt = $pdo->prepare("
                                            SELECT COUNT(*) as count 
                                            FROM applications a
                                            WHERE a.user_id = ? 
                                            AND a.status = 'accepted'
                                            AND (a.reviewed_at IS NULL OR a.reviewed_at > DATE_SUB(NOW(), INTERVAL 7 DAY))
                                        ");
                                        $accepted_stmt->execute([$current_user['id']]);
                                        $accepted_result = $accepted_stmt->fetch();
                                        $accepted_applications_count = $accepted_result['count'] ?? 0;
                                        $notification_count += $accepted_applications_count;
                                    } catch (PDOException $e) {
                                        // Ignore errors
                                    }
                                } elseif (hasRole('organizer')) {
                                    try {
                                        $notif_stmt = $pdo->prepare("
                                            SELECT COUNT(*) as count 
                                            FROM applications a
                                            JOIN opportunities o ON a.opportunity_id = o.id
                                            WHERE o.organizer_id = ? 
                                            AND a.status = 'applied'
                                            AND a.reviewed_at IS NULL
                                        ");
                                        $notif_stmt->execute([$current_user['id']]);
                                        $notif_result = $notif_stmt->fetch();
                                        $notification_count = $notif_result['count'] ?? 0;
                                    } catch (PDOException $e) {
                                        $notification_count = 0;
                                    }
                                }
                                ?>
                                <?php if ($notification_count > 0): ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem; min-width: 1rem; height: 1rem; display: flex; align-items: center; justify-content: center;">
                                        <?php echo $notification_count > 9 ? '9+' : $notification_count; ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" style="min-width: 300px; max-width: 400px;">
                                <li><h6 class="dropdown-header">Notifications</h6></li>
                                <li><hr class="dropdown-divider"></li>
                                <?php if (hasRole('user') && ($job_offers_count > 0 || $accepted_applications_count > 0)): ?>
                                    <?php
                                    // Get recent job offers
                                    $has_notifications = false;
                                    if ($job_offers_count > 0) {
                                        try {
                                            $recent_offers_stmt = $pdo->prepare("
                                                SELECT jo.*, o.title as job_title, u.name as organizer_name, 'job_offer' as type
                                                FROM job_offers jo
                                                JOIN opportunities o ON jo.opportunity_id = o.id
                                                JOIN users u ON jo.organizer_id = u.id
                                                WHERE jo.student_id = ? 
                                                AND jo.status = 'pending'
                                                ORDER BY jo.created_at DESC
                                                LIMIT 5
                                            ");
                                            $recent_offers_stmt->execute([$current_user['id']]);
                                            $recent_offers = $recent_offers_stmt->fetchAll();
                                            foreach ($recent_offers as $offer):
                                                $has_notifications = true;
                                    ?>
                                                <li>
                                                    <a class="dropdown-item" href="/inclusify/user/job-offers.php">
                                                        <div class="d-flex w-100 justify-content-between">
                                                            <div>
                                                                <strong class="text-primary">New Job Offer</strong>
                                                                <p class="mb-0 small text-muted"><?php echo escape($offer['job_title']); ?></p>
                                                                <small class="text-muted"><?php echo escape($offer['organizer_name']); ?></small>
                                                            </div>
                                                            <small class="text-muted"><?php echo date('M j', strtotime($offer['created_at'])); ?></small>
                                                        </div>
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                    <?php 
                                            endforeach;
                                        } catch (PDOException $e) {
                                            // Ignore errors
                                        }
                                    }
                                    
                                    // Get recent accepted applications
                                    if ($accepted_applications_count > 0) {
                                        try {
                                            $accepted_apps_stmt = $pdo->prepare("
                                                SELECT a.*, o.title as job_title, o.type as job_type, u.org_name, u.name as organizer_name
                                                FROM applications a
                                                JOIN opportunities o ON a.opportunity_id = o.id
                                                LEFT JOIN users u ON o.organizer_id = u.id
                                                WHERE a.user_id = ? 
                                                AND a.status = 'accepted'
                                                AND (a.reviewed_at IS NULL OR a.reviewed_at > DATE_SUB(NOW(), INTERVAL 7 DAY))
                                                ORDER BY a.reviewed_at DESC, a.applied_at DESC
                                                LIMIT 5
                                            ");
                                            $accepted_apps_stmt->execute([$current_user['id']]);
                                            $accepted_apps = $accepted_apps_stmt->fetchAll();
                                            foreach ($accepted_apps as $app):
                                                $has_notifications = true;
                                    ?>
                                                <li>
                                                    <a class="dropdown-item" href="/inclusify/user/application-accepted.php?id=<?php echo $app['id']; ?>">
                                                        <div class="d-flex w-100 justify-content-between">
                                                            <div>
                                                                <strong class="text-success">Application Accepted! 🎉</strong>
                                                                <p class="mb-0 small text-muted"><?php echo escape($app['job_title']); ?></p>
                                                                <small class="text-muted"><?php echo escape($app['org_name'] ?? $app['organizer_name']); ?></small>
                                                            </div>
                                                            <small class="text-muted"><?php echo date('M j', strtotime($app['reviewed_at'] ?: $app['applied_at'])); ?></small>
                                                        </div>
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                    <?php 
                                            endforeach;
                                        } catch (PDOException $e) {
                                            // Ignore errors
                                        }
                                    }
                                    
                                    if (!$has_notifications):
                                    ?>
                                        <li><span class="dropdown-item-text text-muted">No new notifications</span></li>
                                    <?php endif; ?>
                                <?php elseif (hasRole('organizer') && $notification_count > 0): ?>
                                    <?php
                                    // Get recent applications for organizers
                                    try {
                                        $recent_apps_stmt = $pdo->prepare("
                                            SELECT a.*, o.title as job_title, u.name as applicant_name
                                            FROM applications a
                                            JOIN opportunities o ON a.opportunity_id = o.id
                                            JOIN users u ON a.user_id = u.id
                                            WHERE o.organizer_id = ? 
                                            AND a.status = 'applied'
                                            AND a.reviewed_at IS NULL
                                            ORDER BY a.applied_at DESC
                                            LIMIT 5
                                        ");
                                        $recent_apps_stmt->execute([$current_user['id']]);
                                        $recent_apps = $recent_apps_stmt->fetchAll();
                                        foreach ($recent_apps as $app):
                                    ?>
                                        <li>
                                            <a class="dropdown-item" href="/inclusify/organizer/view-applications.php">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <div>
                                                        <strong>New Application</strong>
                                                        <p class="mb-0 small text-muted"><?php echo escape($app['job_title']); ?></p>
                                                        <small class="text-muted">From <?php echo escape($app['applicant_name']); ?></small>
                                                    </div>
                                                    <small class="text-muted"><?php echo date('M j', strtotime($app['applied_at'])); ?></small>
                                                </div>
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                    <?php 
                                        endforeach;
                                    } catch (PDOException $e) {
                                        // Ignore errors
                                    }
                                    ?>
                                <?php else: ?>
                                    <li><span class="dropdown-item-text text-muted">No new notifications</span></li>
                                <?php endif; ?>
                                <li>
                                    <a class="dropdown-item text-center" href="<?php echo hasRole('user') ? '/inclusify/user/job-offers.php' : '/inclusify/organizer/view-applications.php'; ?>">
                                        <strong>View All</strong>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        
                        <!-- User Profile -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i>
                                <?php echo escape($current_user['name']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="/inclusify/profile.php">
                                    <i class="fas fa-user-edit me-2"></i>Profile
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/inclusify/logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/inclusify/login.php">
                                <i class="fas fa-sign-in-alt me-1"></i>Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/inclusify/signup.php">
                                <i class="fas fa-user-plus me-1"></i>Sign Up
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>


    <main class="main-content">
