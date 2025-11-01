<?php
require_once 'db_config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $user_type = $_POST['user_type']; // 'student' or 'employer'
    
    // Map user_type to role
    $role = ($user_type === 'employer') ? 'organizer' : 'user';
    
    // For organizers, get additional fields
    $org_name = isset($_POST['org_name']) ? trim($_POST['org_name']) : null;
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : null;
    
    // Validate inputs
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'All fields are required!';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match!';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format!';
    } elseif ($role === 'organizer' && empty($org_name)) {
        $error = 'Organization name is required for employers!';
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $error = 'Email already registered!';
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert user
                if ($role === 'organizer') {
                    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, org_name, phone) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $email, $hashed_password, $role, $org_name, $phone]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$name, $email, $hashed_password, $role]);
                }
                
                $success = 'Account created successfully! Redirecting to login...';
                header("refresh:2;url=login.php");
            }
        } catch (PDOException $e) {
            $error = 'Registration failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Avsar</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #000000;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .signup-container {
            background: #1a1a1a;
            border-radius: 20px;
            padding: 50px 40px;
            max-width: 450px;
            width: 100%;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
        }

        h1 {
            font-size: 36px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 10px;
            text-align: center;
        }

        .subtitle {
            font-size: 14px;
            color: #a0a0a0;
            margin-bottom: 35px;
            text-align: center;
        }

        .user-type-selection {
            margin-bottom: 30px;
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .user-type-option {
            flex: 1;
            position: relative;
        }

        .user-type-option input[type="radio"] {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }

        .user-type-label {
            display: block;
            padding: 12px 20px;
            background: #2a2a2a;
            border: 2px solid #3a3a3a;
            border-radius: 10px;
            text-align: center;
            color: #ffffff;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .user-type-option input[type="radio"]:checked + .user-type-label {
            background: linear-gradient(135deg, #00ff88 0%, #aaff00 100%);
            color: #000000;
            border-color: #00ff88;
            box-shadow: 0 4px 15px rgba(0, 255, 136, 0.3);
        }

        .user-type-option:hover .user-type-label {
            border-color: #00ff88;
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            color: #ffffff;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 14px 16px;
            background: #f5f5f5;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            color: #000000;
            outline: none;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(0, 255, 136, 0.2);
        }

        .terms {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            gap: 10px;
        }

        input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #00ff88;
        }

        .terms label {
            margin: 0;
            cursor: pointer;
            font-size: 14px;
            color: #ffffff;
        }

        .signup-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #00ff88 0%, #aaff00 100%);
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            color: #000000;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 20px rgba(0, 255, 136, 0.3);
            transition: all 0.3s ease;
        }

        .signup-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 30px rgba(0, 255, 136, 0.5);
        }

        .signup-btn:active {
            transform: translateY(0);
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #a0a0a0;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            color: #ffffff;
        }

        .login-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #a0a0a0;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .login-link:hover {
            color: #ffffff;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }

        .alert-error {
            background: #ff4444;
            color: #ffffff;
        }

        .alert-success {
            background: #00ff88;
            color: #000000;
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <h1>Create Account</h1>
        <p class="subtitle">Sign up to start your journey</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form action="signin.php" method="POST">
            <div class="user-type-selection">
                <div class="user-type-option">
                    <input type="radio" id="student" name="user_type" value="student" checked required>
                    <label for="student" class="user-type-label">Student</label>
                </div>
                <div class="user-type-option">
                    <input type="radio" id="employer" name="user_type" value="employer" required>
                    <label for="employer" class="user-type-label">Employer</label>
                </div>
            </div>

            <div class="form-group">
                <label for="name"><span id="name-label">Full Name</span></label>
                <input type="text" id="name" name="name" placeholder="Enter your full name" required>
            </div>

            <div class="form-group employer-only" style="display: none;">
                <label for="org_name">Organization Name</label>
                <input type="text" id="org_name" name="org_name" placeholder="Enter organization name">
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
            </div>

            <div class="form-group employer-only" style="display: none;">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" placeholder="Enter phone number">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Create a password" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
            </div>

            <div class="terms">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms">I agree to the terms and conditions</label>
            </div>

            <button type="submit" class="signup-btn">SIGN UP</button>
        </form>

        <a href="login.php" class="login-link">Already have an account? Log in</a>
        <a href="index.php" class="back-link">← Back to Home</a>
    </div>

    <script>
        // Toggle employer fields based on user type selection
        const studentRadio = document.getElementById('student');
        const employerRadio = document.getElementById('employer');
        const employerFields = document.querySelectorAll('.employer-only');
        const nameLabel = document.getElementById('name-label');
        const orgNameField = document.getElementById('org_name');

        function toggleEmployerFields() {
            if (employerRadio.checked) {
                employerFields.forEach(field => field.style.display = 'block');
                nameLabel.textContent = 'Contact Person Name';
                orgNameField.required = true;
            } else {
                employerFields.forEach(field => field.style.display = 'none');
                nameLabel.textContent = 'Full Name';
                orgNameField.required = false;
            }
        }

        studentRadio.addEventListener('change', toggleEmployerFields);
        employerRadio.addEventListener('change', toggleEmployerFields);

        // Password confirmation validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
        });
    </script>
</body>
</html>

