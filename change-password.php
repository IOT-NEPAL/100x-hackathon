<?php
require_once 'includes/auth.php';
requireLogin();

$user = getCurrentUser();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verifyCSRFToken($csrf_token)) {
        $error = 'Invalid request. Please try again.';
    } elseif (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'Please fill in all fields.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match.';
    } elseif (strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters long.';
    } else {
        // For hardcoded admin accounts, use simple verification
        if ($user['id'] === 1 || $user['id'] === 2) {
            if ($current_password !== 'admin123') {
                $error = 'Current password is incorrect.';
            } else {
                $success = 'Password updated successfully! (Note: For demo admin accounts, this is simulated)';
            }
        } else {
            // For regular database users, verify current password
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user['id']]);
            $user_data = $stmt->fetch();
            
            if (!$user_data || !password_verify($current_password, $user_data['password'])) {
                $error = 'Current password is incorrect.';
            } else {
                // Update password in database
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $user['id']]);
                
                // Log activity
                $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, 'password_change', 'User changed password', ?)");
                $stmt->execute([$user['id'], $_SERVER['REMOTE_ADDR'] ?? '']);
                
                $success = 'Password updated successfully!';
            }
        }
    }
}

$page_title = 'Change Password';
include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-lg-6 mx-auto">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="profile.php">Profile</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Change Password</li>
                </ol>
            </nav>
            
            <div class="card shadow">
                <div class="card-header">
                    <h3 class="mb-0">
                        <i class="fas fa-key me-2"></i>Change Password
                    </h3>
                </div>
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
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password *</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                            <div class="invalid-feedback">Please enter your current password.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password *</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" 
                                   minlength="6" required>
                            <div class="form-text">Must be at least 6 characters long.</div>
                            <div class="invalid-feedback">Please enter a new password (minimum 6 characters).</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password *</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                   minlength="6" required>
                            <div class="invalid-feedback">Please confirm your new password.</div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Security Tips:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Use a mix of uppercase and lowercase letters</li>
                                <li>Include numbers and special characters</li>
                                <li>Avoid using personal information</li>
                                <li>Don't reuse passwords from other accounts</li>
                            </ul>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="profile.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Profile
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Additional Security Options -->
            <div class="card mt-4">
                <div class="card-body">
                    <h5><i class="fas fa-shield-alt me-2"></i>Account Security</h5>
                    <p class="text-muted">Keep your account secure with these additional options:</p>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Two-Factor Authentication</h6>
                            <p class="small text-muted">Add an extra layer of security (Coming Soon)</p>
                            <button class="btn btn-sm btn-outline-secondary" disabled>
                                <i class="fas fa-mobile-alt me-1"></i>Enable 2FA
                            </button>
                        </div>
                        <div class="col-md-6">
                            <h6>Login History</h6>
                            <p class="small text-muted">Review recent account activity (Coming Soon)</p>
                            <button class="btn btn-sm btn-outline-secondary" disabled>
                                <i class="fas fa-history me-1"></i>View History
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Password confirmation validation
document.getElementById('confirm_password').addEventListener('input', function() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = this.value;
    
    if (confirmPassword && newPassword !== confirmPassword) {
        this.setCustomValidity('Passwords do not match');
        this.classList.add('is-invalid');
    } else {
        this.setCustomValidity('');
        this.classList.remove('is-invalid');
        if (confirmPassword) {
            this.classList.add('is-valid');
        }
    }
});

document.getElementById('new_password').addEventListener('input', function() {
    const confirmPassword = document.getElementById('confirm_password');
    if (confirmPassword.value) {
        confirmPassword.dispatchEvent(new Event('input'));
    }
});
</script>

<?php include 'includes/footer.php'; ?>
