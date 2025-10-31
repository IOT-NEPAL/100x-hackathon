<?php
require_once 'includes/auth.php';
requireRole('user');

$user = getCurrentUser();
$opportunity_id = intval($_GET['id'] ?? 0);

if (!$opportunity_id) {
    header("Location: opportunities.php");
    exit;
}

// Get opportunity details
$stmt = $pdo->prepare("
    SELECT o.*, u.org_name, u.name as organizer_name 
    FROM opportunities o
    LEFT JOIN users u ON o.organizer_id = u.id
    WHERE o.id = ? AND o.is_active = 1
");
$stmt->execute([$opportunity_id]);
$opportunity = $stmt->fetch();

if (!$opportunity) {
    header("Location: opportunities.php?error=not_found");
    exit;
}

// Check if user has already applied
$stmt = $pdo->prepare("SELECT id FROM applications WHERE user_id = ? AND opportunity_id = ?");
$stmt->execute([$user['id'], $opportunity_id]);
if ($stmt->fetch()) {
    header("Location: view-opportunity.php?id=$opportunity_id&error=already_applied");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cover_letter = trim($_POST['cover_letter'] ?? '');
    $additional_info = trim($_POST['additional_info'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verifyCSRFToken($csrf_token)) {
        $error = 'Invalid request. Please try again.';
    } elseif (empty($cover_letter)) {
        $error = 'Cover letter is required.';
    } else {
        // Handle resume upload
        $resume_path = null;
        
        if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/resumes/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_info = pathinfo($_FILES['resume']['name']);
            $extension = strtolower($file_info['extension']);
            $allowed_extensions = ['pdf', 'doc', 'docx'];
            
            if (!in_array($extension, $allowed_extensions)) {
                $error = 'Please upload a valid resume file (PDF, DOC, DOCX).';
            } elseif ($_FILES['resume']['size'] > 10 * 1024 * 1024) {
                $error = 'Resume file must be less than 10MB.';
            } else {
                $filename = 'resume_' . $user['id'] . '_' . $opportunity_id . '_' . time() . '.' . $extension;
                $upload_path = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['resume']['tmp_name'], $upload_path)) {
                    $resume_path = $filename;
                } else {
                    $error = 'Failed to upload resume.';
                }
            }
        }
        
        if (!$error) {
            try {
                // Submit application
                $stmt = $pdo->prepare("
                    INSERT INTO applications (user_id, opportunity_id, cover_letter, additional_info, resume_path)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$user['id'], $opportunity_id, $cover_letter, $additional_info, $resume_path]);
                
                // Log activity
                $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, 'application_submitted', 'Applied to opportunity: " . $opportunity['title'] . "', ?)");
                $stmt->execute([$user['id'], $_SERVER['REMOTE_ADDR'] ?? '']);
                
                // Redirect to success page
                header("Location: view-opportunity.php?id=$opportunity_id&success=applied");
                exit;
                
            } catch (PDOException $e) {
                $error = 'Failed to submit application. Please try again.';
                error_log("Application submission error: " . $e->getMessage());
            }
        }
    }
}

$page_title = 'Apply - ' . $opportunity['title'];
include 'includes/header.php';
?>

