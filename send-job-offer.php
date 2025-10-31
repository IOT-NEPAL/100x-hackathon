<?php
require_once 'includes/auth.php';

// Only organizers can send job offers
requireRole('organizer');

$user = getCurrentUser();

// Handle POST request (form submission)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    $opportunity_id = intval($_POST['opportunity_id'] ?? 0);
    $student_id = intval($_POST['student_id'] ?? 0);
    $message = trim($_POST['message'] ?? '');
    
    // Validate CSRF token
    if (!verifyCSRFToken($csrf_token)) {
        header("Location: opportunities.php?error=invalid_request");
        exit;
    }
    
    // Validate inputs
    if (!$opportunity_id || !$student_id || empty($message)) {
        header("Location: send-job-offer.php?opportunity_id={$opportunity_id}&student_id={$student_id}&error=missing_fields");
        exit;
    }
    
    // Verify the opportunity belongs to the current organizer
    $stmt = $pdo->prepare("SELECT id, organizer_id, title FROM opportunities WHERE id = ?");
    $stmt->execute([$opportunity_id]);
    $opportunity = $stmt->fetch();
    
    if (!$opportunity || $opportunity['organizer_id'] != $user['id']) {
        header("Location: opportunities.php?error=unauthorized");
        exit;
    }
    
    // Verify the student exists and is a user
    $stmt = $pdo->prepare("SELECT id, name, email, role FROM users WHERE id = ? AND role = 'user'");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch();
    
    if (!$student) {
        header("Location: opportunities.php?error=student_not_found");
        exit;
    }
    
    // Check if table exists, if not create it
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS job_offers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                opportunity_id INT NOT NULL,
                organizer_id INT NOT NULL,
                student_id INT NOT NULL,
                message TEXT NOT NULL,
                status ENUM('pending', 'accepted', 'declined', 'expired') DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                read_at TIMESTAMP NULL,
                FOREIGN KEY (opportunity_id) REFERENCES opportunities(id) ON DELETE CASCADE,
                FOREIGN KEY (organizer_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_opportunity (opportunity_id),
                INDEX idx_student (student_id),
                INDEX idx_organizer (organizer_id),
                INDEX idx_status (status),
                INDEX idx_created (created_at)
            )
        ");
    } catch (PDOException $e) {
        // Table might already exist, continue
    }
    
    // Insert job offer
    try {
        $stmt = $pdo->prepare("
            INSERT INTO job_offers (opportunity_id, organizer_id, student_id, message, status)
            VALUES (?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([$opportunity_id, $user['id'], $student_id, $message]);
        
        // Log activity
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, 'job_offer_sent', ?, ?)");
        $stmt->execute([
            $user['id'],
            "Sent job offer for '{$opportunity['title']}' to {$student['name']}",
            $_SERVER['REMOTE_ADDR'] ?? ''
        ]);
        
        header("Location: view-opportunity.php?id={$opportunity_id}&success=offer_sent");
        exit;
    } catch (PDOException $e) {
        error_log("Job offer error: " . $e->getMessage());
        header("Location: send-job-offer.php?opportunity_id={$opportunity_id}&student_id={$student_id}&error=offer_failed");
        exit;
    }
}

// Handle GET request (show form)
$opportunity_id = intval($_GET['opportunity_id'] ?? 0);
$student_id = intval($_GET['student_id'] ?? 0);
$error_msg = $_GET['error'] ?? '';

if (!$opportunity_id || !$student_id) {
    header("Location: opportunities.php");
    exit;
}

// Verify the opportunity belongs to the current organizer
$stmt = $pdo->prepare("SELECT id, organizer_id, title FROM opportunities WHERE id = ?");
$stmt->execute([$opportunity_id]);
$opportunity = $stmt->fetch();

if (!$opportunity || $opportunity['organizer_id'] != $user['id']) {
    header("Location: opportunities.php?error=unauthorized");
    exit;
}

// Get student info
$stmt = $pdo->prepare("SELECT id, name, email, role FROM users WHERE id = ? AND role = 'user'");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

if (!$student) {
    header("Location: opportunities.php?error=student_not_found");
    exit;
}

$page_title = 'Send Job Offer';
include 'includes/header.php';
?>

<div class="container mt-4">
    <!-- Breadcrumb -->
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="opportunities.php">Jobs</a></li>
            <li class="breadcrumb-item"><a href="view-opportunity.php?id=<?php echo $opportunity['id']; ?>"><?php echo escape($opportunity['title']); ?></a></li>
            <li class="breadcrumb-item active">Send Offer</li>
        </ol>
    </nav>
    
    <?php if ($error_msg === 'missing_fields'): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>Please fill in the message field.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($error_msg === 'offer_failed'): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>Failed to send job offer. Please try again.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header" style="background: #1a1a1a; color: white; border-bottom: 3px solid #ffff00; font-weight: 700;">
                    <h5 class="mb-0">
                        <i class="fas fa-paper-plane me-2"></i>Send Job Offer to <?php echo escape($student['name']); ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h6>Job:</h6>
                        <p class="mb-2"><strong><?php echo escape($opportunity['title']); ?></strong></p>
                    </div>
                    
                    <div class="mb-4">
                        <h6>Candidate:</h6>
                        <p class="mb-2"><strong><?php echo escape($student['name']); ?></strong></p>
                        <p class="mb-0 text-muted"><?php echo escape($student['email']); ?></p>
                    </div>
                    
                    <form method="POST" action="send-job-offer.php">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="opportunity_id" value="<?php echo $opportunity['id']; ?>">
                        <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                        
                        <div class="mb-4">
                            <label for="message" class="form-label fw-bold">Message *</label>
                            <textarea class="form-control" 
                                      id="message" 
                                      name="message" 
                                      rows="8" 
                                      placeholder="Type your job offer message here..." 
                                      required><?php echo isset($_POST['message']) ? escape($_POST['message']) : ''; ?></textarea>
                            <div class="form-text">This message will be sent to <?php echo escape($student['name']); ?> as a notification.</div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>Send Offer
                            </button>
                            <a href="view-opportunity.php?id=<?php echo $opportunity['id']; ?>" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
