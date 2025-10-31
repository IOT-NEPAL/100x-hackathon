<?php
require_once 'includes/auth.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header("Location: opportunities.php");
    exit;
}

// Get opportunity details with organizer info
$stmt = $pdo->prepare("
    SELECT o.*, u.name as organizer_name, u.org_name, u.email as organizer_email, u.phone as organizer_phone
    FROM opportunities o
    LEFT JOIN users u ON o.organizer_id = u.id
    WHERE o.id = ? AND o.is_active = 1
");
$stmt->execute([$id]);
$opportunity = $stmt->fetch();

if (!$opportunity) {
    header("Location: opportunities.php?error=not_found");
    exit;
}

// Check if user has already applied
$has_applied = false;
$application = null;
if (isLoggedIn() && hasRole('user')) {
    $stmt = $pdo->prepare("SELECT * FROM applications WHERE user_id = ? AND opportunity_id = ?");
    $stmt->execute([getCurrentUser()['id'], $id]);
    $application = $stmt->fetch();
    $has_applied = (bool)$application;
}

// Increment view count
$stmt = $pdo->prepare("UPDATE opportunities SET views_count = views_count + 1 WHERE id = ?");
$stmt->execute([$id]);

// Get related opportunities from same organizer
$stmt = $pdo->prepare("
    SELECT o.*, u.org_name 
    FROM opportunities o
    LEFT JOIN users u ON o.organizer_id = u.id
    WHERE o.organizer_id = ? AND o.id != ? AND o.is_active = 1
    ORDER BY o.date_posted DESC
    LIMIT 3
");
$stmt->execute([$opportunity['organizer_id'], $id]);
$related_opportunities = $stmt->fetchAll();

$page_title = $opportunity['title'];
include 'includes/header.php';

// Handle success/error messages
$success_msg = $_GET['success'] ?? '';
$error_msg = $_GET['error'] ?? '';
?>

<div class="container mt-4">
    <?php if ($success_msg === 'offer_sent'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>Job offer sent successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($error_msg === 'offer_failed'): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>Failed to send job offer. Please try again.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <!-- Breadcrumb -->
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="opportunities.php">Jobs</a></li>
            <li class="breadcrumb-item active"><?php echo escape($opportunity['title']); ?></li>
        </ol>
    </nav>
    
    <div class="row">
        <div class="col-lg-8">
            <!-- Main Opportunity Details -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="badge bg-<?php echo getTypeColor($opportunity['type']); ?> mb-2">
                                <?php echo ucfirst(escape($opportunity['type'])); ?>
                            </span>
                            <h1 class="h2 fw-bold"><?php echo escape($opportunity['title']); ?></h1>
                        </div>
                        
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <i class="fas fa-building text-primary me-2"></i>
                                <strong><?php echo escape($opportunity['org_name'] ?? $opportunity['organizer_name']); ?></strong>
                            </p>
                            <?php if ($opportunity['location']): ?>
                                <p class="mb-2">
                                    <i class="fas fa-map-marker-alt text-success me-2"></i>
                                    <?php echo escape($opportunity['location']); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <p class="mb-2">
                                <i class="fas fa-calendar text-info me-2"></i>
                                Posted: <?php echo date('M j, Y', strtotime($opportunity['date_posted'])); ?>
                            </p>
                            <?php if (!empty($opportunity['application_deadline'])): ?>
                                <p class="mb-2">
                                    <i class="fas fa-clock text-warning me-2"></i>
                                    Deadline: <?php echo date('M j, Y', strtotime($opportunity['application_deadline'])); ?>
                                </p>
                            <?php endif; ?>
                            <p class="mb-2">
                                <i class="fas fa-eye text-muted me-2"></i>
                                <?php echo number_format($opportunity['views_count']); ?> views
                            </p>
                        </div>
                    </div>
                    
                    <!-- Description -->
                    <div class="mb-4">
                        <h4>Description</h4>
                        <div class="text-muted">
                            <?php echo nl2br(escape($opportunity['description'])); ?>
                        </div>
                    </div>
                    
                    <!-- Requirements -->
                    <?php if ($opportunity['requirements']): ?>
                        <div class="mb-4">
                            <h4>Requirements</h4>
                            <div class="text-muted">
                                <?php echo nl2br(escape($opportunity['requirements'])); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Contact Information -->
                    <?php if (!empty($opportunity['contact_email']) || !empty($opportunity['contact_phone'])): ?>
                        <div class="mb-4">
                            <h4>Contact Information</h4>
                            <div class="text-muted">
                                <?php if (!empty($opportunity['contact_email'])): ?>
                                    <p class="mb-1">
                                        <i class="fas fa-envelope me-2"></i>
                                        <a href="mailto:<?php echo escape($opportunity['contact_email']); ?>">
                                            <?php echo escape($opportunity['contact_email']); ?>
                                        </a>
                                    </p>
                                <?php endif; ?>
                                <?php if (!empty($opportunity['contact_phone'])): ?>
                                    <p class="mb-1">
                                        <i class="fas fa-phone me-2"></i>
                                        <a href="tel:<?php echo escape($opportunity['contact_phone']); ?>">
                                            <?php echo escape($opportunity['contact_phone']); ?>
                                        </a>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Attachment -->
                    <?php if (!empty($opportunity['file_path'])): ?>
                        <div class="mb-4">
                            <h4>Attachment</h4>
                            <a href="uploads/opportunity_files/<?php echo escape($opportunity['file_path']); ?>" 
                               class="btn btn-outline-primary" target="_blank">
                                <i class="fas fa-download me-2"></i>Download Attachment
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Application Status (if user has applied) -->
            <?php if ($has_applied): ?>
                <div class="card mt-4">
                    <div class="card-body">
                        <h5><i class="fas fa-info-circle me-2"></i>Your Application</h5>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-<?php echo getStatusColor($application['status']); ?> me-2">
                                    <?php echo ucfirst(str_replace('_', ' ', escape($application['status']))); ?>
                                </span>
                                <small class="text-muted">
                                    Applied on <?php echo date('M j, Y', strtotime($application['applied_at'])); ?>
                                </small>
                            </div>
                            <div>
                                <a href="user/my-applications.php" class="btn btn-sm btn-outline-primary">
                                    View All Applications
                                </a>
                            </div>
                        </div>
                        
                        <?php if ($application['cover_letter']): ?>
                            <div class="mt-3">
                                <h6>Your Cover Letter:</h6>
                                <p class="text-muted small"><?php echo nl2br(escape($application['cover_letter'])); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Application Card -->
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <?php if (!isLoggedIn()): ?>
                        <h5>Interested in this opportunity?</h5>
                        <p class="text-muted">Sign up or log in to apply</p>
                        <div class="d-grid gap-2">
                            <a href="signup.php" class="btn btn-primary">
                                <i class="fas fa-user-plus me-2"></i>Sign Up
                            </a>
                            <a href="login.php" class="btn btn-outline-primary">
                                <i class="fas fa-sign-in-alt me-2"></i>Log In
                            </a>
                        </div>
                    <?php elseif (hasRole('user')): ?>
                        <?php if ($has_applied): ?>
                            <h5 class="text-success">
                                <i class="fas fa-check-circle me-2"></i>Application Submitted
                            </h5>
                            <p class="text-muted">You have already applied for this opportunity</p>
                            <span class="badge bg-<?php echo getStatusColor($application['status']); ?> mb-3">
                                <?php echo ucfirst(str_replace('_', ' ', escape($application['status']))); ?>
                            </span>
                            <div class="d-grid">
                                <a href="user/my-applications.php" class="btn btn-outline-primary">
                                    <i class="fas fa-list me-2"></i>View My Applications
                                </a>
                            </div>
                        <?php else: ?>
                            <h5>Apply for this opportunity</h5>
                            <p class="text-muted">Join thousands of applicants</p>
                            <div class="d-grid">
                                <a href="apply.php?id=<?php echo $opportunity['id']; ?>" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i>Apply Now
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php elseif (hasRole('organizer')): ?>
                        <?php if ($opportunity['organizer_id'] == getCurrentUser()['id']): ?>
                            <h5>Your Opportunity</h5>
                            <p class="text-muted">You posted this opportunity</p>
                            <div class="d-grid gap-2">
                                <a href="organizer/edit-opportunity.php?id=<?php echo $opportunity['id']; ?>" 
                                   class="btn btn-primary">
                                    <i class="fas fa-edit me-2"></i>Edit
                                </a>
                                <a href="organizer/view-applications.php?id=<?php echo $opportunity['id']; ?>" 
                                   class="btn btn-outline-primary">
                                    <i class="fas fa-users me-2"></i>View Applications
                                </a>
                                <a href="#capable-candidates" class="btn btn-outline-primary">
                                    <i class="fas fa-user-check me-2"></i>Capable Candidates
                                </a>
                            </div>
                        <?php else: ?>
                            <h5>Contact Organizer</h5>
                            <p class="text-muted">Get in touch for more information</p>
                            <div class="d-grid">
                                <a href="mailto:<?php echo escape($opportunity['organizer_email']); ?>?subject=Inquiry about <?php echo urlencode($opportunity['title']); ?>" 
                                   class="btn btn-outline-primary">
                                    <i class="fas fa-envelope me-2"></i>Send Email
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <!-- Share buttons -->
                    <hr>
                    <h6>Share this opportunity</h6>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode($opportunity['title']); ?>&url=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" 
                           target="_blank" class="btn btn-sm btn-outline-info">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" 
                           target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" 
                           target="_blank" class="btn btn-sm btn-outline-secondary">
                            <i class="fab fa-linkedin"></i>
                        </a>
                        <button onclick="copyToClipboard()" class="btn btn-sm btn-outline-dark">
                            <i class="fas fa-link"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Organizer Info -->
            <div class="card mt-4">
                <div class="card-body">
                    <h6><i class="fas fa-building me-2"></i>About the Organization</h6>
                    <h5><?php echo escape($opportunity['org_name'] ?? $opportunity['organizer_name']); ?></h5>
                    
                    <?php if ($opportunity['organizer_email']): ?>
                        <p class="mb-1">
                            <i class="fas fa-envelope text-muted me-2"></i>
                            <a href="mailto:<?php echo escape($opportunity['organizer_email']); ?>">
                                <?php echo escape($opportunity['organizer_email']); ?>
                            </a>
                        </p>
                    <?php endif; ?>
                    
                    <?php if ($opportunity['organizer_phone']): ?>
                        <p class="mb-1">
                            <i class="fas fa-phone text-muted me-2"></i>
                            <a href="tel:<?php echo escape($opportunity['organizer_phone']); ?>">
                                <?php echo escape($opportunity['organizer_phone']); ?>
                            </a>
                        </p>
                    <?php endif; ?>
                    
                    <div class="mt-3">
                        <a href="opportunities.php?organizer=<?php echo $opportunity['organizer_id']; ?>" 
                           class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-briefcase me-1"></i>View All Jobs
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Related Jobs -->
            <?php if (!empty($related_opportunities)): ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="mb-0">More from this Organization</h6>
                    </div>
                    <div class="card-body">
                        <?php foreach ($related_opportunities as $related): ?>
                            <div class="border-bottom pb-2 mb-2">
                                <h6 class="mb-1">
                                    <a href="view-opportunity.php?id=<?php echo $related['id']; ?>" 
                                       class="text-decoration-none">
                                        <?php echo escape($related['title']); ?>
                                    </a>
                                </h6>
                                <small class="text-muted">
                                    <span class="badge badge-sm bg-secondary">
                                        <?php echo ucfirst(escape($related['type'])); ?>
                                    </span>
                                    <?php echo date('M j', strtotime($related['date_posted'])); ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Capable Candidates Section (for job owner only) -->
            <?php if (isLoggedIn() && hasRole('organizer') && $opportunity['organizer_id'] == getCurrentUser()['id']): ?>
                <?php
                // Get capable candidates (students whose skills match job requirements)
                $job_requirements = strtolower($opportunity['requirements'] ?? '');
                $requirements_array = [];
                if (!empty($job_requirements)) {
                    $requirements_array = array_map('trim', explode(',', $job_requirements));
                    $requirements_array = array_filter($requirements_array);
                    $requirements_array = array_map('strtolower', $requirements_array);
                }
                
                // Find students with matching skills
                $capable_candidates = [];
                if (!empty($requirements_array)) {
                    $skills_conditions = [];
                    $params = [];
                    
                    foreach ($requirements_array as $req) {
                        $skills_conditions[] = "LOWER(u.skills) LIKE ?";
                        $params[] = '%' . $req . '%';
                    }
                    
                    $skills_where = implode(' OR ', $skills_conditions);
                    
                    $candidates_stmt = $pdo->prepare("
                        SELECT u.id, u.name, u.email, u.skills, u.profile_pic
                        FROM users u
                        WHERE u.role = 'user'
                        AND u.is_active = 1
                        AND u.skills IS NOT NULL
                        AND u.skills != ''
                        AND ($skills_where)
                        AND u.id NOT IN (
                            SELECT user_id FROM applications WHERE opportunity_id = ?
                        )
                        ORDER BY u.name ASC
                        LIMIT 20
                    ");
                    
                    $execute_params = $params;
                    $execute_params[] = $opportunity['id'];
                    
                    $candidates_stmt->execute($execute_params);
                    $capable_candidates = $candidates_stmt->fetchAll();
                }
                ?>
                
                <div class="card mt-4" id="capable-candidates">
                    <div class="card-header" style="background: #1a1a1a; color: white; border-bottom: 3px solid #ffff00; font-weight: 700;">
                        <h5 class="mb-0"><i class="fas fa-user-check me-2"></i>Capable Candidates</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($requirements_array)): ?>
                            <p class="text-muted">Add requirements to this job to find capable candidates.</p>
                        <?php elseif (empty($capable_candidates)): ?>
                            <p class="text-muted">No candidates found with matching skills. Try adjusting the requirements.</p>
                        <?php else: ?>
                            <p class="text-muted small mb-3">
                                Found <?php echo count($capable_candidates); ?> candidate(s) whose skills match your job requirements.
                            </p>
                            <div class="list-group">
                                <?php foreach ($capable_candidates as $candidate): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-start">
                                        <div class="d-flex align-items-center flex-grow-1">
                                            <?php if ($candidate['profile_pic']): ?>
                                                <img src="uploads/profile_pics/<?php echo escape($candidate['profile_pic']); ?>" 
                                                     alt="<?php echo escape($candidate['name']); ?>" 
                                                     class="rounded-circle me-3" 
                                                     width="50" height="50">
                                            <?php else: ?>
                                                <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-3" 
                                                     style="width: 50px; height: 50px;">
                                                    <i class="fas fa-user text-white"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1"><?php echo escape($candidate['name']); ?></h6>
                                                <p class="mb-1 text-muted small">
                                                    <i class="fas fa-envelope me-1"></i><?php echo escape($candidate['email']); ?>
                                                </p>
                                                <?php if ($candidate['skills']): ?>
                                                    <p class="mb-0 small">
                                                        <strong>Skills:</strong> <?php echo escape(substr($candidate['skills'], 0, 100)); ?>
                                                        <?php if (strlen($candidate['skills']) > 100): ?>...<?php endif; ?>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="ms-3">
                                            <a href="send-job-offer.php?opportunity_id=<?php echo $opportunity['id']; ?>&student_id=<?php echo $candidate['id']; ?>" 
                                               class="btn btn-sm btn-primary">
                                                <i class="fas fa-paper-plane me-1"></i>Send Offer
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function copyToClipboard() {
    navigator.clipboard.writeText(window.location.href).then(function() {
        // Link copied successfully (no notification)
    }).catch(function() {
        // Fallback: select the URL for manual copying
        prompt('Copy this link:', window.location.href);
    });
}
</script>

<?php
function getTypeColor($type) {
    $colors = [
        'employment' => 'primary',
        'internship' => 'info'
    ];
    return $colors[$type] ?? 'secondary';
}

function getStatusColor($status) {
    $colors = [
        'applied' => 'primary',
        'under_review' => 'warning',
        'accepted' => 'success',
        'rejected' => 'danger'
    ];
    return $colors[$status] ?? 'secondary';
}

include 'includes/footer.php';
?>