<div class="container mt-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="opportunities.php">Opportunities</a></li>
            <li class="breadcrumb-item"><a href="view-opportunity.php?id=<?php echo $opportunity['id']; ?>"><?php echo escape($opportunity['title']); ?></a></li>
            <li class="breadcrumb-item active" aria-current="page">Apply</li>
        </ol>
    </nav>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header">
                    <h3 class="mb-0">
                        <i class="fas fa-paper-plane me-2"></i>Apply for Opportunity
                    </h3>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo escape($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Opportunity Summary -->
                    <div class="bg-light p-3 rounded mb-4">
                        <h5 class="mb-2"><?php echo escape($opportunity['title']); ?></h5>
                        <p class="text-muted mb-1">
                            <i class="fas fa-building me-2"></i>
                            <?php echo escape($opportunity['org_name'] ?? $opportunity['organizer_name']); ?>
                        </p>
                        <span class="badge bg-<?php echo getTypeColor($opportunity['type']); ?>">
                            <?php echo ucfirst(escape($opportunity['type'])); ?>
                        </span>
                        <?php if ($opportunity['location']): ?>
                            <span class="badge bg-secondary ms-1">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                <?php echo escape($opportunity['location']); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <form method="POST" action="" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <!-- Pre-filled User Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Your Name</label>
                                <input type="text" class="form-control" value="<?php echo escape($user['name']); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control" value="<?php echo escape($user['email']); ?>" readonly>
                            </div>
                        </div>
                        
                        <?php if ($user['phone']): ?>
                            <div class="mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" value="<?php echo escape($user['phone']); ?>" readonly>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Cover Letter -->
                        <div class="mb-3">
                            <label for="cover_letter" class="form-label">Cover Letter *</label>
                            <textarea class="form-control" id="cover_letter" name="cover_letter" rows="8" required
                                      placeholder="Explain why you're interested in this opportunity and how your skills and experience make you a good fit..."><?php echo escape($_POST['cover_letter'] ?? ''); ?></textarea>
                            <div class="form-text">
                                <i class="fas fa-lightbulb me-1"></i>
                                Tip: Mention specific skills from your profile that relate to this opportunity.
                            </div>
                            <div class="invalid-feedback">Please write a cover letter explaining your interest.</div>
                        </div>
                        
                        <!-- Resume Upload -->
                        <div class="mb-3">
                            <label for="resume" class="form-label">Resume/CV</label>
                            <input type="file" class="form-control" id="resume" name="resume" 
                                   accept=".pdf,.doc,.docx">
                            <div class="form-text">
                                Optional. Upload your resume in PDF, DOC, or DOCX format (max 10MB).
                            </div>
                        </div>
                        
                        <!-- Additional Information -->
                        <div class="mb-3">
                            <label for="additional_info" class="form-label">Additional Information</label>
                            <textarea class="form-control" id="additional_info" name="additional_info" rows="4"
                                      placeholder="Any additional information you'd like to share, such as availability, accommodations needed, or questions..."><?php echo escape($_POST['additional_info'] ?? ''); ?></textarea>
                        </div>
                        
                        <!-- Skills & Disability Info (if available) -->
                        <?php if ($user['skills'] || $user['disability_text']): ?>
                            <div class="mb-4">
                                <h5>Information from Your Profile</h5>
                                <?php if ($user['skills']): ?>
                                    <div class="mb-2">
                                        <strong>Skills & Experience:</strong>
                                        <p class="text-muted small"><?php echo nl2br(escape($user['skills'])); ?></p>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($user['disability_text']): ?>
                                    <div class="mb-2">
                                        <strong>Accessibility Information:</strong>
                                        <p class="text-muted small"><?php echo nl2br(escape($user['disability_text'])); ?></p>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="text-center">
                                    <a href="profile.php" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit me-1"></i>Update Profile
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Consent and Terms -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="consent" required>
                                <label class="form-check-label" for="consent">
                                    I consent to sharing my application information with the organization 
                                    posting this opportunity for evaluation purposes. *
                                </label>
                                <div class="invalid-feedback">You must provide consent to submit your application.</div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="view-opportunity.php?id=<?php echo $opportunity['id']; ?>" 
                               class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Opportunity
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>Submit Application
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Application Tips -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Application Tips</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <strong>Be specific:</strong> Mention relevant skills and experiences
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <strong>Show enthusiasm:</strong> Explain why you're interested
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <strong>Address requirements:</strong> Reference the job posting
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <strong>Be honest:</strong> Include relevant accessibility needs
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <strong>Proofread:</strong> Check for spelling and grammar
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Contact Support -->
            <div class="card mt-4">
                <div class="card-body">
                    <h6><i class="fas fa-question-circle me-2"></i>Need Help?</h6>
                    <p class="small text-muted">
                        Having trouble with your application? Our support team is here to help.
                    </p>
                    <div class="d-grid">
                        <a href="mailto:support@inclusify.com?subject=Application Help" 
                           class="btn btn-sm btn-outline-info">
                            <i class="fas fa-envelope me-1"></i>Contact Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
function getTypeColor($type) {
    $colors = [
        'job' => 'primary',
        'scholarship' => 'success',
        'training' => 'info',
        'event' => 'warning',
        'resource' => 'secondary'
    ];
    return $colors[$type] ?? 'secondary';
}

include 'includes/footer.php';
?>
