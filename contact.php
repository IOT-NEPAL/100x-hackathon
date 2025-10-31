<?php
$page_title = 'Contact Us';
include 'includes/header.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // In a real application, you would send an email here
        // For demo purposes, we'll just show a success message
        $success = 'Thank you for your message! We\'ll get back to you soon.';
    }
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h1 class="display-5 fw-bold mb-4">
                <i class="fas fa-envelope me-3"></i>Contact Us
            </h1>
            <p class="lead text-muted">We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo escape($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo escape($success); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Your Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo escape($_POST['name'] ?? ''); ?>" required>
                                    <div class="invalid-feedback">Please provide your name.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo escape($_POST['email'] ?? ''); ?>" required>
                                    <div class="invalid-feedback">Please provide a valid email address.</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject *</label>
                            <select class="form-select" id="subject" name="subject" required>
                                <option value="">Select a subject</option>
                                <option value="General Inquiry" <?php echo ($_POST['subject'] ?? '') === 'General Inquiry' ? 'selected' : ''; ?>>General Inquiry</option>
                                <option value="Technical Support" <?php echo ($_POST['subject'] ?? '') === 'Technical Support' ? 'selected' : ''; ?>>Technical Support</option>
                                <option value="Accessibility Issue" <?php echo ($_POST['subject'] ?? '') === 'Accessibility Issue' ? 'selected' : ''; ?>>Accessibility Issue</option>
                                <option value="Partnership" <?php echo ($_POST['subject'] ?? '') === 'Partnership' ? 'selected' : ''; ?>>Partnership Opportunity</option>
                                <option value="Feedback" <?php echo ($_POST['subject'] ?? '') === 'Feedback' ? 'selected' : ''; ?>>Feedback</option>
                                <option value="Other" <?php echo ($_POST['subject'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                            <div class="invalid-feedback">Please select a subject.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="message" class="form-label">Message *</label>
                            <textarea class="form-control" id="message" name="message" rows="6" required
                                      placeholder="Please provide details about your inquiry..."><?php echo escape($_POST['message'] ?? ''); ?></textarea>
                            <div class="invalid-feedback">Please provide your message.</div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>Send Message
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5><i class="fas fa-info-circle me-2"></i>Get in Touch</h5>
                    <p class="text-muted">We're here to help and answer any questions you might have.</p>
                    
                    <div class="mb-3">
                        <h6><i class="fas fa-envelope me-2"></i>Email</h6>
                        <p class="text-muted">support@inclusify.com</p>
                    </div>
                    
                    <div class="mb-3">
                        <h6><i class="fas fa-clock me-2"></i>Response Time</h6>
                        <p class="text-muted">We typically respond within 24 hours during business days.</p>
                    </div>
                    
                    <div class="mb-3">
                        <h6><i class="fas fa-universal-access me-2"></i>Accessibility</h6>
                        <p class="text-muted">If you're experiencing accessibility issues, please let us know immediately so we can assist you.</p>
                    </div>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-body">
                    <h5><i class="fas fa-question-circle me-2"></i>Frequently Asked</h5>
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h6 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    Is Inclusify free to use?
                                </button>
                            </h6>
                            <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Yes! Inclusify is completely free for job seekers. Organizations pay a small fee to post opportunities.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h6 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    How do I use accessibility features?
                                </button>
                            </h6>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Click on your profile menu (or Accessibility menu for guests) to access high contrast mode, font size controls, and text-to-speech features.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
