<?php
require_once 'db_config.php';

$page_title = 'Job Details';

$opportunity_id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);

if (!$opportunity_id) {
    header('Location: opportunities.php');
    exit();
}

// Get opportunity details
$stmt = $pdo->prepare("
    SELECT o.*, u.org_name, u.name as organizer_name, u.email as organizer_email, u.phone
    FROM opportunities o
    JOIN users u ON o.organizer_id = u.id
    WHERE o.id = ?
");
$stmt->execute([$opportunity_id]);
$job = $stmt->fetch();

if (!$job) {
    header('Location: opportunities.php');
    exit();
}

// Increment view count
$stmt = $pdo->prepare("UPDATE opportunities SET views_count = views_count + 1 WHERE id = ?");
$stmt->execute([$opportunity_id]);

// Check if user has applied
$has_applied = false;
$application_status = '';
if (isLoggedIn() && hasRole('user')) {
    $stmt = $pdo->prepare("SELECT status FROM applications WHERE user_id = ? AND opportunity_id = ?");
    $stmt->execute([$_SESSION['user_id'], $opportunity_id]);
    $app = $stmt->fetch();
    if ($app) {
        $has_applied = true;
        $application_status = $app['status'];
    }
}

// Get application count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE opportunity_id = ?");
$stmt->execute([$opportunity_id]);
$application_count = $stmt->fetchColumn();

include 'includes/public-header.php';
?>

<div class="row">
    <div class="col-md-8">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="opportunities.php">Jobs</a></li>
                <li class="breadcrumb-item active"><?php echo escape($job['title']); ?></li>
            </ol>
        </nav>

        <div class="card border-3 mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="badge bg-<?php echo $job['type'] === 'employment' ? 'primary' : 'info'; ?> me-2">
                            <?php echo ucfirst($job['type']); ?>
                        </span>
                        <?php if (!$job['is_active']): ?>
                        <span class="badge bg-secondary">Inactive</span>
                        <?php endif; ?>
                        <?php if ($has_applied): ?>
                        <span class="badge bg-success">You Applied</span>
                        <?php endif; ?>
                    </div>
                    <small class="text-muted">
                        Posted <?php echo formatDate($job['date_posted']); ?>
                    </small>
                </div>

                <h2 class="mb-3"><?php echo escape($job['title']); ?></h2>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <p class="mb-2">
                            <i class="fas fa-building me-2 text-muted"></i>
                            <strong><?php echo escape($job['org_name'] ?? $job['organizer_name']); ?></strong>
                        </p>
                    </div>
                    <?php if ($job['location']): ?>
                    <div class="col-md-6">
                        <p class="mb-2">
                            <i class="fas fa-map-marker-alt me-2 text-muted"></i>
                            <?php echo escape($job['location']); ?>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>

                <hr>

                <h5 class="mb-3"><i class="fas fa-info-circle me-2"></i>Job Description</h5>
                <div class="mb-4" style="white-space: pre-line;">
                    <?php echo nl2br(escape($job['description'])); ?>
                </div>

                <?php if ($job['requirements']): ?>
                <hr>
                <h5 class="mb-3"><i class="fas fa-check-circle me-2"></i>Requirements</h5>
                <div class="mb-4" style="white-space: pre-line;">
                    <?php echo nl2br(escape($job['requirements'])); ?>
                </div>
                <?php endif; ?>

                <hr>

                <div class="d-flex gap-2 mt-4">
                    <?php if (isLoggedIn() && hasRole('user')): ?>
                        <?php if ($has_applied): ?>
                        <button class="btn btn-secondary btn-lg" disabled>
                            <i class="fas fa-check me-2"></i>Already Applied
                        </button>
                        <a href="user/user-dashboard.php" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-home me-2"></i>Go to Dashboard
                        </a>
                        <?php elseif ($job['is_active']): ?>
                        <a href="apply-opportunity.php?id=<?php echo $job['id']; ?>" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane me-2"></i>Apply for This Job
                        </a>
                        <a href="opportunities.php" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-arrow-left me-2"></i>Back to Jobs
                        </a>
                        <?php else: ?>
                        <button class="btn btn-secondary btn-lg" disabled>
                            <i class="fas fa-times me-2"></i>Job Inactive
                        </button>
                        <?php endif; ?>
                    <?php elseif (isLoggedIn() && hasRole('organizer')): ?>
                        <a href="organizer/organizer-dashboard.php" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-home me-2"></i>Go to Dashboard
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>Login to Apply
                        </a>
                        <a href="signin.php" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-user-plus me-2"></i>Create Account
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Quick Stats -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-chart-bar me-2"></i>Job Statistics
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Views:</span>
                        <strong><?php echo $job['views_count']; ?></strong>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Applications:</span>
                        <strong><?php echo $application_count; ?></strong>
                    </div>
                </div>
                <div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Type:</span>
                        <strong><?php echo ucfirst($job['type']); ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Company Info -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-building me-2"></i>About the Company
            </div>
            <div class="card-body">
                <h6 class="mb-2"><?php echo escape($job['org_name'] ?? $job['organizer_name']); ?></h6>
                <?php if ($job['organizer_email']): ?>
                <p class="small mb-1">
                    <i class="fas fa-envelope me-2"></i><?php echo escape($job['organizer_email']); ?>
                </p>
                <?php endif; ?>
                <?php if ($job['phone']): ?>
                <p class="small mb-0">
                    <i class="fas fa-phone me-2"></i><?php echo escape($job['phone']); ?>
                </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Share -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-share-alt me-2"></i>Share This Job
            </div>
            <div class="card-body">
                <p class="small text-muted mb-2">Share this opportunity with friends:</p>
                <div class="d-grid gap-2">
                    <button class="btn btn-sm btn-outline-primary" onclick="copyToClipboard()">
                        <i class="fas fa-copy me-2"></i>Copy Link
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard() {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(() => {
        alert('Job link copied to clipboard!');
    });
}
</script>

<?php include 'includes/footer.php'; ?>

