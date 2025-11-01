<?php
require_once 'includes/auth.php';
requireLogin();

$user = getCurrentUser();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $skills = trim($_POST['skills'] ?? '');
    $org_name = trim($_POST['org_name'] ?? '');
    $contact_person = trim($_POST['contact_person'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verifyCSRFToken($csrf_token)) {
        $error = 'Invalid request. Please try again.';
    } elseif (empty($name) || empty($email)) {
        $error = 'Name and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Check if email is already taken by another user
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user['id']]);
        if ($stmt->fetch()) {
            $error = 'This email is already in use by another account.';
        } else {
            // Handle profile picture upload
            $profile_pic = $user['profile_pic']; // Keep existing if no new upload
            
            if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = 'uploads/profile_pics/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_info = pathinfo($_FILES['profile_pic']['name']);
                $extension = strtolower($file_info['extension']);
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                
                if (!in_array($extension, $allowed_extensions)) {
                    $error = 'Please upload a valid image file (JPG, PNG, GIF).';
                } elseif ($_FILES['profile_pic']['size'] > 5 * 1024 * 1024) {
                    $error = 'Profile picture must be less than 5MB.';
                } else {
                    $filename = uniqid() . '_' . $user['id'] . '.' . $extension;
                    $upload_path = $upload_dir . $filename;
                    
                    if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $upload_path)) {
                        // Delete old profile picture if exists
                        if ($user['profile_pic'] && file_exists($upload_dir . $user['profile_pic'])) {
                            unlink($upload_dir . $user['profile_pic']);
                        }
                        $profile_pic = $filename;
                    } else {
                        $error = 'Failed to upload profile picture.';
                    }
                }
            }
            
            if (!$error) {
                try {
                    // Update user profile
                    $sql = "UPDATE users SET name = ?, email = ?, phone = ?, profile_pic = ?";
                    $params = [$name, $email, $phone, $profile_pic];
                    
                    // Add role-specific fields
                    if ($user['role'] === 'user') {
                        $sql .= ", skills = ?";
                        $params[] = $skills;
                    } elseif ($user['role'] === 'organizer') {
                        $sql .= ", org_name = ?, contact_person = ?";
                        $params[] = $org_name;
                        $params[] = $contact_person;
                    }
                    
                    $sql .= " WHERE id = ?";
                    $params[] = $user['id'];
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    
                    // Log activity
                    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, 'profile_update', 'User updated profile', ?)");
                    $stmt->execute([$user['id'], $_SERVER['REMOTE_ADDR'] ?? '']);
                    
                    $success = 'Profile updated successfully!';
                    
                    // Refresh user data
                    $user = getCurrentUser();
                    
                } catch (PDOException $e) {
                    $error = 'Failed to update profile. Please try again.';
                    error_log("Profile update error: " . $e->getMessage());
                }
            }
        }
    }
}

