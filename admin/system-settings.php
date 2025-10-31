<?php
require_once '../includes/auth.php';
requireRole('admin');

$user = getCurrentUser();

// Handle settings updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_settings'])) {
        $csrf_token = $_POST['csrf_token'] ?? '';
        
        if (!verifyCSRFToken($csrf_token)) {
            $error_message = 'Invalid request. Please try again.';
        } else {
            // For this demo, we'll simulate saving settings
            // In a real application, these would be stored in a settings table
            $success_message = 'Settings updated successfully!';
        }
    }
    
    if (isset($_POST['clear_logs'])) {
        try {
            $stmt = $pdo->prepare("DELETE FROM activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $stmt->execute();
            $deleted_count = $stmt->rowCount();
            $success_message = "Cleared $deleted_count old log entries (older than 30 days).";
        } catch (PDOException $e) {
            $error_message = "Error clearing logs: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['backup_database'])) {
        // Simulate backup process
        $success_message = 'Database backup initiated. You will receive an email when complete.';
    }
}

// Get system statistics
$stats = [];
try {
    // Database size info (approximate)
    $stmt = $pdo->query("
        SELECT 
            (SELECT COUNT(*) FROM users) as total_users,
            (SELECT COUNT(*) FROM opportunities) as total_opportunities,
            (SELECT COUNT(*) FROM applications) as total_applications,
            (SELECT COUNT(*) FROM activity_logs) as total_logs
    ");
    $stats = $stmt->fetch();
    
    // Recent activity count
    $stmt = $pdo->query("SELECT COUNT(*) as recent_activity FROM activity_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    $stats['recent_activity'] = $stmt->fetchColumn();
    
    // Disk usage simulation
    $stats['disk_usage'] = '45.2 MB'; // Simulated
    $stats['upload_files'] = '12.8 MB'; // Simulated
    
} catch (PDOException $e) {
    $stats = [
        'total_users' => 0,
        'total_opportunities' => 0,
        'total_applications' => 0,
        'total_logs' => 0,
        'recent_activity' => 0,
        'disk_usage' => '0 MB',
        'upload_files' => '0 MB'
    ];
}

$page_title = 'System Settings';
include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="admin-dashboard.php">Admin Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">System Settings</li>
                </ol>
            </nav>
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-cog me-2"></i>System Settings</h2>
                <a href="admin-dashboard.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo escape($success_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo escape($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- System Overview -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-users fa-2x text-primary mb-2"></i>
                            <h4><?php echo number_format($stats['total_users']); ?></h4>
                            <p class="card-text">Total Users</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-briefcase fa-2x text-success mb-2"></i>
                            <h4><?php echo number_format($stats['total_opportunities']); ?></h4>
                            <p class="card-text">Opportunities</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-paper-plane fa-2x text-info mb-2"></i>
                            <h4><?php echo number_format($stats['total_applications']); ?></h4>
                            <p class="card-text">Applications</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-chart-line fa-2x text-warning mb-2"></i>
                            <h4><?php echo number_format($stats['recent_activity']); ?></h4>
                            <p class="card-text">24hr Activity</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-8">
                    <!-- General Settings -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-sliders-h me-2"></i>General Settings</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="update_settings" value="1">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="site_name" class="form-label">Site Name</label>
                                            <input type="text" class="form-control" id="site_name" name="site_name" 
                                                   value="Inclusify" readonly>
                                            <div class="form-text">The name of your website</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="admin_email" class="form-label">Admin Email</label>
                                            <input type="email" class="form-control" id="admin_email" name="admin_email" 
                                                   value="admin@inclusify.com">
                                            <div class="form-text">Primary administrator email</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="max_file_size" class="form-label">Max File Upload Size</label>
                                            <select class="form-select" id="max_file_size" name="max_file_size">
                                                <option value="2">2 MB</option>
                                                <option value="5" selected>5 MB</option>
                                                <option value="10">10 MB</option>
                                                <option value="20">20 MB</option>
                                            </select>
                                            <div class="form-text">Maximum size for file uploads</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="session_timeout" class="form-label">Session Timeout</label>
                                            <select class="form-select" id="session_timeout" name="session_timeout">
                                                <option value="30">30 minutes</option>
                                                <option value="60" selected>1 hour</option>
                                                <option value="120">2 hours</option>
                                                <option value="1440">24 hours</option>
                                            </select>
                                            <div class="form-text">User session duration</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Feature Toggles</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="allow_registrations" 
                                               name="allow_registrations" checked>
                                        <label class="form-check-label" for="allow_registrations">
                                            Allow new user registrations
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="require_email_verification" 
                                               name="require_email_verification">
                                        <label class="form-check-label" for="require_email_verification">
                                            Require email verification for new accounts
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="enable_notifications" 
                                               name="enable_notifications" checked>
                                        <label class="form-check-label" for="enable_notifications">
                                            Enable email notifications
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="maintenance_mode" 
                                               name="maintenance_mode">
                                        <label class="form-check-label" for="maintenance_mode">
                                            <span class="text-warning">Maintenance Mode</span>
                                        </label>
                                        <div class="form-text">Restricts access to administrators only</div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="site_description" class="form-label">Site Description</label>
                                    <textarea class="form-control" id="site_description" name="site_description" rows="3">Inclusify is a platform dedicated to creating inclusive employment opportunities for people with disabilities. We connect job seekers with employers committed to diversity and accessibility.</textarea>
                                    <div class="form-text">Brief description of your website</div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Settings
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Email Settings -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-envelope me-2"></i>Email Configuration</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Demo Mode:</strong> Email settings are simulated in this demo environment.
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="smtp_host" class="form-label">SMTP Host</label>
                                        <input type="text" class="form-control" id="smtp_host" name="smtp_host" 
                                               value="smtp.example.com" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="smtp_port" class="form-label">SMTP Port</label>
                                        <input type="number" class="form-control" id="smtp_port" name="smtp_port" 
                                               value="587" readonly>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="smtp_username" class="form-label">SMTP Username</label>
                                        <input type="text" class="form-control" id="smtp_username" name="smtp_username" 
                                               value="noreply@inclusify.com" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="smtp_encryption" class="form-label">Encryption</label>
                                        <select class="form-select" id="smtp_encryption" name="smtp_encryption" disabled>
                                            <option value="tls" selected>TLS</option>
                                            <option value="ssl">SSL</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="button" class="btn btn-outline-primary" disabled>
                                <i class="fas fa-paper-plane me-2"></i>Test Email Configuration
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <!-- System Status -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-server me-2"></i>System Status</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>PHP Version:</span>
                                    <span class="badge bg-success"><?php echo PHP_VERSION; ?></span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Database:</span>
                                    <span class="badge bg-success">Connected</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Disk Usage:</span>
                                    <span class="badge bg-info"><?php echo $stats['disk_usage']; ?></span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Upload Files:</span>
                                    <span class="badge bg-warning"><?php echo $stats['upload_files']; ?></span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Log Entries:</span>
                                    <span class="badge bg-secondary"><?php echo number_format($stats['total_logs']); ?></span>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <h6>Quick Actions</h6>
                            <div class="d-grid gap-2">
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="clear_logs" value="1">
                                    <button type="submit" class="btn btn-outline-warning btn-sm w-100" 
                                            onclick="return confirm('Clear old log entries (30+ days)?')">
                                        <i class="fas fa-broom me-2"></i>Clear Old Logs
                                    </button>
                                </form>
                                
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="backup_database" value="1">
                                    <button type="submit" class="btn btn-outline-info btn-sm w-100">
                                        <i class="fas fa-download me-2"></i>Backup Database
                                    </button>
                                </form>
                                
                                <button type="button" class="btn btn-outline-secondary btn-sm w-100" 
                                        onclick="window.location.reload()">
                                    <i class="fas fa-sync me-2"></i>Refresh Status
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Security Settings -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Security</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Password Requirements</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="require_strong_passwords" checked disabled>
                                    <label class="form-check-label" for="require_strong_passwords">
                                        Minimum 6 characters
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="require_special_chars" disabled>
                                    <label class="form-check-label" for="require_special_chars">
                                        Require special characters
                                    </label>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Login Security</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="enable_rate_limiting" checked disabled>
                                    <label class="form-check-label" for="enable_rate_limiting">
                                        Rate limiting enabled
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="log_failed_attempts" checked disabled>
                                    <label class="form-check-label" for="log_failed_attempts">
                                        Log failed login attempts
                                    </label>
                                </div>
                            </div>
                            
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                <small>All security features are active</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Accessibility Features -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-universal-access me-2"></i>Accessibility</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>High Contrast Mode</span>
                                    <span class="badge bg-success">Available</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Font Size Controls</span>
                                    <span class="badge bg-success">Available</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Text-to-Speech</span>
                                    <span class="badge bg-success">Available</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Voice Commands</span>
                                    <span class="badge bg-success">Available</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Keyboard Navigation</span>
                                    <span class="badge bg-success">Available</span>
                                </div>
                            </div>
                            
                            <a href="../voice-demo.php" class="btn btn-outline-primary btn-sm w-100">
                                <i class="fas fa-microphone me-2"></i>Voice Commands Demo
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
