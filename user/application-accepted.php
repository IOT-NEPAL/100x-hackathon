<?php
require_once '../includes/auth.php';
requireRole('user');

$user = getCurrentUser();

// Get application ID from query parameter
$application_id = intval($_GET['id'] ?? 0);

if (!$application_id) {
    header("Location: user-dashboard.php");
    exit;
}

// Get application details
$stmt = $pdo->prepare("
    SELECT a.*, o.title as job_title, o.type as job_type, o.location, 
           u.name as organizer_name, u.org_name, u.email as organizer_email
    FROM applications a
    JOIN opportunities o ON a.opportunity_id = o.id
    LEFT JOIN users u ON o.organizer_id = u.id
    WHERE a.id = ? AND a.user_id = ? AND a.status = 'accepted'
");
$stmt->execute([$application_id, $user['id']]);
$application = $stmt->fetch();

if (!$application) {
    header("Location: user-dashboard.php?error=application_not_found");
    exit;
}

$page_title = 'Application Accepted';
include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Success Banner -->
            <div class="card border-success mb-4">
                <div class="card-header bg-success text-white text-center py-4">
                    <i class="fas fa-check-circle fa-3x mb-3"></i>
                    <h2 class="mb-0">Congratulations! 🎉</h2>
                    <p class="mb-0">Your Application Has Been Accepted</p>
                </div>
                <div class="card-body p-5">
                    <!-- Job Details -->
                    <div class="mb-4">
                        <h4 class="mb-3">
                            <i class="fas fa-briefcase me-2 text-primary"></i>
                            <?php echo escape($application['job_title']); ?>
                        </h4>
                        <div class="row mb-3">
                            <?php if ($application['org_name'] ?? $application['organizer_name']): ?>
                                <div class="col-md-6 mb-2">
                                    <strong>Company:</strong><br>
                                    <?php echo escape($application['org_name'] ?? $application['organizer_name']); ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($application['location']): ?>
                                <div class="col-md-6 mb-2">
                                    <strong>Location:</strong><br>
                                    <i class="fas fa-map-marker-alt me-1"></i><?php echo escape($application['location']); ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($application['job_type']): ?>
                                <div class="col-md-6 mb-2">
                                    <strong>Type:</strong><br>
                                    <span class="badge bg-primary"><?php echo ucfirst(escape($application['job_type'])); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <!-- Acceptance Message -->
                    <div class="card border-success mb-4" style="display: block !important; visibility: visible !important; opacity: 1 !important; position: relative !important; z-index: 1 !important;">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0" style="display: block !important; visibility: visible !important;">
                                <i class="fas fa-envelope-open-text me-2"></i>
                                Next Steps - Interview Process
                            </h5>
                        </div>
                        <div class="card-body" style="display: block !important; visibility: visible !important;">
                            <p class="mb-0" style="display: block !important; visibility: visible !important; white-space: normal !important; word-wrap: break-word !important;">
                                After reviewing your resume and considering your skills, we have decided to proceed with your interview. 
                                We will contact you via email at <strong><?php echo escape($user['email']); ?></strong> to inform you about the date and time for your interview and further details.
                            </p>
                        </div>
                    </div>
                    
                    <!-- Important Information -->
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="fas fa-info-circle me-2 text-info"></i>
                                What Happens Next?
                            </h6>
                            <ul class="mb-0">
                                <li>You will receive an email notification regarding the interview schedule</li>
                                <li>Please check your email regularly and respond promptly</li>
                                <li>Make sure your email address is up to date in your profile</li>
                                <li>Prepare for the interview by reviewing the job requirements</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Contact Information -->
                    <?php if ($application['organizer_email']): ?>
                        <div class="card mb-4">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-headset me-2"></i>
                                    Contact Information
                                </h6>
                                <p class="mb-2">
                                    <strong>Company Email:</strong><br>
                                    <a href="mailto:<?php echo escape($application['organizer_email']); ?>">
                                        <?php echo escape($application['organizer_email']); ?>
                                    </a>
                                </p>
                                <p class="mb-0 small text-muted">
                                    If you have any questions, feel free to reach out to the company directly.
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Action Buttons -->
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="my-applications.php" class="btn btn-primary">
                            <i class="fas fa-list me-2"></i>View My Applications
                        </a>
                        <a href="../view-opportunity.php?id=<?php echo $application['opportunity_id']; ?>" class="btn btn-outline-primary">
                            <i class="fas fa-eye me-2"></i>View Job Details
                        </a>
                        <a href="user-dashboard.php" class="btn btn-outline-secondary">
                            <i class="fas fa-home me-2"></i>Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Application Timeline -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>
                        Application Timeline
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6>Application Submitted</h6>
                                <small class="text-muted"><?php echo date('F j, Y g:i A', strtotime($application['applied_at'])); ?></small>
                            </div>
                        </div>
                        <?php if ($application['reviewed_at']): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6>Application Accepted</h6>
                                    <small class="text-muted"><?php echo date('F j, Y g:i A', strtotime($application['reviewed_at'])); ?></small>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-warning"></div>
                            <div class="timeline-content">
                                <h6>Interview Scheduling</h6>
                                <small class="text-muted">Awaiting email notification</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Ensure acceptance message card is always visible */
.card.border-success.mb-4 {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    position: relative !important;
    z-index: 1 !important;
    overflow: visible !important;
    height: auto !important;
    min-height: auto !important;
}

.card.border-success .card-header,
.card.border-success .card-body,
.card.border-success h5,
.card.border-success p {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    white-space: normal !important;
    word-wrap: break-word !important;
    overflow: visible !important;
}

.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline-item {
    position: relative;
    padding-bottom: 2rem;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-marker {
    position: absolute;
    left: -2rem;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 3px solid #fff;
    box-shadow: 0 0 0 2px currentColor;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -1.7rem;
    top: 12px;
    width: 2px;
    height: calc(100% - 12px);
    background: #dee2e6;
}

.timeline-content h6 {
    margin-bottom: 0.25rem;
    font-weight: 600;
}
</style>

<script>
// Prevent any JavaScript from hiding the interview process message
document.addEventListener('DOMContentLoaded', function() {
    const messageCard = document.querySelector('.card.border-success.mb-4');
    if (messageCard) {
        // Force visibility immediately
        messageCard.style.display = 'block';
        messageCard.style.visibility = 'visible';
        messageCard.style.opacity = '1';
        messageCard.style.position = 'relative';
        messageCard.style.zIndex = '1';
        
        // Remove any fade/hide classes
        messageCard.classList.remove('fade', 'd-none', 'alert-dismissible');
        
        // Remove any close buttons
        const closeButtons = messageCard.querySelectorAll('.btn-close, [data-bs-dismiss]');
        closeButtons.forEach(btn => btn.remove());
        
        // Monitor and prevent hiding
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && 
                    (mutation.attributeName === 'style' || mutation.attributeName === 'class')) {
                    // Check if element is being hidden
                    const style = window.getComputedStyle(messageCard);
                    if (style.display === 'none' || 
                        style.visibility === 'hidden' || 
                        style.opacity === '0' ||
                        messageCard.classList.contains('d-none') ||
                        messageCard.classList.contains('fade')) {
                        // Immediately restore visibility
                        messageCard.style.display = 'block';
                        messageCard.style.visibility = 'visible';
                        messageCard.style.opacity = '1';
                        messageCard.classList.remove('d-none', 'fade', 'alert-dismissible');
                    }
                }
            });
        });
        
        observer.observe(messageCard, {
            attributes: true,
            attributeFilter: ['style', 'class'],
            subtree: true
        });
        
        // Also check periodically
        setInterval(function() {
            const style = window.getComputedStyle(messageCard);
            if (style.display === 'none' || 
                style.visibility === 'hidden' || 
                parseFloat(style.opacity) < 1 ||
                messageCard.classList.contains('d-none')) {
                messageCard.style.display = 'block';
                messageCard.style.visibility = 'visible';
                messageCard.style.opacity = '1';
                messageCard.classList.remove('d-none', 'fade', 'alert-dismissible');
            }
        }, 100); // Check every 100ms
    }
});
</script>

<?php include '../includes/footer.php'; ?>

