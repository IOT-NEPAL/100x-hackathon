<?php
require_once '../includes/auth.php';
requireRole('admin');

$user = getCurrentUser();

// Handle opportunity actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['toggle_status'])) {
        $opportunity_id = intval($_POST['opportunity_id']);
        $stmt = $pdo->prepare("UPDATE opportunities SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$opportunity_id]);
        $success_message = "Opportunity status updated successfully!";
    }
    
    if (isset($_POST['delete_opportunity'])) {
        $opportunity_id = intval($_POST['opportunity_id']);
        
        try {
            // Delete related applications first
            $stmt = $pdo->prepare("DELETE FROM applications WHERE opportunity_id = ?");
            $stmt->execute([$opportunity_id]);
            
            // Delete the opportunity
            $stmt = $pdo->prepare("DELETE FROM opportunities WHERE id = ?");
            $stmt->execute([$opportunity_id]);
            
            $success_message = "Opportunity deleted successfully!";
        } catch (PDOException $e) {
            $error_message = "Error deleting opportunity: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['feature_opportunity'])) {
        $opportunity_id = intval($_POST['opportunity_id']);
        $is_featured = intval($_POST['is_featured']);
        
        // First, unfeatured all other opportunities if we're featuring this one
        if ($is_featured) {
            $stmt = $pdo->prepare("UPDATE opportunities SET is_featured = 0");
            $stmt->execute();
        }
        
        $stmt = $pdo->prepare("UPDATE opportunities SET is_featured = ? WHERE id = ?");
        $stmt->execute([$is_featured, $opportunity_id]);
        $success_message = $is_featured ? "Opportunity featured successfully!" : "Opportunity unfeatured successfully!";
    }
}

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$type_filter = $_GET['type'] ?? '';
$organizer_filter = $_GET['organizer'] ?? '';
$search_query = trim($_GET['search'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 15;
$offset = ($page - 1) * $per_page;

// Build query conditions
$where_conditions = ['1=1'];
$params = [];

if ($status_filter !== '') {
    $where_conditions[] = 'o.is_active = ?';
    $params[] = $status_filter === 'active' ? 1 : 0;
}

if ($type_filter) {
    $where_conditions[] = 'o.type = ?';
    $params[] = $type_filter;
}

if ($organizer_filter) {
    $where_conditions[] = 'o.organizer_id = ?';
    $params[] = $organizer_filter;
}

if ($search_query) {
    $where_conditions[] = '(o.title LIKE ? OR o.description LIKE ? OR o.requirements LIKE ? OR o.location LIKE ?)';
    $params[] = '%' . $search_query . '%';
    $params[] = '%' . $search_query . '%';
    $params[] = '%' . $search_query . '%';
    $params[] = '%' . $search_query . '%';
}

$where_clause = implode(' AND ', $where_conditions);

// Get total count
$count_sql = "
    SELECT COUNT(*) as total 
    FROM opportunities o 
    LEFT JOIN users u ON o.organizer_id = u.id 
    WHERE $where_clause
";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_opportunities = $count_stmt->fetch()['total'];
$total_pages = ceil($total_opportunities / $per_page);

// Get opportunities with organizer info and application counts
$sql = "
    SELECT o.*, u.name as organizer_name, u.org_name, u.email as organizer_email,
           COUNT(a.id) as application_count,
           SUM(CASE WHEN a.status = 'applied' THEN 1 ELSE 0 END) as new_applications
    FROM opportunities o
    LEFT JOIN users u ON o.organizer_id = u.id
    LEFT JOIN applications a ON o.id = a.opportunity_id
    WHERE $where_clause
    GROUP BY o.id
    ORDER BY o.date_posted DESC
    LIMIT $per_page OFFSET $offset
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$opportunities = $stmt->fetchAll();

// Get organizers for filter dropdown
$stmt = $pdo->prepare("SELECT id, name, org_name FROM users WHERE role = 'organizer' AND is_active = 1 ORDER BY name");
$stmt->execute();
$organizers = $stmt->fetchAll();

$page_title = 'Manage Jobs';
include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="admin-dashboard.php">Admin Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Manage Jobs</li>
                </ol>
            </nav>
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-briefcase me-2"></i>Manage Jobs</h2>
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
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo escape($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Filters and Search -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?php echo escape($search_query); ?>" 
                                   placeholder="Search jobs...">
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Statuses</option>
                                <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="type" class="form-label">Type</label>
                            <select class="form-select" id="type" name="type">
                                <option value="">All Types</option>
                                <option value="employment" <?php echo $type_filter === 'employment' ? 'selected' : ''; ?>>Employment</option>
                                <option value="internship" <?php echo $type_filter === 'internship' ? 'selected' : ''; ?>>Internship</option>
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
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search me-1"></i>Filter
                            </button>
                            <a href="manage-opportunities.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Summary Statistics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h4 class="text-primary"><?php echo number_format($total_opportunities); ?></h4>
                            <p class="card-text">Total Jobs</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h4 class="text-success">
                                <?php 
                                $active_count = 0;
                                foreach ($opportunities as $opp) {
                                    if ($opp['is_active']) $active_count++;
                                }
                                echo $active_count;
                                ?>
                            </h4>
                            <p class="card-text">Active</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h4 class="text-info">
                                <?php 
                                $total_applications = 0;
                                foreach ($opportunities as $opp) {
                                    $total_applications += $opp['application_count'];
                                }
                                echo $total_applications;
                                ?>
                            </h4>
                            <p class="card-text">Total Applications</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h4 class="text-warning">
                                <?php 
                                $featured_count = 0;
                                foreach ($opportunities as $opp) {
                                    if (isset($opp['is_featured']) && $opp['is_featured']) $featured_count++;
                                }
                                echo $featured_count;
                                ?>
                            </h4>
                            <p class="card-text">Featured</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Opportunities List -->
            <?php if (empty($opportunities)): ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-briefcase fa-3x text-muted mb-3"></i>
                        <h5>No Opportunities Found</h5>
                        <p class="text-muted">No opportunities match your current search criteria.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($opportunities as $opportunity): ?>
                        <div class="col-lg-6 mb-4">
                            <div class="card h-100 <?php echo !$opportunity['is_active'] ? 'border-secondary' : ((isset($opportunity['is_featured']) && $opportunity['is_featured']) ? 'border-warning' : ''); ?>">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">
                                            <?php if (isset($opportunity['is_featured']) && $opportunity['is_featured']): ?>
                                                <i class="fas fa-star text-warning me-1" title="Featured"></i>
                                            <?php endif; ?>
                                            <?php echo escape($opportunity['title']); ?>
                                        </h6>
                                        <small class="text-muted">
                                            By: <?php echo escape($opportunity['organizer_name']); ?>
                                            <?php if ($opportunity['org_name']): ?>
                                                (<?php echo escape($opportunity['org_name']); ?>)
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    <div>
                                        <span class="badge bg-<?php echo $opportunity['is_active'] ? 'success' : 'secondary'; ?> me-1">
                                            <?php echo $opportunity['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                        <span class="badge bg-<?php 
                                            echo match($opportunity['type']) {
                                                'employment' => 'primary',
                                                'internship' => 'info',
                                                default => 'secondary'
                                            };
                                        ?>">
                                            <?php echo ucfirst($opportunity['type']); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted mb-2">
                                        <i class="fas fa-map-marker-alt me-1"></i><?php echo escape($opportunity['location']); ?>
                                        <span class="ms-3">
                                            <i class="fas fa-calendar me-1"></i>
                                            Posted <?php echo date('M j, Y', strtotime($opportunity['date_posted'])); ?>
                                        </span>
                                    </p>
                                    
                                    <p class="card-text">
                                        <?php echo nl2br(escape(substr($opportunity['description'], 0, 150))); ?>
                                        <?php if (strlen($opportunity['description']) > 150): ?>
                                            <a href="../view-opportunity.php?id=<?php echo $opportunity['id']; ?>" class="text-decoration-none">... Read more</a>
                                        <?php endif; ?>
                                    </p>
                                    
                                    <div class="row text-center mb-3">
                                        <div class="col-3">
                                            <small class="text-muted">Applications</small><br>
                                            <strong><?php echo $opportunity['application_count']; ?></strong>
                                            <?php if ($opportunity['new_applications'] > 0): ?>
                                                <span class="badge bg-warning ms-1"><?php echo $opportunity['new_applications']; ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-3">
                                            <small class="text-muted">Views</small><br>
                                            <strong><?php echo $opportunity['views_count']; ?></strong>
                                        </div>
                                        <div class="col-3">
                                            <small class="text-muted">Days Active</small><br>
                                            <strong><?php echo floor((time() - strtotime($opportunity['date_posted'])) / 86400); ?></strong>
                                        </div>
                                        <div class="col-3">
                                            <small class="text-muted">Rate</small><br>
                                            <strong>
                                                <?php 
                                                $rate = $opportunity['views_count'] > 0 ? 
                                                    round(($opportunity['application_count'] / $opportunity['views_count']) * 100, 1) : 0;
                                                echo $rate . '%';
                                                ?>
                                            </strong>
                                        </div>
                                    </div>
                                    
                                    <?php if ($opportunity['application_deadline']): ?>
                                        <p class="text-muted mb-2">
                                            <i class="fas fa-calendar-alt me-1"></i>
                                            Deadline: <?php echo date('M j, Y', strtotime($opportunity['application_deadline'])); ?>
                                            <?php 
                                            $days_left = floor((strtotime($opportunity['application_deadline']) - time()) / 86400);
                                            if ($days_left < 0): ?>
                                                <span class="badge bg-danger ms-2">Expired</span>
                                            <?php elseif ($days_left <= 7): ?>
                                                <span class="badge bg-warning ms-2"><?php echo $days_left; ?> days left</span>
                                            <?php endif; ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <div class="mb-2">
                                        <small class="text-muted">Organizer Contact:</small><br>
                                        <small>
                                            <a href="mailto:<?php echo escape($opportunity['organizer_email']); ?>">
                                                <?php echo escape($opportunity['organizer_email']); ?>
                                            </a>
                                        </small>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="btn-group w-100 mb-2" role="group">
                                        <a href="../view-opportunity.php?id=<?php echo $opportunity['id']; ?>" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye me-1"></i>View
                                        </a>
                                        
                                        <!-- Toggle Status -->
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="opportunity_id" value="<?php echo $opportunity['id']; ?>">
                                            <input type="hidden" name="toggle_status" value="1">
                                            <button type="submit" class="btn btn-outline-<?php echo $opportunity['is_active'] ? 'warning' : 'success'; ?> btn-sm">
                                                <i class="fas fa-toggle-<?php echo $opportunity['is_active'] ? 'off' : 'on'; ?> me-1"></i>
                                                <?php echo $opportunity['is_active'] ? 'Disable' : 'Enable'; ?>
                                            </button>
                                        </form>
                                        
                                        <!-- Feature Toggle -->
                                        <button type="button" class="btn btn-outline-<?php echo (isset($opportunity['is_featured']) && $opportunity['is_featured']) ? 'warning' : 'info'; ?> btn-sm"
                                                onclick="toggleFeature(<?php echo $opportunity['id']; ?>, <?php echo (isset($opportunity['is_featured']) && $opportunity['is_featured']) ? 0 : 1; ?>)">
                                            <i class="fas fa-star me-1"></i>
                                            <?php echo (isset($opportunity['is_featured']) && $opportunity['is_featured']) ? 'Unfeature' : 'Feature'; ?>
                                        </button>
                                        
                                        <!-- Delete -->
                                        <button type="button" class="btn btn-outline-danger btn-sm" 
                                                onclick="confirmDelete(<?php echo $opportunity['id']; ?>, '<?php echo escape($opportunity['title']); ?>')">
                                            <i class="fas fa-trash me-1"></i>Delete
                                        </button>
                                    </div>
                                    
                                    <?php if ($opportunity['application_count'] > 0): ?>
                                        <a href="view-applications.php?opportunity=<?php echo $opportunity['id']; ?>" 
                                           class="btn btn-primary btn-sm w-100">
                                            <i class="fas fa-inbox me-2"></i>
                                            View Applications (<?php echo $opportunity['application_count']; ?>)
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Jobs pagination" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search_query); ?>&status=<?php echo urlencode($status_filter); ?>&type=<?php echo urlencode($type_filter); ?>&organizer=<?php echo urlencode($organizer_filter); ?>">
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

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this opportunity?</p>
                <p><strong id="deleteOpportunityTitle"></strong></p>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone. All applications for this opportunity will also be deleted.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" id="deleteForm" class="d-inline">
                    <input type="hidden" name="opportunity_id" id="deleteOpportunityId">
                    <input type="hidden" name="delete_opportunity" value="1">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>Delete Opportunity
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Feature Forms (hidden) -->
<form method="POST" id="featureForm" style="display: none;">
    <input type="hidden" name="opportunity_id" id="featureOpportunityId">
    <input type="hidden" name="is_featured" id="featureStatus">
    <input type="hidden" name="feature_opportunity" value="1">
</form>

<script>
function confirmDelete(opportunityId, opportunityTitle) {
    document.getElementById('deleteOpportunityId').value = opportunityId;
    document.getElementById('deleteOpportunityTitle').textContent = opportunityTitle;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

function toggleFeature(opportunityId, isFeatured) {
    document.getElementById('featureOpportunityId').value = opportunityId;
    document.getElementById('featureStatus').value = isFeatured;
    document.getElementById('featureForm').submit();
}
</script>

<?php include '../includes/footer.php'; ?>