$page_title = 'Profile';
include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow">
                <div class="card-header">
                    <h3 class="mb-0">
                        <i class="fas fa-user-edit me-2"></i>Edit Profile
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
                    
                    <form method="POST" action="" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <!-- Profile Picture -->
                        <div class="row mb-4">
                            <div class="col-md-3 text-center">
                                <div class="profile-pic-container">
                                    <?php if ($user['profile_pic']): ?>
                                        <img src="uploads/profile_pics/<?php echo escape($user['profile_pic']); ?>" 
                                             alt="Profile Picture" class="rounded-circle img-thumbnail" 
                                             width="150" height="150" style="object-fit: cover;">
                                    <?php else: ?>
                                        <div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center img-thumbnail" 
                                             style="width: 150px; height: 150px;">
                                            <i class="fas fa-user fa-4x text-white"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="mt-3">
                                    <label for="profile_pic" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-camera me-1"></i>Change Photo
                                    </label>
                                    <input type="file" id="profile_pic" name="profile_pic" 
                                           accept="image/*" class="d-none">
                                    <div class="form-text small">Max 5MB. JPG, PNG, GIF only.</div>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Full Name *</label>
                                            <input type="text" class="form-control" id="name" name="name" 
                                                   value="<?php echo escape($user['name']); ?>" required>
                                            <div class="invalid-feedback">Please enter your full name.</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email Address *</label>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   value="<?php echo escape($user['email']); ?>" required>
                                            <div class="invalid-feedback">Please enter a valid email address.</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo escape($user['phone']); ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'organizer' ? 'success' : 'primary'); ?>">
                                        <?php echo ucfirst(escape($user['role'])); ?>
                                    </span>
                                    <small class="text-muted ms-2">Account Type</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Role-specific fields -->
                        <?php if ($user['role'] === 'user'): ?>
                            <hr>
                            <h5 class="mb-3"><i class="fas fa-info-circle me-2"></i>Personal Information</h5>
                            
                            <div class="mb-3">
                                <label for="skills" class="form-label">Skills & Experience</label>
                                <textarea class="form-control" id="skills" name="skills" rows="4"
                                          placeholder="List your skills, experience, and qualifications"><?php echo escape($user['skills']); ?></textarea>
                                <div class="form-text">Include technical skills, soft skills, certifications, and work experience.</div>
                            </div>
                            
                        <?php elseif ($user['role'] === 'organizer'): ?>
                            <hr>
                            <h5 class="mb-3"><i class="fas fa-building me-2"></i>Organization Information</h5>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="org_name" class="form-label">Organization Name *</label>
                                        <input type="text" class="form-control" id="org_name" name="org_name" 
                                               value="<?php echo escape($user['org_name']); ?>" required>
                                        <div class="invalid-feedback">Please enter your organization name.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="contact_person" class="form-label">Contact Person</label>
                                        <input type="text" class="form-control" id="contact_person" name="contact_person" 
                                               value="<?php echo escape($user['contact_person']); ?>"
                                               placeholder="Primary contact person">
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <hr>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Update Profile
                                </button>
                                <?php
                                $cancel_link = '/avsarnepal/index.php';
                                if ($user['role'] === 'user') {
                                    $cancel_link = '/avsarnepal/user/user-dashboard.php';
                                } elseif ($user['role'] === 'organizer') {
                                    $cancel_link = '/avsarnepal/organizer/organizer-dashboard.php';
                                } elseif ($user['role'] === 'admin') {
                                    $cancel_link = '/avsarnepal/admin/admin-dashboard.php';
                                } elseif ($user['role'] === 'career_centre') {
                                    $cancel_link = '/avsarnepal/career_centre/career-centre-dashboard.php';
                                }
                                ?>
                                <a href="<?php echo $cancel_link; ?>" 
                                   class="btn btn-outline-secondary ms-2">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                            </div>
                            <div class="col-md-6 text-end">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Last updated: <?php echo date('M j, Y', strtotime($user['updated_at'] ?? $user['created_at'])); ?>
                                </small>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Account Actions -->
    <div class="row mt-4">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Account Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <h6><i class="fas fa-key me-2"></i>Change Password</h6>
                            <p class="text-muted small">Update your account password for better security.</p>
                            <a href="change-password.php" class="btn btn-outline-primary btn-sm">
                                Change Password
                            </a>
                        </div>
                        
                        <div class="col-md-6">
                            <h6><i class="fas fa-download me-2"></i>Export Data</h6>
                            <p class="text-muted small">Download a copy of your profile data and activity.</p>
                            <a href="export-data.php" class="btn btn-outline-info btn-sm">
                                Export Data
                            </a>
                        </div>
                        
                        <?php if ($user['role'] === 'user'): ?>
                            <div class="col-md-6">
                                <h6><i class="fas fa-file-download me-2"></i>My Applications</h6>
                                <p class="text-muted small">View and manage your job applications.</p>
                                <a href="user/my-applications.php" class="btn btn-outline-success btn-sm">
                                    View Applications
                                </a>
                            </div>
                        <?php elseif ($user['role'] === 'organizer'): ?>
                            <div class="col-md-6">
                                <h6><i class="fas fa-briefcase me-2"></i>My Opportunities</h6>
                                <p class="text-muted small">Manage your posted opportunities.</p>
                                <a href="organizer/my-opportunities.php" class="btn btn-outline-success btn-sm">
                                    Manage Opportunities
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <div class="col-12">
                            <hr class="my-3">
                            <h6 class="text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Danger Zone</h6>
                            <p class="text-muted small">Permanently delete your account and all associated data.</p>
                            <button type="button" class="btn btn-outline-danger btn-sm" 
                                    data-confirm="Are you sure you want to delete your account? This action cannot be undone."
                                    onclick="alert('Account deletion feature will be implemented.')">
                                Delete Account
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Profile picture preview
document.getElementById('profile_pic').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const container = document.querySelector('.profile-pic-container');
            container.innerHTML = `
                <img src="${e.target.result}" alt="Profile Picture" 
                     class="rounded-circle img-thumbnail" width="150" height="150" 
                     style="object-fit: cover;">
            `;
        };
        reader.readAsDataURL(file);
    }
});
</script>

<?php include 'includes/footer.php'; ?>
