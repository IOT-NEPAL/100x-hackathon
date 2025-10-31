<?php
require_once 'includes/auth.php';

// Get filter parameters
$type_filter = $_GET['type'] ?? '';
$location_filter = $_GET['location'] ?? '';
$organizer_filter = $_GET['organizer'] ?? '';
$search_query = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Build SQL query with filters
$where_conditions = ['o.is_active = 1'];
$params = [];

if (!empty($type_filter)) {
    $where_conditions[] = 'o.type = ?';
    $params[] = $type_filter;
}

if (!empty($location_filter)) {
    $where_conditions[] = 'o.location LIKE ?';
    $params[] = '%' . $location_filter . '%';
}

if (!empty($organizer_filter)) {
    $where_conditions[] = 'o.organizer_id = ?';
    $params[] = $organizer_filter;
}

if (!empty($search_query)) {
    $where_conditions[] = '(o.title LIKE ? OR o.description LIKE ? OR o.requirements LIKE ?)';
    $params[] = '%' . $search_query . '%';
    $params[] = '%' . $search_query . '%';
    $params[] = '%' . $search_query . '%';
}

$where_clause = implode(' AND ', $where_conditions);

// Get total count for pagination
$count_sql = "SELECT COUNT(*) as total FROM opportunities o LEFT JOIN users u ON o.organizer_id = u.id WHERE $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_opportunities = $count_stmt->fetch()['total'];
$total_pages = ceil($total_opportunities / $per_page);

// Get opportunities with pagination
$sql = "
    SELECT o.*, u.name as organizer_name, u.org_name 
    FROM opportunities o 
    LEFT JOIN users u ON o.organizer_id = u.id 
    WHERE $where_clause 
    ORDER BY o.date_posted DESC 
    LIMIT $per_page OFFSET $offset
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$opportunities = $stmt->fetchAll();

// Get available types and locations for filters
$types_stmt = $pdo->query("SELECT DISTINCT type FROM opportunities WHERE is_active = 1 ORDER BY type");
$available_types = $types_stmt->fetchAll(PDO::FETCH_COLUMN);

$locations_stmt = $pdo->query("SELECT DISTINCT location FROM opportunities WHERE is_active = 1 AND location IS NOT NULL AND location != '' ORDER BY location");
$available_locations = $locations_stmt->fetchAll(PDO::FETCH_COLUMN);

// Get organizer info if filtering by organizer
$organizer_info = null;
if (!empty($organizer_filter)) {
    $org_stmt = $pdo->prepare("SELECT name, org_name FROM users WHERE id = ? AND role = 'organizer'");
    $org_stmt->execute([$organizer_filter]);
    $organizer_info = $org_stmt->fetch();
}

$page_title = 'Jobs';
include 'includes/header.php';
?>

