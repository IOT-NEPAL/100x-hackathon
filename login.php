<?php
require_once 'db_config.php';

$error = '';
$admin_redirect = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Check for admin credentials FIRST - skip all other checks
    $email_normalized = strtolower($email);
    if ($email_normalized === 'admin@gmail.com' && $password === 'password') {
        // Set session variables
        $_SESSION['user_id'] = 0; // Special admin ID
        $_SESSION['user_name'] = 'Admin';
        $_SESSION['user_email'] = 'admin@gmail.com';
        $_SESSION['role'] = 'admin';
        $_SESSION['org_name'] = null;
        
        // Try header redirect first
        if (!headers_sent()) {
            header("Location: admin/admin-dashboard.php");
            exit();
        } else {
            // Fallback to JavaScript redirect if headers already sent
            $admin_redirect = true;
        }
    }
    
    // Continue with normal login flow for other users
    $remember = isset($_POST['remember']);
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password!';
    } else {
        try {
            // Get user from database
            $stmt = $pdo->prepare("SELECT id, name, email, password, role, org_name FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['org_name'] = $user['org_name'];
                
                // Handle remember me
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
                    
                    $stmt2 = $pdo->prepare("INSERT INTO sessions (user_id, session_token, expires_at) VALUES (?, ?, ?)");
                    $stmt2->execute([$user['id'], $token, $expires]);
                    
                    setcookie('remember_token', $token, strtotime('+30 days'), '/');
                }
                
                // Redirect to appropriate dashboard based on role
                if ($user['role'] === 'organizer') {
                    header("Location: organizer/organizer-dashboard.php");
                } else {
                    header("Location: user/user-dashboard.php");
                }
                exit();
            } else {
                $error = 'Invalid email or password!';
            }
        } catch (PDOException $e) {
            $error = 'Login failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Avsar</title>
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

        .login-container {
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

        input[type="email"]:focus,
        input[type="password"]:focus {
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(0, 255, 136, 0.2);
        }

        .remember-me {
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

        .remember-me label {
            margin: 0;
            cursor: pointer;
            font-size: 14px;
            color: #ffffff;
        }

        .login-btn {
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

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 30px rgba(0, 255, 136, 0.5);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .signup-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #a0a0a0;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .signup-link:hover {
            color: #ffffff;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #a0a0a0;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .back-link:hover {
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
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Welcome Back</h1>
        <p class="subtitle">Log in to continue your journey</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="devya@gmail.com" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="........" required>
            </div>

            <div class="remember-me">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Remember me</label>
            </div>

            <button type="submit" class="login-btn">LOG IN</button>
        </form>

        <a href="signin.php" class="signup-link">Don't have an account? Sign up</a>
        <a href="index.php" class="back-link">← Back to Home</a>
    </div>
    
    <?php if ($admin_redirect): ?>
    <script>
        window.location.href = 'admin/admin-dashboard.php';
    </script>
    <?php endif; ?>
</body>
</html>

