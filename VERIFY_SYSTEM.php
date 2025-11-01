<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Verification - AVSAR</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
        }
        h1 {
            color: #1a1a1a;
            border-bottom: 3px solid #ffff00;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        h2 {
            color: #1a1a1a;
            margin-top: 30px;
            margin-bottom: 15px;
        }
        .check-item {
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .check-pass {
            background: #d4edda;
            border-left: 4px solid #28a745;
        }
        .check-fail {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
        }
        .check-warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
        }
        .icon {
            font-size: 24px;
            font-weight: bold;
        }
        .details {
            flex: 1;
        }
        .file-list {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .file-list ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        .btn {
            background: #1a1a1a;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin: 10px 5px;
            cursor: pointer;
        }
        .btn:hover {
            background: #2d2d2d;
        }
        .summary {
            background: #e7f3ff;
            border: 2px solid #0066cc;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 AVSAR System Verification</h1>
        <p style="color: #666; margin-bottom: 20px;">Checking all system components and connections...</p>

        <?php
        $all_checks_pass = true;
        $errors = [];
        $warnings = [];
        $success = [];

        // Check 1: Database Connection
        echo '<h2>1️⃣ Database Connection</h2>';
        try {
            require_once 'db_config.php';
            echo '<div class="check-item check-pass">';
            echo '<span class="icon">✅</span>';
            echo '<div class="details"><strong>Database Connected</strong><br>';
            echo '<small>Host: ' . DB_HOST . ' | Database: ' . DB_NAME . '</small></div>';
            echo '</div>';
            $success[] = 'Database connection successful';
        } catch (Exception $e) {
            echo '<div class="check-item check-fail">';
            echo '<span class="icon">❌</span>';
            echo '<div class="details"><strong>Database Connection Failed</strong><br>';
            echo '<small>' . htmlspecialchars($e->getMessage()) . '</small></div>';
            echo '</div>';
            $errors[] = 'Database connection failed';
            $all_checks_pass = false;
        }

        // Check 2: Database Tables
        echo '<h2>2️⃣ Database Tables</h2>';
        $required_tables = ['users', 'opportunities', 'applications', 'job_offers', 'activity_logs', 'sessions'];
        $existing_tables = [];
        
        try {
            $result = $pdo->query("SHOW TABLES");
            $existing_tables = $result->fetchAll(PDO::FETCH_COLUMN);
            
            $missing_tables = array_diff($required_tables, $existing_tables);
            
            if (empty($missing_tables)) {
                echo '<div class="check-item check-pass">';
                echo '<span class="icon">✅</span>';
                echo '<div class="details"><strong>All Tables Present</strong><br>';
                echo '<small>Found ' . count($required_tables) . ' required tables</small></div>';
                echo '</div>';
                $success[] = 'All database tables exist';
            } else {
                echo '<div class="check-item check-fail">';
                echo '<span class="icon">❌</span>';
                echo '<div class="details"><strong>Missing Tables</strong><br>';
                echo '<small>Missing: ' . implode(', ', $missing_tables) . '</small><br>';
                echo '<small>Import database.sql in phpMyAdmin</small></div>';
                echo '</div>';
                $errors[] = 'Missing database tables: ' . implode(', ', $missing_tables);
                $all_checks_pass = false;
            }
        } catch (Exception $e) {
            echo '<div class="check-item check-fail">';
            echo '<span class="icon">❌</span>';
            echo '<div class="details"><strong>Cannot Check Tables</strong><br>';
            echo '<small>' . htmlspecialchars($e->getMessage()) . '</small></div>';
            echo '</div>';
            $errors[] = 'Cannot verify tables';
            $all_checks_pass = false;
        }

        // Check 3: Critical Files
        echo '<h2>3️⃣ Critical Files</h2>';
        $critical_files = [
            'db_config.php' => 'Database configuration',
            'login.php' => 'Login page',
            'signin.php' => 'Signup page',
            'logout.php' => 'Logout handler',
            'opportunities.php' => 'Job listing',
            'view-opportunity.php' => 'Job details',
            'apply-opportunity.php' => 'Apply form',
            'user/user-dashboard.php' => 'Student dashboard',
            'user/my-skills.php' => 'Skills management',
            'user/career-guidance-ai.php' => 'AI chatbot',
            'user/job-offers.php' => 'Job offers',
            'organizer/organizer-dashboard.php' => 'Employer dashboard',
            'organizer/add-opportunity.php' => 'Post job',
            'organizer/view-applications.php' => 'View applications',
            'includes/header.php' => 'Navigation header',
            'includes/footer.php' => 'Footer scripts'
        ];

        $missing_files = [];
        foreach ($critical_files as $file => $desc) {
            if (!file_exists($file)) {
                $missing_files[] = "$file ($desc)";
            }
        }

        if (empty($missing_files)) {
            echo '<div class="check-item check-pass">';
            echo '<span class="icon">✅</span>';
            echo '<div class="details"><strong>All Critical Files Present</strong><br>';
            echo '<small>' . count($critical_files) . ' files verified</small></div>';
            echo '</div>';
            $success[] = 'All critical files exist';
        } else {
            echo '<div class="check-item check-fail">';
            echo '<span class="icon">❌</span>';
            echo '<div class="details"><strong>Missing Files</strong><br>';
            echo '<small>' . implode('<br>', $missing_files) . '</small></div>';
            echo '</div>';
            $errors[] = count($missing_files) . ' files missing';
            $all_checks_pass = false;
        }

        // Check 4: Folders
        echo '<h2>4️⃣ Required Folders</h2>';
        $required_folders = ['user', 'organizer', 'includes', 'uploads'];
        $missing_folders = [];
        
        foreach ($required_folders as $folder) {
            if (!is_dir($folder)) {
                $missing_folders[] = $folder;
            }
        }

        if (empty($missing_folders)) {
            echo '<div class="check-item check-pass">';
            echo '<span class="icon">✅</span>';
            echo '<div class="details"><strong>All Folders Present</strong><br>';
            echo '<small>' . implode(', ', $required_folders) . '</small></div>';
            echo '</div>';
            $success[] = 'All folders exist';
        } else {
            echo '<div class="check-item check-fail">';
            echo '<span class="icon">❌</span>';
            echo '<div class="details"><strong>Missing Folders</strong><br>';
            echo '<small>' . implode(', ', $missing_folders) . '</small></div>';
            echo '</div>';
            $errors[] = 'Missing folders: ' . implode(', ', $missing_folders);
            $all_checks_pass = false;
        }

        // Check 5: API Key
        echo '<h2>5️⃣ Gemini API Configuration</h2>';
        if (defined('GEMINI_API_KEY') && GEMINI_API_KEY !== 'YOUR_GEMINI_API_KEY_HERE') {
            echo '<div class="check-item check-pass">';
            echo '<span class="icon">✅</span>';
            echo '<div class="details"><strong>API Key Configured</strong><br>';
            echo '<small>Key: ' . substr(GEMINI_API_KEY, 0, 10) . '...' . substr(GEMINI_API_KEY, -4) . '</small></div>';
            echo '</div>';
            $success[] = 'Gemini API key configured';
        } else {
            echo '<div class="check-item check-warning">';
            echo '<span class="icon">⚠️</span>';
            echo '<div class="details"><strong>API Key Not Configured</strong><br>';
            echo '<small>Update GEMINI_API_KEY in db_config.php for AI chatbot to work</small></div>';
            echo '</div>';
            $warnings[] = 'API key needs configuration';
        }

        // Check 6: PHP Version
        echo '<h2>6️⃣ PHP Version</h2>';
        $php_version = phpversion();
        if (version_compare($php_version, '7.4.0', '>=')) {
            echo '<div class="check-item check-pass">';
            echo '<span class="icon">✅</span>';
            echo '<div class="details"><strong>PHP Version Compatible</strong><br>';
            echo '<small>Current: PHP ' . $php_version . ' (Required: 7.4+)</small></div>';
            echo '</div>';
            $success[] = 'PHP version compatible';
        } else {
            echo '<div class="check-item check-fail">';
            echo '<span class="icon">❌</span>';
            echo '<div class="details"><strong>PHP Version Too Old</strong><br>';
            echo '<small>Current: ' . $php_version . ' | Required: 7.4+</small></div>';
            echo '</div>';
            $errors[] = 'PHP version incompatible';
            $all_checks_pass = false;
        }

        // Check 7: Extensions
        echo '<h2>7️⃣ PHP Extensions</h2>';
        $required_extensions = ['pdo', 'pdo_mysql', 'session', 'json'];
        $missing_extensions = [];
        
        foreach ($required_extensions as $ext) {
            if (!extension_loaded($ext)) {
                $missing_extensions[] = $ext;
            }
        }

        if (empty($missing_extensions)) {
            echo '<div class="check-item check-pass">';
            echo '<span class="icon">✅</span>';
            echo '<div class="details"><strong>All Extensions Loaded</strong><br>';
            echo '<small>' . implode(', ', $required_extensions) . '</small></div>';
            echo '</div>';
            $success[] = 'All PHP extensions available';
        } else {
            echo '<div class="check-item check-fail">';
            echo '<span class="icon">❌</span>';
            echo '<div class="details"><strong>Missing Extensions</strong><br>';
            echo '<small>' . implode(', ', $missing_extensions) . '</small></div>';
            echo '</div>';
            $errors[] = 'Missing PHP extensions';
            $all_checks_pass = false;
        }

        // Check 8: Session
        echo '<h2>8️⃣ Session Support</h2>';
        if (session_status() === PHP_SESSION_ACTIVE) {
            echo '<div class="check-item check-pass">';
            echo '<span class="icon">✅</span>';
            echo '<div class="details"><strong>Session Active</strong><br>';
            echo '<small>Sessions are working properly</small></div>';
            echo '</div>';
            $success[] = 'Sessions working';
        } else {
            echo '<div class="check-item check-warning">';
            echo '<span class="icon">⚠️</span>';
            echo '<div class="details"><strong>Session Not Started</strong><br>';
            echo '<small>This is normal for verification page</small></div>';
            echo '</div>';
        }

        // Final Summary
        echo '<h2>📊 Verification Summary</h2>';
        
        if ($all_checks_pass) {
            echo '<div class="summary" style="background: #d4edda; border-color: #28a745;">';
            echo '<h3 style="color: #155724; margin-bottom: 15px;">✅ All Checks Passed!</h3>';
            echo '<p style="color: #155724;">Your AVSAR system is properly configured and ready to use.</p>';
            echo '<div style="margin-top: 20px;">';
            echo '<a href="index.php" class="btn">Go to Application →</a>';
            echo '<a href="test-gemini-api.php" class="btn" style="background: #0066cc;">Test API Connection</a>';
            echo '</div>';
            echo '</div>';
        } else {
            echo '<div class="summary" style="background: #f8d7da; border-color: #dc3545;">';
            echo '<h3 style="color: #721c24; margin-bottom: 15px;">❌ Issues Found</h3>';
            echo '<p style="color: #721c24;">Please fix the following issues:</p>';
            echo '<ul style="color: #721c24;">';
            foreach ($errors as $error) {
                echo '<li>' . htmlspecialchars($error) . '</li>';
            }
            echo '</ul>';
            echo '<p style="color: #721c24; margin-top: 15px;">See <strong>INSTALLATION_GUIDE.md</strong> for help.</p>';
            echo '</div>';
        }

        if (!empty($warnings)) {
            echo '<div class="summary" style="background: #fff3cd; border-color: #ffc107; margin-top: 20px;">';
            echo '<h3 style="color: #856404; margin-bottom: 15px;">⚠️ Warnings</h3>';
            echo '<ul style="color: #856404;">';
            foreach ($warnings as $warning) {
                echo '<li>' . htmlspecialchars($warning) . '</li>';
            }
            echo '</ul>';
            echo '</div>';
        }

        // System Information
        echo '<h2>💻 System Information</h2>';
        echo '<div class="file-list">';
        echo '<p><strong>PHP Version:</strong> ' . phpversion() . '</p>';
        echo '<p><strong>Server:</strong> ' . $_SERVER['SERVER_SOFTWARE'] . '</p>';
        echo '<p><strong>Database:</strong> ' . (isset($pdo) ? 'MySQL via PDO' : 'Not connected') . '</p>';
        echo '<p><strong>Session Status:</strong> ' . (session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive') . '</p>';
        echo '<p><strong>Document Root:</strong> ' . $_SERVER['DOCUMENT_ROOT'] . '</p>';
        echo '</div>';

        // File Structure
        echo '<h2>📁 File Structure</h2>';
        echo '<div class="file-list">';
        echo '<strong>Root Files:</strong>';
        echo '<ul>';
        $root_files = glob('*.php');
        foreach ($root_files as $file) {
            echo '<li>' . htmlspecialchars($file) . '</li>';
        }
        echo '</ul>';
        
        echo '<strong>User Folder:</strong>';
        if (is_dir('user')) {
            echo '<ul>';
            $user_files = glob('user/*.php');
            foreach ($user_files as $file) {
                echo '<li>' . htmlspecialchars($file) . '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p style="color: red;">Folder not found!</p>';
        }
        
        echo '<strong>Organizer Folder:</strong>';
        if (is_dir('organizer')) {
            echo '<ul>';
            $org_files = glob('organizer/*.php');
            foreach ($org_files as $file) {
                echo '<li>' . htmlspecialchars($file) . '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p style="color: red;">Folder not found!</p>';
        }
        
        echo '<strong>Includes Folder:</strong>';
        if (is_dir('includes')) {
            echo '<ul>';
            $inc_files = glob('includes/*.php');
            foreach ($inc_files as $file) {
                echo '<li>' . htmlspecialchars($file) . '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p style="color: red;">Folder not found!</p>';
        }
        echo '</div>';

        // Next Steps
        echo '<h2>🚀 Next Steps</h2>';
        echo '<div class="file-list">';
        if ($all_checks_pass) {
            echo '<ol>';
            echo '<li>✅ System verified - everything looks good!</li>';
            echo '<li>Test API connection: <a href="test-gemini-api.php">test-gemini-api.php</a></li>';
            echo '<li>Access application: <a href="index.php">index.php</a></li>';
            echo '<li>Create test accounts and explore features</li>';
            echo '</ol>';
        } else {
            echo '<ol>';
            echo '<li>❌ Fix errors listed above</li>';
            echo '<li>Import database.sql if tables are missing</li>';
            echo '<li>Check file structure matches documentation</li>';
            echo '<li>Run this verification again</li>';
            echo '</ol>';
        }
        echo '</div>';
        ?>

        <div style="text-align: center; margin-top: 30px;">
            <a href="index.php" class="btn">Go to Application</a>
            <a href="test-gemini-api.php" class="btn" style="background: #0066cc;">Test API</a>
            <a href="javascript:location.reload()" class="btn" style="background: #28a745;">Refresh Check</a>
        </div>

        <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 5px; text-align: center;">
            <p style="color: #666; margin: 0;">
                <strong>Documentation:</strong> 
                <a href="README.md" style="color: #0066cc;">README</a> | 
                <a href="INSTALLATION_GUIDE.md" style="color: #0066cc;">Installation</a> | 
                <a href="QUICK_START_GUIDE.md" style="color: #0066cc;">Quick Start</a>
            </p>
        </div>
    </div>
</body>
</html>