<div class="container mt-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-5 fw-bold">
                <i class="fas fa-briefcase me-3"></i>Jobs
            </h1>
            <?php if ($organizer_info): ?>
                <div class="alert alert-info mb-3">
                    <i class="fas fa-filter me-2"></i>
                    Showing jobs from: <strong><?php echo escape($organizer_info['name']); ?></strong>
                    <?php if ($organizer_info['org_name']): ?>
                        (<?php echo escape($organizer_info['org_name']); ?>)
                    <?php endif; ?>
                    <a href="opportunities.php" class="btn btn-sm btn-outline-primary ms-3">
                        <i class="fas fa-times me-1"></i>Show All Jobs
                    </a>
                </div>
            <?php else: ?>
                <p class="lead text-muted">Discover full-time jobs, part-time jobs, and internships</p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Filters and Search -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-3">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?php echo escape($search_query); ?>" 
                                   placeholder="Keywords, skills, company...">
                        </div>
                        
                        <div class="col-md-3">
                            <label for="type" class="form-label">Type</label>
                            <select class="form-select" id="type" name="type">
                                <option value="">All Types</option>
                                <?php foreach ($available_types as $type): ?>
                                    <option value="<?php echo escape($type); ?>" 
                                            <?php echo $type_filter === $type ? 'selected' : ''; ?>>
                                        <?php echo ucfirst(escape($type)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="location" class="form-label">Location</label>
                            <select class="form-select" id="location" name="location">
                                <option value="">All Locations</option>
                                <?php foreach ($available_locations as $location): ?>
                                    <option value="<?php echo escape($location); ?>" 
                                            <?php echo $location_filter === $location ? 'selected' : ''; ?>>
                                        <?php echo escape($location); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search me-1"></i>Search
                            </button>
                            <a href="opportunities.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Results Summary -->
    <div class="row mb-3">
        <div class="col-12">
            <p class="text-muted">
                Showing <?php echo number_format($total_opportunities); ?> jobs
                <?php if ($search_query || $type_filter || $location_filter): ?>
                    for your search
                <?php endif; ?>
            </p>
        </div>
    </div>
    
    <!-- Opportunities Grid -->
    <div class="row g-4 mb-5">
        <?php if (empty($opportunities)): ?>
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h4>No jobs found</h4>
                    <p class="text-muted">Try adjusting your search criteria or check back later for new jobs.</p>
                    <a href="opportunities.php" class="btn btn-primary">
                        <i class="fas fa-refresh me-2"></i>View All Jobs
                    </a>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($opportunities as $opportunity): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm opportunity-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-<?php echo getTypeColor($opportunity['type']); ?> text-white">
                                    <?php echo ucfirst(escape($opportunity['type'])); ?>
                                </span>
                            </div>
                            
                            <h5 class="card-title mb-2">
                                <a href="view-opportunity.php?id=<?php echo $opportunity['id']; ?>" 
                                   class="text-decoration-none">
                                    <?php echo escape($opportunity['title']); ?>
                                </a>
                            </h5>
                            
                            <p class="text-muted small mb-2">
                                <i class="fas fa-building me-1"></i>
                                <?php echo escape($opportunity['org_name'] ?? $opportunity['organizer_name']); ?>
                            </p>
                            
                            <?php if ($opportunity['location']): ?>
                                <p class="text-muted small mb-2">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    <?php echo escape($opportunity['location']); ?>
                                </p>
                            <?php endif; ?>
                            
                            <p class="card-text">
                                <?php echo escape(substr($opportunity['description'], 0, 150)); ?>
                                <?php if (strlen($opportunity['description']) > 150): ?>...<?php endif; ?>
                            </p>
                            
                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    <?php echo date('M j, Y', strtotime($opportunity['date_posted'])); ?>
                                </small>
                                
                                <div>
                                    <a href="view-opportunity.php?id=<?php echo $opportunity['id']; ?>" 
                                       class="btn btn-outline-primary btn-sm">
                                        View Details
                                    </a>
                                    <?php if (isLoggedIn() && hasRole('user')): ?>
                                        <a href="apply.php?id=<?php echo $opportunity['id']; ?>" 
                                           class="btn btn-primary btn-sm">
                                            Apply
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <nav aria-label="Jobs pagination">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    </li>
                <?php endif; ?>
                
                <?php
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);
                
                if ($start_page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>">1</a>
                    </li>
                    <?php if ($start_page > 2): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>
                
                <?php if ($end_page < $total_pages): ?>
                    <?php if ($end_page < $total_pages - 1): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $total_pages])); ?>">
                            <?php echo $total_pages; ?>
                        </a>
                    </li>
                <?php endif; ?>
                
                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<?php
function getTypeColor($type) {
    $colors = [
        'employment' => 'primary',
        'internship' => 'info'
    ];
    return $colors[$type] ?? 'secondary';
}
?>

<style>
/* Fix footer spacing and ensure proper display */
.container.mt-4 {
    margin-bottom: 2rem;
    padding-bottom: 2rem;
}

footer.footer {
    position: relative;
    clear: both;
    margin-top: 3rem;
    z-index: 1;
}

/* Ensure no overlapping elements between content and footer */
body {
    position: relative;
}
</style>

<?php include 'includes/footer.php'; ?>
