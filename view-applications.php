<?php
require_once '../includes/auth.php';
requireRole('organizer');

$user = getCurrentUser();

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $application_id = intval($_POST['application_id']);
    $new_status = $_POST['status'];
    $notes = trim($_POST['notes'] ?? '');
    
    $allowed_statuses = ['applied', 'under_review', 'accepted', 'rejected'];
    if (in_array($new_status, $allowed_statuses)) {
        $stmt = $pdo->prepare("
            UPDATE applications a
            JOIN opportunities o ON a.opportunity_id = o.id
            SET a.status = ?, a.notes = ?, a.reviewed_at = NOW()
            WHERE a.id = ? AND o.organizer_id = ?
        ");
        $stmt->execute([$new_status, $notes, $application_id, $user['id']]);
        
        $success_message = "Application status updated successfully!";
    }
}

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$opportunity_filter = $_GET['opportunity'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Build query conditions
$where_conditions = ['o.organizer_id = ?'];
$params = [$user['id']];

if ($status_filter) {
    $where_conditions[] = 'a.status = ?';
    $params[] = $status_filter;
}

if ($opportunity_filter) {
    $where_conditions[] = 'o.id = ?';
    $params[] = $opportunity_filter;
}

$where_clause = implode(' AND ', $where_conditions);

// Get total count
$count_sql = "
    SELECT COUNT(*) as total
    FROM applications a
    JOIN opportunities o ON a.opportunity_id = o.id
    JOIN users u ON a.user_id = u.id
    WHERE $where_clause
";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_applications = $count_stmt->fetch()['total'];
$total_pages = ceil($total_applications / $per_page);

// Get applications
$sql = "
    SELECT a.*, o.title as opportunity_title, o.type as opportunity_type,
           u.name as applicant_name, u.email as applicant_email, 
           u.phone as applicant_phone, u.skills
    FROM applications a
    JOIN opportunities o ON a.opportunity_id = o.id
    JOIN users u ON a.user_id = u.id
    WHERE $where_clause
    ORDER BY a.applied_at DESC
    LIMIT $per_page OFFSET $offset
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$applications = $stmt->fetchAll();

// Get organizer's opportunities for filter dropdown
$stmt = $pdo->prepare("SELECT id, title FROM opportunities WHERE organizer_id = ? ORDER BY title");
$stmt->execute([$user['id']]);
$opportunities = $stmt->fetchAll();

$page_title = 'Review Applications';
include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="organizer-dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Review Applications</li>
                </ol>
            </nav>
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-inbox me-2"></i>Review Applications</h2>
                <a href="organizer-dashboard.php" class="btn btn-outline-primary">
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
                        <div class="col-md-4">
                            <label for="status" class="form-label">Filter by Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Statuses</option>
                                <option value="applied" <?php echo $status_filter === 'applied' ? 'selected' : ''; ?>>New Applications</option>
                                <option value="under_review" <?php echo $status_filter === 'under_review' ? 'selected' : ''; ?>>Under Review</option>
                                <option value="accepted" <?php echo $status_filter === 'accepted' ? 'selected' : ''; ?>>Accepted</option>
                                <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="opportunity" class="form-label">Filter by Opportunity</label>
                            <select class="form-select" id="opportunity" name="opportunity">
                                <option value="">All Opportunities</option>
                                <?php foreach ($opportunities as $opp): ?>
                                    <option value="<?php echo $opp['id']; ?>" <?php echo $opportunity_filter == $opp['id'] ? 'selected' : ''; ?>>
                                        <?php echo escape($opp['title']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-filter me-2"></i>Filter
                            </button>
                            <a href="view-applications.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Clear
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
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h5>No Applications Found</h5>
                        <p class="text-muted">
                            <?php if ($status_filter || $opportunity_filter): ?>
                                No applications match your current filters. Try adjusting your search criteria.
                            <?php else: ?>
                                You haven't received any applications yet. Start by posting some opportunities!
                            <?php endif; ?>
                        </p>
                        <?php if (!$status_filter && !$opportunity_filter): ?>
                            <a href="add-opportunity.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Post Your First Opportunity
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($applications as $application): ?>
                        <div class="col-lg-6 mb-4">
                            <div class="card h-100 <?php echo $application['status'] === 'applied' ? 'border-warning' : ''; ?>">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">
                                        <i class="fas fa-user me-2"></i>
                                        <?php echo escape($application['applicant_name']); ?>
                                    </h6>
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
                                    <h6 class="card-title">
                                        <i class="fas fa-briefcase me-2"></i>
                                        <?php echo escape($application['opportunity_title']); ?>
                                    </h6>
                                    
                                    <div class="row mb-3">
                                        <div class="col-sm-6">
                                            <small class="text-muted">Email:</small><br>
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
                                    
                                    <?php if ($application['resume_path']): ?>
                                        <div class="mt-3">
                                            <?php
                                            // Construct the correct path to the resume
                                            // Resume path in DB is just filename, stored in uploads/resumes/ directory
                                            $resume_url = '../uploads/resumes/' . $application['resume_path'];
                                            ?>
                                            <a href="<?php echo escape($resume_url); ?>" 
                                               class="btn btn-outline-primary btn-sm" target="_blank">
                                                <i class="fas fa-file-pdf me-2"></i>View Resume
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer">
                                    <form method="POST" class="d-flex gap-2">
                                        <input type="hidden" name="application_id" value="<?php echo $application['id']; ?>">
                                        <input type="hidden" name="update_status" value="1">
                                        
                                        <select name="status" class="form-select form-select-sm">
                                            <option value="applied" <?php echo $application['status'] === 'applied' ? 'selected' : ''; ?>>New</option>
                                            <option value="under_review" <?php echo $application['status'] === 'under_review' ? 'selected' : ''; ?>>Under Review</option>
                                            <option value="accepted" <?php echo $application['status'] === 'accepted' ? 'selected' : ''; ?>>Accept</option>
                                            <option value="rejected" <?php echo $application['status'] === 'rejected' ? 'selected' : ''; ?>>Reject</option>
                                        </select>
                                        
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fas fa-save me-1"></i>Update
                                        </button>
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
                                    <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo urlencode($status_filter); ?>&opportunity=<?php echo urlencode($opportunity_filter); ?>">
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
