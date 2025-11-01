<?php
require_once 'db_config.php';

$page_title = 'Browse Jobs';

// Get filters
$type_filter = $_GET['type'] ?? 'all';
$search_query = $_GET['search'] ?? '';

// Build query
$sql = "
    SELECT o.*, u.org_name, u.name as organizer_name
    FROM opportunities o
    JOIN users u ON o.organizer_id = u.id
    WHERE o.is_active = 1
";

$params = [];

if ($type_filter !== 'all') {
    $sql .= " AND o.type = ?";
    $params[] = $type_filter;
}

if (!empty($search_query)) {
    $sql .= " AND (o.title LIKE ? OR o.description LIKE ? OR o.requirements LIKE ?)";
    $search_param = "%$search_query%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$sql .= " ORDER BY o.date_posted DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$opportunities = $stmt->fetchAll();

// Get counts
$stmt = $pdo->prepare("SELECT COUNT(*) FROM opportunities WHERE is_active = 1");
$stmt->execute();
$total_jobs = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM opportunities WHERE is_active = 1 AND type = 'employment'");
$stmt->execute();
$employment_count = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM opportunities WHERE is_active = 1 AND type = 'internship'");
$stmt->execute();
$internship_count = $stmt->fetchColumn();

// Check if user is logged in to show application status
$user_applications = [];
if (isLoggedIn() && hasRole('user')) {
    $stmt = $pdo->prepare("SELECT opportunity_id FROM applications WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_applications = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

include 'includes/public-header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h2><i class="fas fa-briefcase me-2"></i>Browse Job Opportunities</h2>
        <p class="text-muted">Explore exciting career opportunities that match your skills and interests</p>
    </div>
</div>

<!-- Filters -->
<div class="row mb-4">
    <div class="col-md-8">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="search" class="form-control" placeholder="Search jobs..." value="<?php echo escape($search_query); ?>">
            <select name="type" class="form-select" style="max-width: 200px;">
                <option value="all" <?php echo $type_filter === 'all' ? 'selected' : ''; ?>>All Types</option>
                <option value="employment" <?php echo $type_filter === 'employment' ? 'selected' : ''; ?>>Employment (<?php echo $employment_count; ?>)</option>
                <option value="internship" <?php echo $type_filter === 'internship' ? 'selected' : ''; ?>>Internship (<?php echo $internship_count; ?>)</option>
            </select>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i>
            </button>
            <?php if ($type_filter !== 'all' || !empty($search_query)): ?>
            <a href="opportunities.php" class="btn btn-outline-secondary">
                <i class="fas fa-times"></i> Clear
            </a>
            <?php endif; ?>
        </form>
    </div>
    <div class="col-md-4 text-end">
        <span class="text-muted"><strong><?php echo count($opportunities); ?></strong> job<?php echo count($opportunities) != 1 ? 's' : ''; ?> found</span>
    </div>
</div>

<!-- Jobs Listing -->
<?php if (empty($opportunities)): ?>
<div class="card">
    <div class="card-body text-center py-5">
        <i class="fas fa-search fa-4x text-muted mb-3"></i>
        <h4 class="text-muted">No jobs found</h4>
        <p class="text-muted">Try adjusting your search filters or check back later for new opportunities</p>
        <?php if ($type_filter !== 'all' || !empty($search_query)): ?>
        <a href="opportunities.php" class="btn btn-primary mt-3">
            <i class="fas fa-list me-2"></i>View All Jobs
        </a>
        <?php endif; ?>
    </div>
</div>
<?php else: ?>
<div class="row">
    <?php foreach ($opportunities as $job): ?>
    <div class="col-md-6 mb-4">
        <div class="card h-100 border-2 position-relative">
            <?php if (in_array($job['id'], $user_applications)): ?>
            <div class="position-absolute top-0 end-0 m-2">
                <span class="badge bg-success">Applied</span>
            </div>
            <?php endif; ?>
            
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="badge bg-<?php echo $job['type'] === 'employment' ? 'primary' : 'info'; ?>">
                        <?php echo ucfirst($job['type']); ?>
                    </span>
                    <small class="text-muted">
                        <i class="fas fa-calendar me-1"></i><?php echo formatDate($job['date_posted']); ?>
                    </small>
                </div>
                
                <h5 class="card-title mb-3"><?php echo escape($job['title']); ?></h5>
                
                <p class="text-muted mb-2">
                    <i class="fas fa-building me-2"></i><?php echo escape($job['org_name'] ?? $job['organizer_name']); ?>
                </p>
                
                <?php if ($job['location']): ?>
                <p class="text-muted mb-3">
                    <i class="fas fa-map-marker-alt me-2"></i><?php echo escape($job['location']); ?>
                </p>
                <?php endif; ?>
                
                <p class="card-text text-muted mb-3">
                    <?php echo escape(substr($job['description'], 0, 150)) . (strlen($job['description']) > 150 ? '...' : ''); ?>
                </p>
                
                <?php if ($job['requirements']): ?>
                <div class="mb-3">
                    <small class="text-muted fw-bold">Requirements:</small><br>
                    <small class="text-muted"><?php echo escape(substr($job['requirements'], 0, 100)) . (strlen($job['requirements']) > 100 ? '...' : ''); ?></small>
                </div>
                <?php endif; ?>
                
                <div class="d-flex gap-2">
                    <a href="view-opportunity.php?id=<?php echo $job['id']; ?>" class="btn btn-outline-primary flex-grow-1">
                        <i class="fas fa-eye me-1"></i>View Details
                    </a>
                    <?php if (isLoggedIn() && hasRole('user')): ?>
                        <?php if (in_array($job['id'], $user_applications)): ?>
                        <button class="btn btn-secondary flex-grow-1" disabled>
                            <i class="fas fa-check me-1"></i>Applied
                        </button>
                        <?php else: ?>
                        <a href="apply-opportunity.php?id=<?php echo $job['id']; ?>" class="btn btn-primary flex-grow-1">
                            <i class="fas fa-paper-plane me-1"></i>Apply Now
                        </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card-footer bg-light">
                <small class="text-muted">
                    <i class="fas fa-eye me-1"></i><?php echo $job['views_count']; ?> views
                </small>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (!isLoggedIn()): ?>
<div class="alert alert-info mt-4">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Want to apply?</strong> 
    <a href="login.php" class="alert-link">Log in</a> or 
    <a href="signin.php" class="alert-link">create an account</a> to start applying for jobs!
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>

