<?php
require_once '../includes/auth.php';
requireRole('admin');

$user = getCurrentUser();

// Handle status updates (admin can override organizer decisions)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $application_id = intval($_POST['application_id']);
    $new_status = $_POST['status'];
    $notes = trim($_POST['notes'] ?? '');
    
    $allowed_statuses = ['applied', 'under_review', 'accepted', 'rejected'];
    if (in_array($new_status, $allowed_statuses)) {
        $stmt = $pdo->prepare("UPDATE applications SET status = ?, notes = ?, reviewed_at = NOW() WHERE id = ?");
        $stmt->execute([$new_status, $notes, $application_id]);
        
        $success_message = "Application status updated successfully!";
    }
}

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$opportunity_filter = $_GET['opportunity'] ?? '';
$organizer_filter = $_GET['organizer'] ?? '';
$applicant_filter = $_GET['applicant'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Build query conditions
$where_conditions = ['1=1'];
$params = [];

if ($status_filter) {
    $where_conditions[] = 'a.status = ?';
    $params[] = $status_filter;
}

if ($opportunity_filter) {
    $where_conditions[] = 'o.id = ?';
    $params[] = $opportunity_filter;
}

if ($organizer_filter) {
    $where_conditions[] = 'o.organizer_id = ?';
    $params[] = $organizer_filter;
}

if ($applicant_filter) {
    $where_conditions[] = 'u.id = ?';
    $params[] = $applicant_filter;
}

$where_clause = implode(' AND ', $where_conditions);

// Get total count
$count_sql = "
    SELECT COUNT(*) as total
    FROM applications a
    JOIN opportunities o ON a.opportunity_id = o.id
    JOIN users u ON a.user_id = u.id
    LEFT JOIN users org ON o.organizer_id = org.id
    WHERE $where_clause
";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_applications = $count_stmt->fetch()['total'];
$total_pages = ceil($total_applications / $per_page);

// Get applications with full details
$sql = "
    SELECT a.*, 
           o.title as opportunity_title, o.type as opportunity_type, o.location,
           u.name as applicant_name, u.email as applicant_email, 
           u.phone as applicant_phone, u.skills, u.disability_text,
           org.name as organizer_name, org.org_name, org.email as organizer_email
    FROM applications a
    JOIN opportunities o ON a.opportunity_id = o.id
    JOIN users u ON a.user_id = u.id
    LEFT JOIN users org ON o.organizer_id = org.id
    WHERE $where_clause
    ORDER BY a.applied_at DESC
    LIMIT $per_page OFFSET $offset
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$applications = $stmt->fetchAll();

// Get opportunities for filter dropdown
$stmt = $pdo->prepare("SELECT id, title FROM opportunities ORDER BY title");
$stmt->execute();
$opportunities = $stmt->fetchAll();

// Get organizers for filter dropdown
$stmt = $pdo->prepare("SELECT id, name, org_name FROM users WHERE role = 'organizer' AND is_active = 1 ORDER BY name");
$stmt->execute();
$organizers = $stmt->fetchAll();

// Get applicants for filter dropdown
$stmt = $pdo->prepare("
    SELECT DISTINCT u.id, u.name 
    FROM users u 
    JOIN applications a ON u.id = a.user_id 
    WHERE u.role = 'user' 
    ORDER BY u.name
");
$stmt->execute();
$applicants = $stmt->fetchAll();

$page_title = 'View All Applications';
include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="admin-dashboard.php">Admin Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">View Applications</li>
                </ol>
            </nav>
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-paper-plane me-2"></i>View All Applications</h2>
                <a href="admin-dashboard.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo escape($success_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Statuses</option>
                                <option value="applied" <?php echo $status_filter === 'applied' ? 'selected' : ''; ?>>New Applications</option>
                                <option value="under_review" <?php echo $status_filter === 'under_review' ? 'selected' : ''; ?>>Under Review</option>
                                <option value="accepted" <?php echo $status_filter === 'accepted' ? 'selected' : ''; ?>>Accepted</option>
                                <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="opportunity" class="form-label">Opportunity</label>
                            <select class="form-select" id="opportunity" name="opportunity">
                                <option value="">All Opportunities</option>
                                <?php foreach ($opportunities as $opp): ?>
                                    <option value="<?php echo $opp['id']; ?>" <?php echo $opportunity_filter == $opp['id'] ? 'selected' : ''; ?>>
                                        <?php echo escape(substr($opp['title'], 0, 50)) . (strlen($opp['title']) > 50 ? '...' : ''); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="organizer" class="form-label">Organizer</label>
                            <select class="form-select" id="organizer" name="organizer">
                                <option value="">All Organizers</option>
                                <?php foreach ($organizers as $org): ?>
                                    <option value="<?php echo $org['id']; ?>" <?php echo $organizer_filter == $org['id'] ? 'selected' : ''; ?>>
                                        <?php echo escape($org['name']) . ($org['org_name'] ? ' (' . escape($org['org_name']) . ')' : ''); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="applicant" class="form-label">Applicant</label>
                            <select class="form-select" id="applicant" name="applicant">
                                <option value="">All Applicants</option>
                                <?php foreach ($applicants as $app): ?>
                                    <option value="<?php echo $app['id']; ?>" <?php echo $applicant_filter == $app['id'] ? 'selected' : ''; ?>>
                                        <?php echo escape($app['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-filter me-2"></i>Filter
                            </button>
                            <a href="view-applications.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Clear Filters
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Statistics Summary -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h4 class="text-primary"><?php echo number_format($total_applications); ?></h4>
                            <p class="card-text">Total Applications</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h4 class="text-warning">
                                <?php 
                                $new_count = 0;
                                foreach ($applications as $app) {
                                    if ($app['status'] === 'applied') $new_count++;
                                }
                                echo $new_count;
                                ?>
                            </h4>
                            <p class="card-text">New Applications</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h4 class="text-info">
                                <?php 
                                $review_count = 0;
                                foreach ($applications as $app) {
                                    if ($app['status'] === 'under_review') $review_count++;
                                }
                                echo $review_count;
                                ?>
                            </h4>
                            <p class="card-text">Under Review</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h4 class="text-success">
                                <?php 
                                $accepted_count = 0;
                                foreach ($applications as $app) {
                                    if ($app['status'] === 'accepted') $accepted_count++;
                                }
                                echo $accepted_count;
                                ?>
                            </h4>
                            <p class="card-text">Accepted</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Applications List -->
            <?php if (empty($applications)): ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-paper-plane fa-3x text-muted mb-3"></i>
                        <h5>No Applications Found</h5>
                        <p class="text-muted">
                            <?php if ($status_filter || $opportunity_filter || $organizer_filter || $applicant_filter): ?>
                                No applications match your current filters.
                            <?php else: ?>
                                No applications have been submitted yet.
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($applications as $application): ?>
                        <div class="col-lg-6 mb-4">
                            <div class="card h-100 <?php echo $application['status'] === 'applied' ? 'border-warning' : ''; ?>">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">
                                            <i class="fas fa-user me-2"></i>
                                            <?php echo escape($application['applicant_name']); ?>
                                        </h6>
                                        <small class="text-muted">
                                            Applied to: <?php echo escape($application['opportunity_title']); ?>
                                        </small>
                                    </div>
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
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-sm-6">
                                            <small class="text-muted">Opportunity:</small><br>
                                            <strong><?php echo escape($application['opportunity_title']); ?></strong><br>
                                            <small class="text-muted">
                                                <?php echo ucfirst($application['opportunity_type']); ?> • 
                                                <?php echo escape($application['location']); ?>
                                            </small>
                                        </div>
                                        <div class="col-sm-6">
                                            <small class="text-muted">Organizer:</small><br>
                                            <strong><?php echo escape($application['organizer_name']); ?></strong>
                                            <?php if ($application['org_name']): ?>
                                                <br><small class="text-muted"><?php echo escape($application['org_name']); ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-sm-6">
                                            <small class="text-muted">Applicant Email:</small><br>
                                            <a href="mailto:<?php echo escape($application['applicant_email']); ?>">
                                                <?php echo escape($application['applicant_email']); ?>
                                            </a>
                                        </div>
                                        <div class="col-sm-6">
                                            <small class="text-muted">Phone:</small><br>
                                            <?php if ($application['applicant_phone']): ?>
                                                <a href="tel:<?php echo escape($application['applicant_phone']); ?>">
                                                    <?php echo escape($application['applicant_phone']); ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">Not provided</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <?php if ($application['cover_letter']): ?>
                                        <div class="mb-3">
                                            <small class="text-muted">Cover Letter:</small>
                                            <p class="small"><?php echo nl2br(escape(substr($application['cover_letter'], 0, 200))); ?>
                                                <?php if (strlen($application['cover_letter']) > 200): ?>
                                                    ... <button class="btn btn-link btn-sm p-0" onclick="showFullText(this, '<?php echo escape($application['cover_letter']); ?>')">Read more</button>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($application['skills']): ?>
                                        <div class="mb-3">
                                            <small class="text-muted">Skills:</small><br>
                                            <?php 
                                            $skills = explode(',', $application['skills']);
                                            foreach ($skills as $skill): 
                                            ?>
                                                <span class="badge bg-light text-dark me-1"><?php echo escape(trim($skill)); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($application['disability_text']): ?>
                                        <div class="mb-3">
                                            <small class="text-muted">Accessibility Information:</small>
                                            <p class="small"><?php echo nl2br(escape($application['disability_text'])); ?></p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <small class="text-muted">Applied:</small><br>
                                            <small><?php echo date('M j, Y g:i A', strtotime($application['applied_at'])); ?></small>
                                        </div>
                                        <?php if ($application['reviewed_at']): ?>
                                            <div class="col-sm-6">
                                                <small class="text-muted">Last Updated:</small><br>
                                                <small><?php echo date('M j, Y g:i A', strtotime($application['reviewed_at'])); ?></small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if ($application['notes']): ?>
                                        <div class="mt-3 p-2 bg-light rounded">
                                            <small class="text-muted">Notes:</small><br>
                                            <small><?php echo nl2br(escape($application['notes'])); ?></small>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($application['resume_path']): ?>
                                        <div class="mt-3">
                                            <a href="../<?php echo escape($application['resume_path']); ?>" 
                                               class="btn btn-outline-primary btn-sm" target="_blank">
                                                <i class="fas fa-file-pdf me-2"></i>View Resume
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer">
                                    <form method="POST" class="row g-2">
                                        <input type="hidden" name="application_id" value="<?php echo $application['id']; ?>">
                                        <input type="hidden" name="update_status" value="1">
                                        
                                        <div class="col-sm-6">
                                            <select name="status" class="form-select form-select-sm">
                                                <option value="applied" <?php echo $application['status'] === 'applied' ? 'selected' : ''; ?>>New</option>
                                                <option value="under_review" <?php echo $application['status'] === 'under_review' ? 'selected' : ''; ?>>Under Review</option>
                                                <option value="accepted" <?php echo $application['status'] === 'accepted' ? 'selected' : ''; ?>>Accept</option>
                                                <option value="rejected" <?php echo $application['status'] === 'rejected' ? 'selected' : ''; ?>>Reject</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-6">
                                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                                <i class="fas fa-save me-1"></i>Update Status
                                            </button>
                                        </div>
                                        <div class="col-12">
                                            <input type="text" name="notes" class="form-control form-control-sm" 
                                                   placeholder="Add admin notes..." value="<?php echo escape($application['notes'] ?? ''); ?>">
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Applications pagination">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo urlencode($status_filter); ?>&opportunity=<?php echo urlencode($opportunity_filter); ?>&organizer=<?php echo urlencode($organizer_filter); ?>&applicant=<?php echo urlencode($applicant_filter); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function showFullText(button, fullText) {
    const container = button.parentElement;
    container.innerHTML = fullText.replace(/\n/g, '<br>');
}
</script>

<?php include '../includes/footer.php'; ?>
