<?php
require_once 'includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirectByRole();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'user';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // Role-specific fields
    $education = trim($_POST['education'] ?? '');
    $org_name = trim($_POST['org_name'] ?? '');
    $contact_person = trim($_POST['contact_person'] ?? '');
    $verification_note = trim($_POST['verification_note'] ?? '');
    
    // Validation
    if (!verifyCSRFToken($csrf_token)) {
        $error = 'Invalid request. Please try again.';
    } elseif (empty($name) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (($role === 'organizer' || $role === 'career_centre') && empty($org_name)) {
        $error = 'Organization/Institution name is required.';
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'An account with this email already exists.';
        } else {
            // Create user account
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            try {
                // Use the role directly (database now supports career_centre)
                $stmt = $pdo->prepare("
                    INSERT INTO users (name, email, phone, password, role, org_name, contact_person, verification_note) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $name,
                    $email,
                    $phone,
                    $hashed_password,
                    $role, // Now supports 'career_centre' directly
                    ($role === 'organizer' || $role === 'career_centre') ? $org_name : null,
                    ($role === 'organizer' || $role === 'career_centre') ? $contact_person : null,
                    ($role === 'organizer' || $role === 'career_centre') ? $verification_note : null
                ]);
                
                $user_id = $pdo->lastInsertId();
                
                // Log activity
                $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, 'registration', 'New user registered', ?)");
                $stmt->execute([$user_id, $_SERVER['REMOTE_ADDR'] ?? '']);
                
                header("Location: login.php?success=1");
                exit;
                
            } catch (PDOException $e) {
                $error = 'Registration failed. Please try again.';
                error_log("Registration error: " . $e->getMessage());
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Awasar Nepal</title>
    <link rel="stylesheet" href="styles.css">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              'avsar-blue': '#1a2f3a',
              'avsar-gold': '#ccff00',
              'avsar-green': '#00ff41',
              'avsar-dark': '#0a0a0a'
            },
            fontFamily: {
              'inter': ['Inter', 'Segoe UI', 'sans-serif']
            }
          }
        }
      }
    </script>
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        body {
            background: #0a0a0a;
            min-height: 100vh;
        }
        
        .auth-container {
            min-height: calc(100vh - 80px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            margin-top: 100px; /* Account for fixed navbar */
        }
        
        .auth-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 3rem;
            max-width: 680px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        }
        
        .auth-title {
            color: #ffffff;
            font-size: 2rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 0.5rem;
        }
        
        .auth-subtitle {
            color: rgba(255, 255, 255, 0.6);
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
        
        .form-label {
            display: block;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }
        
        .form-input, .form-textarea {
            width: 100%;
            padding: 0.875rem 1rem;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 10px;
            color: #ffffff;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .form-input:focus, .form-textarea:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.12);
            border-color: #ccff00;
            box-shadow: 0 0 0 3px rgba(204, 255, 0, 0.1);
        }
        
        .form-input::placeholder, .form-textarea::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }
        
        .role-selector {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        @media (max-width: 768px) {
            .role-selector {
                grid-template-columns: 1fr;
            }
        }
        
        .role-option {
            position: relative;
            cursor: pointer;
        }
        
        .role-option input[type="radio"] {
            position: absolute;
            opacity: 0;
        }
        
        .role-card {
            padding: 1.5rem;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .role-option input[type="radio"]:checked + .role-card {
            background: rgba(204, 255, 0, 0.1);
            border-color: #ccff00;
        }
        
        .role-card:hover {
            border-color: rgba(204, 255, 0, 0.5);
        }
        
        .role-title {
            color: #ffffff;
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 0.25rem;
        }
        
        .role-description {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.875rem;
        }
        
        .btn-primary {
            width: 100%;
            padding: 1rem;
            background: #ffff00;
            color: #000;
            border: none;
            border-radius: 10px;
            font-size: 1.05rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            box-shadow: 0 4px 15px rgba(255, 255, 0, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(255, 255, 0, 0.5);
            background: #ffff00;
        }
        
        .btn-secondary {
            width: 100%;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.05);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.3);
        }
        
        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
        }
        
        .alert-danger {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }
        
        .divider {
            margin: 2rem 0;
            text-align: center;
            position: relative;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
        }
        
        .divider span {
            background: rgba(255, 255, 255, 0.05);
            padding: 0 1rem;
            color: rgba(255, 255, 255, 0.5);
            position: relative;
            z-index: 1;
        }
        
        .form-text {
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar" x-data="{ mobileMenuOpen: false }">
        <div class="nav-container">
            <div class="nav-left">
                <div class="nav-logo">
                    <a href="index.php" class="hover:scale-105 transition-transform duration-300">
                        <img src="logo.png" alt="Awasar Nepal" class="logo-img">
                    </a>
                </div>
                
                <!-- Desktop Menu -->
                <ul class="nav-menu hidden md:flex">
                    <li><a href="students.php" class="hover:text-avsar-green transition-colors duration-300">Students</a></li>
                    <li><a href="employers.php" class="hover:text-avsar-green transition-colors duration-300">Employers</a></li>
                    <li><a href="career_centers.php" class="hover:text-avsar-green transition-colors duration-300">Career centers</a></li>
                </ul>
            </div>
            
            <div class="nav-right">
                <!-- Mobile Menu Button -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-white p-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                
                <!-- Desktop Buttons -->
                <div class="nav-buttons hidden md:flex">
                    <a href="signup.php" class="btn-signup hover:bg-gray-100 transition-colors duration-300" style="text-decoration: none;">Sign up</a>
                    <a href="login.php" class="btn-login hover:bg-avsar-green/90 transition-colors duration-300" style="text-decoration: none;">Log in</a>
                </div>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div x-show="mobileMenuOpen" x-transition class="md:hidden bg-avsar-dark border-t border-gray-700">
            <div class="px-4 py-2 space-y-2">
                <a href="students.php" class="block py-2 text-white hover:text-avsar-green transition-colors duration-300">Students</a>
                <a href="employers.php" class="block py-2 text-white hover:text-avsar-green transition-colors duration-300">Employers</a>
                <a href="career_centers.php" class="block py-2 text-white hover:text-avsar-green transition-colors duration-300">Career centers</a>
                
                <div class="pt-4 space-y-2">
                    <a href="signup.php" class="w-full btn-signup" style="display: block; text-align: center; text-decoration: none;">Sign up</a>
                    <a href="login.php" class="w-full btn-login" style="display: block; text-align: center; text-decoration: none;">Log in</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Signup Form -->
    <div class="auth-container">
        <div class="auth-card">
            <h1 class="auth-title">Create Account</h1>
            <p class="auth-subtitle">Connect with jobs tailored for students and fresh graduates in Nepal</p>
                    
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo escape($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="signupForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <!-- Role Selection -->
                <div class="role-selector" style="grid-template-columns: repeat(3, 1fr);">
                    <label class="role-option">
                        <input type="radio" name="role" id="role_student" value="user" 
                               <?php echo (!isset($_POST['role']) || $_POST['role'] === 'user') ? 'checked' : ''; ?>>
                        <div class="role-card">
                            <div class="role-title">Student</div>
                            <div class="role-description">Looking for jobs & internships</div>
                        </div>
                    </label>
                    <label class="role-option">
                        <input type="radio" name="role" id="role_employer" value="organizer"
                               <?php echo (isset($_POST['role']) && $_POST['role'] === 'organizer') ? 'checked' : ''; ?>>
                        <div class="role-card">
                            <div class="role-title">Employer</div>
                            <div class="role-description">Post jobs & find talent</div>
                        </div>
                    </label>
                    <label class="role-option">
                        <input type="radio" name="role" id="role_career_centre" value="career_centre"
                               <?php echo (isset($_POST['role']) && $_POST['role'] === 'career_centre') ? 'checked' : ''; ?>>
                        <div class="role-card">
                            <div class="role-title">Career Centre</div>
                            <div class="role-description">Guide students & connect jobs</div>
                        </div>
                    </label>
                </div>
                
                <!-- Basic Information -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="name" class="form-label">Full Name *</label>
                        <input type="text" class="form-input" id="name" name="name" 
                               placeholder="John Doe"
                               value="<?php echo escape($_POST['name'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address *</label>
                        <input type="email" class="form-input" id="email" name="email" 
                               placeholder="john@example.com"
                               value="<?php echo escape($_POST['email'] ?? ''); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="tel" class="form-input" id="phone" name="phone" 
                           placeholder="+977 98XXXXXXXX"
                           value="<?php echo escape($_POST['phone'] ?? ''); ?>">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password" class="form-label">Password *</label>
                        <input type="password" class="form-input" id="password" name="password" 
                               placeholder="Min. 6 characters" required>
                        <div class="form-text">At least 6 characters</div>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirm Password *</label>
                        <input type="password" class="form-input" id="confirm_password" name="confirm_password" 
                               placeholder="Confirm password" required>
                    </div>
                </div>
                
                <!-- Student-specific fields -->
                <div id="user-fields" style="display: none;">
                    <div class="form-group">
                        <label for="education" class="form-label">Education Background</label>
                        <textarea class="form-textarea" id="education" name="education"
                                  placeholder="e.g., Bachelors in Computer Science, Tribhuvan University, 2024"><?php echo escape($_POST['education'] ?? ''); ?></textarea>
                        <div class="form-text">Your degree, university, and graduation year (or expected graduation)</div>
                    </div>
                </div>
                
                <!-- Employer and Career Centre fields -->
                <div id="organizer-fields" style="display: none;">
                    <div class="form-group">
                        <label for="org_name" class="form-label" id="org_name_label">Organization/Institution Name *</label>
                        <input type="text" class="form-input" id="org_name" name="org_name" 
                               placeholder="Your organization or institution name"
                               value="<?php echo escape($_POST['org_name'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="contact_person" class="form-label">Contact Person</label>
                        <input type="text" class="form-input" id="contact_person" name="contact_person" 
                               placeholder="Primary contact person"
                               value="<?php echo escape($_POST['contact_person'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="verification_note" class="form-label">Verification Note</label>
                        <textarea class="form-textarea" id="verification_note" name="verification_note"
                                  placeholder="Additional information to help verify your organization or career centre"><?php echo escape($_POST['verification_note'] ?? ''); ?></textarea>
                    </div>
                </div>
                
                <button type="submit" class="btn-primary">
                    Create Account
                </button>
            </form>
            
            <div class="divider">
                <span>OR</span>
            </div>
            
            <a href="login.php" class="btn-secondary">
                Already have an account? Log In
            </a>
        </div>
    </div>

</body>
</html>

<script>
// Toggle role-specific fields
document.addEventListener('DOMContentLoaded', function() {
    const roleRadios = document.querySelectorAll('input[name="role"]');
    const userFields = document.getElementById('user-fields');
    const organizerFields = document.getElementById('organizer-fields');
    
    function toggleFields() {
        const selectedRole = document.querySelector('input[name="role"]:checked').value;
        const orgNameLabel = document.getElementById('org_name_label');
        
        if (selectedRole === 'user') {
            userFields.style.display = 'block';
            organizerFields.style.display = 'none';
            document.getElementById('org_name').removeAttribute('required');
        } else if (selectedRole === 'organizer') {
            userFields.style.display = 'none';
            organizerFields.style.display = 'block';
            document.getElementById('org_name').setAttribute('required', '');
            if (orgNameLabel) orgNameLabel.textContent = 'Organization Name *';
        } else if (selectedRole === 'career_centre') {
            userFields.style.display = 'none';
            organizerFields.style.display = 'block';
            document.getElementById('org_name').setAttribute('required', '');
            if (orgNameLabel) orgNameLabel.textContent = 'Institution/Career Centre Name *';
        }
    }
    
    roleRadios.forEach(radio => {
        radio.addEventListener('change', toggleFields);
    });
    
    // Initialize on page load
    toggleFields();
    
    // Form validation
    document.getElementById('signupForm').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Passwords do not match!');
            return false;
        }
        
        if (password.length < 6) {
            e.preventDefault();
            alert('Password must be at least 6 characters long!');
            return false;
        }
    });
});
</script>
