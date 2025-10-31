<?php
require_once '../includes/auth.php';
requireRole('user');

$user = getCurrentUser();

// Get user's applications with opportunity details
$stmt = $pdo->prepare("
    SELECT a.*, o.title, o.type, o.location, u.org_name, u.name as organizer_name
    FROM applications a
    JOIN opportunities o ON a.opportunity_id = o.id
    LEFT JOIN users u ON o.organizer_id = u.id
    WHERE a.user_id = ?
    ORDER BY a.applied_at DESC
");
$stmt->execute([$user['id']]);
$applications = $stmt->fetchAll();

$page_title = 'My Applications';
include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="user-dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">My Applications</li>
                </ol>
            </nav>
            
            <h1 class="display-6 fw-bold mb-4">
                <i class="fas fa-paper-plane me-3"></i>My Applications
            </h1>
        </div>
    </div>
    
    <?php if (empty($applications)): ?>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-inbox fa-4x text-muted mb-4"></i>
                        <h4>No Applications Yet</h4>
                        <p class="text-muted">You haven't applied to any opportunities yet. Start browsing to find your perfect match!</p>
                        <a href="../opportunities.php" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Browse Opportunities
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Application History (<?php echo count($applications); ?> total)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Opportunity</th>
                                        <th>Organization</th>
                                        <th>Status</th>
                                        <th>Applied Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($applications as $app): ?>
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
                                            <td><?php echo date('M j, Y', strtotime($app['applied_at'])); ?></td>
                                            <td>
                                                <a href="../view-opportunity.php?id=<?php echo $app['opportunity_id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye me-1"></i>View
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
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

include '../includes/footer.php';
?>
