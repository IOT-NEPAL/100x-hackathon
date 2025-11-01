<?php
require_once 'db_config.php';
requireRole('user');

$page_title = 'Apply for Job';

// Ensure user_id is available
global $user_id;
if (!isset($user_id)) {
    $user_id = $_SESSION['user_id'];
}

$success = '';
$error = '';

$opportunity_id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);

if (!$opportunity_id) {
    header('Location: opportunities.php');
    exit();
}

// Get opportunity details
$stmt = $pdo->prepare("
    SELECT o.*, u.org_name, u.name as organizer_name
    FROM opportunities o
    JOIN users u ON o.organizer_id = u.id
    WHERE o.id = ? AND o.is_active = 1
");
$stmt->execute([$opportunity_id]);
$job = $stmt->fetch();

if (!$job) {
    header('Location: opportunities.php');
    exit();
}

// Check if already applied
$stmt = $pdo->prepare("SELECT id FROM applications WHERE user_id = ? AND opportunity_id = ?");
$stmt->execute([$user_id, $opportunity_id]);
if ($stmt->fetch()) {
    $error = 'You have already applied for this position.';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request';
    } else {
        $cover_letter = trim($_POST['cover_letter']);
        
        if (empty($cover_letter)) {
            $error = 'Cover letter is required';
        } else {
            try {
                // Insert application
                $stmt = $pdo->prepare("
                    INSERT INTO applications (user_id, opportunity_id, cover_letter, status)
                    VALUES (?, ?, ?, 'applied')
                ");
                $stmt->execute([$user_id, $opportunity_id, $cover_letter]);
                
                $success = 'Application submitted successfully!';
            } catch (PDOException $e) {
                $error = 'Failed to submit application. Please try again.';
            }
        }
    }
}

include 'includes/header.php'; // Requires login
?>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="opportunities.php">Jobs</a></li>
                <li class="breadcrumb-item"><a href="view-opportunity.php?id=<?php echo $opportunity_id; ?>"><?php echo escape($job['title']); ?></a></li>
                <li class="breadcrumb-item active">Apply</li>
            </ol>
        </nav>

        <div class="card border-3" style="border-color: var(--primary-dark) !important;">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="fas fa-paper-plane me-2"></i>Apply for Position
                </h4>
            </div>
            <div class="card-body">
                <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i><?php echo escape($success); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <div class="text-center py-4">
                    <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                    <h5>Application Submitted Successfully!</h5>
                    <p class="text-muted">The employer will review your application and may contact you soon.</p>
                    <div class="d-flex gap-2 justify-content-center mt-4">
                        <a href="user/user-dashboard.php" class="btn btn-primary">
                            <i class="fas fa-home me-2"></i>Go to Dashboard
                        </a>
                        <a href="opportunities.php" class="btn btn-outline-secondary">
                            <i class="fas fa-search me-2"></i>Browse More Jobs
                        </a>
                    </div>
                </div>
                <?php else: ?>

                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo escape($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                
                <?php if (strpos($error, 'already applied') !== false): ?>
                <div class="text-center py-4">
                    <a href="user/user-dashboard.php" class="btn btn-primary">
                        <i class="fas fa-home me-2"></i>Go to Dashboard
                    </a>
                    <a href="opportunities.php" class="btn btn-outline-secondary">
                        <i class="fas fa-search me-2"></i>Browse More Jobs
                    </a>
                </div>
                <?php endif; ?>
                <?php endif; ?>

                <?php if (empty($error) || strpos($error, 'already applied') === false): ?>
                <div class="alert alert-info mb-4">
                    <h6><i class="fas fa-info-circle me-2"></i>Job Details</h6>
                    <p class="mb-1"><strong>Position:</strong> <?php echo escape($job['title']); ?></p>
                    <p class="mb-1"><strong>Company:</strong> <?php echo escape($job['org_name'] ?? $job['organizer_name']); ?></p>
                    <p class="mb-0"><strong>Type:</strong> <?php echo ucfirst($job['type']); ?></p>
                </div>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                    <div class="mb-4">
                        <label for="cover_letter" class="form-label fw-bold">
                            <i class="fas fa-file-alt me-2"></i>Cover Letter / Message
                        </label>
                        <textarea 
                            class="form-control" 
                            id="cover_letter" 
                            name="cover_letter" 
                            rows="10" 
                            required 
                            placeholder="Introduce yourself and explain why you're a great fit for this position..."
                        ></textarea>
                        <div class="form-text">
                            <i class="fas fa-lightbulb me-1"></i>
                            Tip: Mention relevant skills and experience. Be professional and enthusiastic!
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>Before you apply:</strong> Make sure you've added your skills in your profile. 
                        <a href="user/my-skills.php" class="alert-link">Manage Skills</a>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane me-2"></i>Submit Application
                        </button>
                        <a href="view-opportunity.php?id=<?php echo $opportunity_id; ?>" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                </form>
                <?php endif; ?>

                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

