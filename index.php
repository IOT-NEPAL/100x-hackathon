<?php
// Start session and check if user is logged in
require_once 'db_config.php';

$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$user = null;

if ($isLoggedIn) {
    try {
        $stmt = $pdo->prepare("SELECT id, name, email, profile_pic, role FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
    } catch (PDOException $e) {
        // If there's an error, just treat as not logged in
        $isLoggedIn = false;
    }
}

// Fetch featured opportunities (latest 6 active opportunities)
try {
    $featured_stmt = $pdo->prepare("
        SELECT o.*, u.name as organizer_name, u.org_name 
        FROM opportunities o 
        LEFT JOIN users u ON o.organizer_id = u.id 
        WHERE o.is_active = 1 
        ORDER BY o.date_posted DESC 
        LIMIT 6
    ");
    $featured_stmt->execute();
    $featured_opportunities = $featured_stmt->fetchAll();
} catch (PDOException $e) {
    $featured_opportunities = [];
}

// Get statistics
try {
    $stats_jobs = $pdo->query("SELECT COUNT(*) FROM opportunities WHERE is_active = 1 AND type = 'employment'")->fetchColumn();
    $stats_internships = $pdo->query("SELECT COUNT(*) FROM opportunities WHERE is_active = 1 AND type = 'internship'")->fetchColumn();
    $stats_organizations = $pdo->query("SELECT COUNT(DISTINCT organizer_id) FROM opportunities WHERE is_active = 1")->fetchColumn();
} catch (PDOException $e) {
    $stats_jobs = 500;
    $stats_internships = 200;
    $stats_organizations = 150;
}

// Get job categories with counts
try {
    $categories_stmt = $pdo->query("
        SELECT 
            CASE 
                WHEN title LIKE '%software%' OR title LIKE '%developer%' OR title LIKE '%engineer%' THEN 'Technology'
                WHEN title LIKE '%design%' OR title LIKE '%UI%' OR title LIKE '%UX%' THEN 'Design'
                WHEN title LIKE '%market%' OR title LIKE '%sales%' THEN 'Marketing'
                WHEN title LIKE '%account%' OR title LIKE '%finance%' THEN 'Finance'
                WHEN title LIKE '%HR%' OR title LIKE '%human%' THEN 'Human Resources'
                WHEN title LIKE '%teach%' OR title LIKE '%educat%' THEN 'Education'
                ELSE 'Other'
            END as category,
            COUNT(*) as count
        FROM opportunities
        WHERE is_active = 1
        GROUP BY category
        ORDER BY count DESC
        LIMIT 6
    ");
    $job_categories = $categories_stmt->fetchAll();
} catch (PDOException $e) {
    $job_categories = [];
}

// Fetch jobs for scrolling section
try {
    $scrolling_jobs_stmt = $pdo->prepare("
        SELECT 
            o.id,
            o.title,
            o.type,
            o.location,
            COALESCE(u.org_name, u.name) as company,
            CASE 
                WHEN o.title LIKE '%software%' OR o.title LIKE '%developer%' OR o.title LIKE '%engineer%' OR o.title LIKE '%programming%' OR o.title LIKE '%tech%' OR o.title LIKE '%IT%' THEN 'Technology'
                WHEN o.title LIKE '%design%' OR o.title LIKE '%UI%' OR o.title LIKE '%UX%' OR o.title LIKE '%graphic%' THEN 'Design'
                WHEN o.title LIKE '%market%' OR o.title LIKE '%sales%' OR o.title LIKE '%marketing%' OR o.title LIKE '%SEO%' OR o.title LIKE '%content%' THEN 'Marketing'
                WHEN o.title LIKE '%account%' OR o.title LIKE '%finance%' OR o.title LIKE '%accounting%' OR o.title LIKE '%tax%' THEN 'Finance'
                WHEN o.title LIKE '%HR%' OR o.title LIKE '%human%' OR o.title LIKE '%recruiter%' OR o.title LIKE '%talent%' THEN 'Human Resources'
                WHEN o.title LIKE '%teach%' OR o.title LIKE '%educat%' OR o.title LIKE '%instructor%' OR o.title LIKE '%curriculum%' THEN 'Education'
                WHEN o.title LIKE '%business%' OR o.title LIKE '%manager%' OR o.title LIKE '%analyst%' THEN 'Business'
                ELSE 'Other'
            END as category
        FROM opportunities o
        LEFT JOIN users u ON o.organizer_id = u.id
        WHERE o.is_active = 1
        ORDER BY o.date_posted DESC
        LIMIT 30
    ");
    $scrolling_jobs_stmt->execute();
    $scrolling_jobs = $scrolling_jobs_stmt->fetchAll();
    
    // Split jobs into two rows for scrolling
    $scrolling_jobs_count = count($scrolling_jobs);
    if ($scrolling_jobs_count > 0) {
        // Split into two groups - first half and second half
        $midpoint = ceil($scrolling_jobs_count / 2);
        $row1_jobs_db = array_slice($scrolling_jobs, 0, $midpoint);
        $row2_jobs_db = array_slice($scrolling_jobs, $midpoint);
        
        // If we have fewer than 5 jobs in a row, duplicate to ensure smooth scrolling
        while (count($row1_jobs_db) < 5 && count($scrolling_jobs) > 0) {
            $row1_jobs_db = array_merge($row1_jobs_db, $scrolling_jobs);
        }
        while (count($row2_jobs_db) < 5 && count($scrolling_jobs) > 0) {
            $row2_jobs_db = array_merge($row2_jobs_db, $scrolling_jobs);
        }
    } else {
        $row1_jobs_db = [];
        $row2_jobs_db = [];
    }
} catch (PDOException $e) {
    $row1_jobs_db = [];
    $row2_jobs_db = [];
}

// Helper function to format job type and generate salary
function formatJobDisplay($job) {
    $company = !empty($job['company']) ? $job['company'] : 'Company';
    $category = !empty($job['category']) ? $job['category'] : 'Other';
    $position = !empty($job['title']) ? $job['title'] : 'Position';
    $location = !empty($job['location']) ? $job['location'] : 'Location Not Specified';
    $type = !empty($job['type']) ? $job['type'] : 'employment';
    $job_id = $job['id'] ?? 0;
    
    // Generate consistent salary based on job ID (using hash for determinism)
    // Use crc32 hash to generate consistent pseudo-random numbers
    $hash1 = crc32($job_id . $position);
    $hash2 = crc32($job_id . $position . 'max');
    
    // Format salary based on type and category (estimated for display)
    $salary = '';
    if ($type === 'internship') {
        $min = 10000 + (abs($hash1) % 15000);
        $max = 25000 + (abs($hash2) % 15000);
        $salary = 'Rs. ' . number_format($min) . '–' . number_format($max) . '/month · Internship';
    } else {
        // Employment - vary by category
        if (stripos($category, 'Technology') !== false || stripos($category, 'Developer') !== false || stripos($category, 'Engineer') !== false) {
            $min = 40000 + (abs($hash1) % 20000);
            $max = 80000 + (abs($hash2) % 70000);
            $salary = 'Rs. ' . number_format($min) . '–' . number_format($max) . '/month · Full-time job';
        } elseif (stripos($category, 'Marketing') !== false || stripos($category, 'Sales') !== false) {
            $min = 30000 + (abs($hash1) % 20000);
            $max = 60000 + (abs($hash2) % 40000);
            $salary = 'Rs. ' . number_format($min) . '–' . number_format($max) . '/month · Full-time job';
        } elseif (stripos($category, 'Finance') !== false || stripos($category, 'Accounting') !== false) {
            $min = 35000 + (abs($hash1) % 20000);
            $max = 70000 + (abs($hash2) % 50000);
            $salary = 'Rs. ' . number_format($min) . '–' . number_format($max) . '/month · Full-time job';
        } else {
            $min = 25000 + (abs($hash1) % 20000);
            $max = 50000 + (abs($hash2) % 40000);
            $salary = 'Rs. ' . number_format($min) . '–' . number_format($max) . '/month · Full-time job';
        }
    }
    
    return [
        'company' => $company,
        'category' => $category,
        'position' => $position,
        'salary' => $salary,
        'location' => $location,
        'id' => $job_id
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avsar Nepal - Your Skills Deserve the Right Opportunity</title>
    <meta name="description" content="Avsar Nepal connects talented individuals with verified organizations through AI-powered job matching and personalized career guidance.">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        darkMode: 'class',
        theme: {
          extend: {
            colors: {
              'avsar-blue': '#10b981',
              'avsar-navy': '#047857',
              'avsar-cyan': '#6ee7b7',
              'avsar-slate': '#64748b',
              'avsar-dark': '#0f172a'
            },
            fontFamily: {
              'poppins': ['Poppins', 'sans-serif'],
              'inter': ['Inter', 'sans-serif']
            }
          }
        }
      }
    </script>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- GSAP -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        /* Fade In Up Animation */
        @keyframes fadeInUp {
            0% {
                opacity: 0;
                transform: translateY(30px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        body {
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
            transition: background-color 0.3s ease, color 0.3s ease;
            line-height: 1.6;
        }
        
        body.dark {
            background-color: #0f172a;
            color: #e2e8f0;
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Poppins', sans-serif;
            line-height: 1.2;
        }

        /* Smooth Scrolling */
        html {
            scroll-behavior: smooth;
        }
        
        /* Focus States for Accessibility */
        a:focus-visible,
        button:focus-visible,
        input:focus-visible {
            outline: 2px solid #10b981;
            outline-offset: 2px;
            border-radius: 4px;
        }
        
        body.dark a:focus-visible,
        body.dark button:focus-visible,
        body.dark input:focus-visible {
            outline: 2px solid #6ee7b7;
            box-shadow: 0 0 12px rgba(110, 231, 183, 0.4);
        }
        
        /* Nepal Architecture Watermark */
        .nepal-watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 60vw;
            max-width: 800px;
            height: auto;
            opacity: 0.08;
            z-index: 0;
            pointer-events: none;
            user-select: none;
        }
        
        .nepal-watermark svg {
            width: 100%;
            height: auto;
            filter: drop-shadow(0 0 40px rgba(216, 224, 233, 0.3));
        }
        
        /* Nepal Cultural Image */
        .nepal-cultural-img {
            position: fixed;
            bottom: 0;
            right: 0;
            width: 40vw;
            max-width: 500px;
            height: auto;
            opacity: 0.12;
            z-index: 0;
            pointer-events: none;
            user-select: none;
        }
        
        body.dark .nepal-cultural-img {
            filter: drop-shadow(0 0 20px rgba(110, 231, 183, 0.3));
        }
        
        @media (max-width: 768px) {
            .nepal-cultural-img {
                width: 60vw;
                max-width: 300px;
            }
        }
        
        /* Ensure content stays above watermark */
        nav,
        section,
        footer {
            position: relative;
            z-index: 1;
        }
        
        /* Particle Background */
        .particle-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }
        
        .particle {
            position: absolute;
            width: 3px;
            height: 3px;
            background: rgba(16, 185, 129, 0.3);
            border-radius: 50%;
            animation: float 15s infinite ease-in-out;
        }
        
        body.dark .particle {
            background: rgba(110, 231, 183, 0.5);
            box-shadow: 0 0 10px rgba(110, 231, 183, 0.6);
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0) translateX(0); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(-100vh) translateX(50px); opacity: 0; }
        }
        
        /* Gradient Text */
        .gradient-text {
            background: linear-gradient(135deg, #10b981 0%, #047857 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        body.dark .gradient-text {
            background: linear-gradient(135deg, #6ee7b7 0%, #34d399 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            filter: drop-shadow(0 0 8px rgba(110, 231, 183, 0.4));
        }
        
        /* Glassmorphism */
        .glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        body.dark .glass {
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        /* Counter Animation */
        .counter {
            font-size: 2.5rem;
            font-weight: 700;
            color: #10b981;
            background: linear-gradient(135deg, #10b981 0%, #047857 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        body.dark .counter {
            background: linear-gradient(135deg, #6ee7b7 0%, #34d399 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            filter: drop-shadow(0 0 12px rgba(110, 231, 183, 0.5));
        }
        
        /* Hover Card Effect */
        .hover-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .hover-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(16, 185, 129, 0.15);
        }
        
        body.dark .hover-card:hover {
            box-shadow: 0 12px 24px rgba(110, 231, 183, 0.25), 0 0 20px rgba(110, 231, 183, 0.15);
        }
        
        @media (prefers-reduced-motion: reduce) {
            .hover-card:hover {
                transform: none;
            }
        }
        
        /* Gradient Border */
        .gradient-border {
            position: relative;
            background: white;
            border-radius: 1rem;
        }
        
        body.dark .gradient-border {
            background: #1e293b;
        }
        
        .gradient-border::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 1rem;
            padding: 2px;
            background: linear-gradient(135deg, #10b981, #047857);
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            mask-composite: exclude;
        }
        
        body.dark .gradient-border::before {
            background: linear-gradient(135deg, #6ee7b7, #34d399);
            filter: drop-shadow(0 0 8px rgba(110, 231, 183, 0.3));
        }
        
        /* Navbar Scroll Effect */
        .navbar-scrolled {
            background: rgba(255, 255, 255, 0.98) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
        }
        
        body.dark .navbar-scrolled {
            background: rgba(15, 23, 42, 0.98) !important;
            border-bottom: 1px solid rgba(30, 41, 59, 0.8);
        }
        
        nav {
            transition: all 0.2s ease;
        }
        
        /* Search Bar Glow */
        .search-glow:focus-within {
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1), 0 8px 24px rgba(16, 185, 129, 0.15);
        }
        
        body.dark .search-glow:focus-within {
            box-shadow: 0 0 0 3px rgba(110, 231, 183, 0.2), 0 8px 24px rgba(110, 231, 183, 0.25), 0 0 30px rgba(110, 231, 183, 0.2);
        }
        
        /* Input Fields */
        input[type="text"],
        input[type="email"],
        input[type="password"],
        textarea,
        select {
            transition: all 0.2s ease;
        }
        
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        textarea:focus,
        select:focus {
            outline: none;
            box-shadow: 0 0 0 2px #10b981;
        }
        
        body.dark input[type="text"]:focus,
        body.dark input[type="email"]:focus,
        body.dark input[type="password"]:focus,
        body.dark textarea:focus,
        body.dark select:focus {
            box-shadow: 0 0 0 2px #6ee7b7, 0 0 15px rgba(110, 231, 183, 0.3);
        }
        
        /* Button Improvements */
        button,
        .btn-ripple {
            transition: all 0.2s ease;
        }
        
        button:hover,
        .btn-ripple:hover {
            transform: translateY(-1px);
        }
        
        button:active,
        .btn-ripple:active {
            transform: translateY(0);
        }
        
        @media (prefers-reduced-motion: reduce) {
            button:hover,
            .btn-ripple:hover {
                transform: none;
            }
        }
        
        /* Testimonial Card */
        .testimonial-card {
            background: white;
            border-radius: 1.5rem;
            padding: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
        }
        
        .testimonial-card::after {
            content: '"';
            position: absolute;
            top: 1rem;
            right: 1.5rem;
            font-size: 6rem;
            font-family: Georgia, serif;
            color: rgba(16, 185, 129, 0.06);
            line-height: 1;
            pointer-events: none;
        }
        
        body.dark .testimonial-card {
            background: #1e293b;
        }
        
        body.dark .testimonial-card::after {
            color: rgba(110, 231, 183, 0.1);
        }
        
        /* Job Card Hover */
        .job-card-modern {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid #e2e8f0;
            position: relative;
            overflow: hidden;
        }
        
        .job-card-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(16, 185, 129, 0.05), transparent);
            transition: left 0.6s ease;
        }
        
        .job-card-modern:hover::before {
            left: 100%;
        }
        
        body.dark .job-card-modern {
            border-color: #334155;
        }
        
        .job-card-modern:hover {
            border-color: #10b981;
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px rgba(16, 185, 129, 0.15);
        }
        
        body.dark .job-card-modern:hover {
            border-color: #6ee7b7;
            box-shadow: 0 20px 40px rgba(110, 231, 183, 0.3), 0 0 30px rgba(110, 231, 183, 0.25);
        }
        
        /* Testimonial Card */
        .testimonial-card {
            background: white;
            border-radius: 1.5rem;
            padding: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
        }
        
        .testimonial-card::after {
            content: '"';
            position: absolute;
            top: 1rem;
            right: 1.5rem;
            font-size: 6rem;
            font-family: Georgia, serif;
            color: rgba(16, 185, 129, 0.06);
            line-height: 1;
            pointer-events: none;
        }
        
        body.dark .testimonial-card {
            background: #1e293b;
        }
        
        body.dark .testimonial-card::after {
            color: rgba(110, 231, 183, 0.1);
        }
        
        .testimonial-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(16, 185, 129, 0.15);
        }
        
        body.dark .testimonial-card:hover {
            box-shadow: 0 12px 24px rgba(110, 231, 183, 0.25), 0 0 25px rgba(110, 231, 183, 0.2);
        }
        
        @media (prefers-reduced-motion: reduce) {
            .testimonial-card:hover {
                transform: none;
            }
        }
        
        /* Wave Animation */
        .wave {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            overflow: hidden;
            line-height: 0;
        }
        
        .wave svg {
            position: relative;
            display: block;
            width: calc(100% + 1.3px);
            height: 80px;
        }
        
        /* Job Card Hover */
        .job-card-modern {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid #e2e8f0;
            position: relative;
            overflow: hidden;
        }
        
        .job-card-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(16, 185, 129, 0.05), transparent);
            transition: left 0.6s ease;
        }
        
        .job-card-modern:hover::before {
            left: 100%;
        }
        
        body.dark .job-card-modern {
            border-color: #334155;
        }
        
        .job-card-modern:hover {
            border-color: #10b981;
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(16, 185, 129, 0.12);
        }
        
        body.dark .job-card-modern:hover {
            border-color: #6ee7b7;
            box-shadow: 0 8px 16px rgba(110, 231, 183, 0.25), 0 0 25px rgba(110, 231, 183, 0.2);
        }
        
        @media (prefers-reduced-motion: reduce) {
            .job-card-modern:hover {
                transform: none;
            }
        }
        
        /* Feature Icon Animation */
        .feature-icon {
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
        }
        
        .feature-icon::before {
            content: '';
            position: absolute;
            inset: -4px;
            background: inherit;
            border-radius: inherit;
            filter: blur(12px);
            opacity: 0;
            transition: opacity 0.4s ease;
        }
        
        .feature-card:hover .feature-icon {
            transform: scale(1.15) rotate(8deg) translateY(-4px);
        }
        
        .feature-card:hover .feature-icon::before {
            opacity: 0.6;
        }
        
        /* Dark Mode Toggle */
        .theme-switch {
            position: relative;
            width: 60px;
            height: 30px;
            background: #cbd5e1;
            border-radius: 30px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        
        .theme-switch.active {
            background: #10b981;
        }
        
        body.dark .theme-switch.active {
            background: #6ee7b7;
            box-shadow: 0 0 15px rgba(110, 231, 183, 0.6);
        }
        
        .theme-switch-slider {
            position: absolute;
            top: 3px;
            left: 3px;
            width: 24px;
            height: 24px;
            background: white;
            border-radius: 50%;
            transition: transform 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .theme-switch.active .theme-switch-slider {
            transform: translateX(30px);
        }
        
        /* Scroll Reveal Animation */
        .reveal {
            opacity: 0;
            transform: translateY(40px);
            transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }
        
        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Floating Animation */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .float-animation {
            animation: float 3s ease-in-out infinite;
        }
        
        /* Pulse Animation */
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.8; }
        }
        
        .pulse-animation {
            animation: pulse 2s ease-in-out infinite;
        }
        
        /* Shine Effect */
        @keyframes shine {
            0% { background-position: -200% center; }
            100% { background-position: 200% center; }
        }
        
        .shine-effect {
            background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.3) 50%, transparent 100%);
            background-size: 200% 100%;
            animation: shine 3s ease-in-out infinite;
        }
        
        /* Button Ripple Effect */
        .btn-ripple {
            position: relative;
            overflow: hidden;
        }
        
        .btn-ripple::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .btn-ripple:hover::after {
            width: 300px;
            height: 300px;
        }
        
        /* Jobs Scrolling Section */
        @keyframes scrollLeft {
            0% {
                transform: translate3d(0, 0, 0);
            }
            100% {
                transform: translate3d(-50%, 0, 0);
            }
        }
        
        @keyframes scrollRight {
            0% {
                transform: translate3d(-50%, 0, 0);
            }
            100% {
                transform: translate3d(0, 0, 0);
            }
        }
        
        .jobs-scroll-section {
            background: linear-gradient(180deg, #050a0f 0%, #08141a 30%, #0a2a2a 70%, #0d2530 100%);
            padding: 6rem 0 8rem;
            position: relative;
            overflow: hidden;
            width: 100%;
        }
        
        .jobs-scroll-title {
            font-size: 3rem;
            font-weight: 700;
            color: #fff;
            text-align: center;
            margin-bottom: 4rem;
            padding: 0 2rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
            position: relative;
            z-index: 10;
            animation: fadeInUp 1s ease-out;
        }
        
        .jobs-row {
            display: block;
            overflow: hidden;
            margin-bottom: 1.5rem;
            position: relative;
        }
        
        .jobs-track {
            display: flex;
            flex-direction: row;
            gap: 1.5rem;
            width: fit-content;
            min-width: 200%;
        }
        
        .jobs-track-left {
            animation: scrollLeft 40s linear infinite;
            transform: translate3d(0, 0, 0);
            will-change: transform;
        }
        
        .jobs-track-left:hover {
            animation-play-state: paused;
        }
        
        .jobs-track-right {
            animation: scrollRight 40s linear infinite;
            transform: translate3d(-50%, 0, 0);
            will-change: transform;
        }
        
        .jobs-track-right:hover {
            animation-play-state: paused;
        }
        
        .job-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
        }
        
        /* Responsive Styles */
        @media (max-width: 768px) {
            .jobs-scroll-title {
                font-size: 2rem;
            }
            
            .job-card {
                width: 280px !important;
                max-width: 280px !important;
            }
            
            .jobs-track-left {
                animation-duration: 30s;
            }
            
            .jobs-track-right {
                animation-duration: 30s;
            }
        }
        
        @media (max-width: 480px) {
            .jobs-scroll-section {
                padding: 4rem 0 6rem;
            }
            
            .jobs-scroll-title {
                font-size: 1.75rem;
                padding: 0 1rem;
                margin-bottom: 2rem;
            }
            
            .job-card {
                width: 260px !important;
                max-width: 260px !important;
                padding: 1.25rem !important;
            }
            
            .job-position {
                font-size: 16px !important;
                min-height: 45px !important;
            }
            
            .jobs-track-left {
                animation-duration: 25s;
            }
            
            .jobs-track-right {
                animation-duration: 25s;
            }
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-avsar-dark text-gray-900 dark:text-gray-100">
    <!-- Nepal Architecture Watermark -->
    <div class="nepal-watermark" aria-hidden="true">
        <svg viewBox="0 0 800 800" xmlns="http://www.w3.org/2000/svg">
            <!-- Stylized Nepalese Pagoda Temple -->
            <g fill="#D8E0E9" opacity="1">
                <!-- Base Platform -->
                <rect x="250" y="600" width="300" height="20" rx="2"/>
                <rect x="270" y="620" width="260" height="15" rx="2"/>
                
                <!-- First Tier -->
                <rect x="300" y="520" width="200" height="80" rx="3"/>
                <polygon points="280,520 520,520 550,480 250,480"/>
                
                <!-- Second Tier -->
                <rect x="330" y="440" width="140" height="60" rx="3"/>
                <polygon points="310,440 490,440 515,405 285,405"/>
                
                <!-- Third Tier -->
                <rect x="360" y="370" width="80" height="50" rx="3"/>
                <polygon points="340,370 460,370 480,340 320,340"/>
                
                <!-- Top Spire -->
                <rect x="385" y="280" width="30" height="60" rx="2"/>
                <polygon points="400,280 415,240 385,240"/>
                
                <!-- Pinnacle -->
                <circle cx="400" cy="230" r="8"/>
                <polygon points="400,220 405,200 395,200"/>
                
                <!-- Decorative Bells (Left) -->
                <circle cx="260" cy="510" r="6"/>
                <circle cx="290" cy="430" r="5"/>
                <circle cx="330" cy="360" r="4"/>
                
                <!-- Decorative Bells (Right) -->
                <circle cx="540" cy="510" r="6"/>
                <circle cx="510" cy="430" r="5"/>
                <circle cx="470" cy="360" r="4"/>
                
                <!-- Roof Details -->
                <line x1="250" y1="480" x2="550" y2="480" stroke="#D8E0E9" stroke-width="2" opacity="0.6"/>
                <line x1="285" y1="405" x2="515" y2="405" stroke="#D8E0E9" stroke-width="2" opacity="0.6"/>
                <line x1="320" y1="340" x2="480" y2="340" stroke="#D8E0E9" stroke-width="2" opacity="0.6"/>
            </g>
        </svg>
    </div>
    
    <!-- Nepal Cultural Image -->
    <img src="Adobe Express - file.png" alt="" class="nepal-cultural-img" aria-hidden="true">
    
    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 transition-all duration-200" id="navbar" role="navigation" aria-label="Main navigation" x-data="{ mobileMenuOpen: false, scrolled: false }" @scroll.window="scrolled = window.pageYOffset > 10" :class="{ 'navbar-scrolled': scrolled }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 lg:h-20">
                <!-- Logo -->
                <div class="flex-shrink-0">
                    <a href="index.php" class="flex items-center space-x-2 group">
                        <img src="loogo.png" alt="Avsar Nepal" class="h-10 w-auto transition-transform duration-300 group-hover:scale-105">
                        <span class="text-2xl font-bold gradient-text hidden sm:block">Avsar Nepal</span>
                    </a>
                </div>
                
                <!-- Desktop Menu -->
                <div class="hidden lg:flex items-center space-x-1">
                    <a href="index.php" class="px-4 py-2 rounded-lg text-gray-700 dark:text-gray-300 hover:text-avsar-blue dark:hover:text-avsar-cyan hover:bg-gray-100 dark:hover:bg-gray-800 transition-all duration-300">Home</a>
                    <a href="students.php" class="px-4 py-2 rounded-lg text-gray-700 dark:text-gray-300 hover:text-avsar-blue dark:hover:text-avsar-cyan hover:bg-gray-100 dark:hover:bg-gray-800 transition-all duration-300">Students</a>
                    <a href="employers.php" class="px-4 py-2 rounded-lg text-gray-700 dark:text-gray-300 hover:text-avsar-blue dark:hover:text-avsar-cyan hover:bg-gray-100 dark:hover:bg-gray-800 transition-all duration-300">Employer</a>
                    <?php if ($isLoggedIn && $user): ?>
                    <a href="user/career-guidance-ai.php" class="px-4 py-2 rounded-lg text-gray-700 dark:text-gray-300 hover:text-avsar-blue dark:hover:text-avsar-cyan hover:bg-gray-100 dark:hover:bg-gray-800 transition-all duration-300">Career Guide</a>
                    <?php endif; ?>
                </div>
                
                <!-- Right Side Actions -->
                <div class="flex items-center space-x-4">
                    <!-- Dark Mode Toggle -->
                    <button onclick="toggleDarkMode()" class="theme-switch hidden lg:block" id="themeSwitch">
                        <div class="theme-switch-slider"></div>
                    </button>
                    
                    <!-- Mobile Menu Button -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden p-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" x-show="!mobileMenuOpen" d="M4 6h16M4 12h16M4 18h16"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" x-show="mobileMenuOpen" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                    
                    <!-- Desktop Auth Buttons -->
                    <div class="hidden lg:flex items-center space-x-3">
                        <?php if ($isLoggedIn && $user): ?>
                        <!-- User Profile Dropdown -->
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="flex items-center space-x-2 px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-all duration-300">
                                <?php if ($user['profile_pic']): ?>
                                    <img src="<?php echo htmlspecialchars($user['profile_pic']); ?>" 
                                         alt="<?php echo htmlspecialchars($user['name']); ?>" 
                                         class="w-8 h-8 rounded-full object-cover border-2 border-avsar-blue">
                                <?php else: ?>
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-avsar-blue to-avsar-cyan flex items-center justify-center text-white font-semibold text-sm">
                                        <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                                <span class="text-gray-700 dark:text-gray-300 font-medium"><?php echo htmlspecialchars($user['name']); ?></span>
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            
                            <!-- Dropdown Menu -->
                            <div x-show="open" 
                                 @click.away="open = false"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-xl shadow-xl py-2 z-50 border border-gray-200 dark:border-gray-700"
                                 style="display: none;">
                                <?php
                                // Determine dashboard link based on role
                                $dashboardLink = 'user/user-dashboard.php';
                                if ($user['role'] === 'admin') {
                                    $dashboardLink = 'admin/admin-dashboard.php';
                                } elseif ($user['role'] === 'organizer') {
                                    $dashboardLink = 'organizer/organizer-dashboard.php';
                                } elseif ($user['role'] === 'career_centre') {
                                    $dashboardLink = 'career_centre/career-centre-dashboard.php';
                                }
                                ?>
                                <a href="<?php echo $dashboardLink; ?>" class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <i class="fas fa-tachometer-alt w-5 text-avsar-blue"></i>
                                    <span class="ml-3">Dashboard</span>
                                </a>
                                <a href="profile.php" class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <i class="fas fa-user w-5 text-avsar-blue"></i>
                                    <span class="ml-3">Profile</span>
                                </a>
                                <hr class="my-2 border-gray-200 dark:border-gray-700">
                                <a href="logout.php" class="flex items-center px-4 py-3 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                    <i class="fas fa-sign-out-alt w-5"></i>
                                    <span class="ml-3">Logout</span>
                                </a>
                            </div>
                        </div>
                        <?php else: ?>
                        <a href="signin.php" class="px-5 py-2 text-avsar-blue dark:text-avsar-cyan font-medium hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-all duration-300">Sign up</a>
                        <a href="login.php" class="px-5 py-2 bg-gradient-to-r from-avsar-blue to-avsar-cyan text-white font-medium rounded-lg hover:shadow-md transition-all duration-200">Log in</a>
                        <?php endif; ?>
                </div>
            </div>
        </div>
            
            <!-- Mobile Menu -->
            <div x-show="mobileMenuOpen" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-2"
                 class="lg:hidden bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800 z-50"
                 style="display: none;">
                <div class="px-4 py-4 space-y-1">
                    <a href="index.php" class="block px-4 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">Home</a>
                    <a href="students.php" class="block px-4 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">Students</a>
                    <a href="employers.php" class="block px-4 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">Employer</a>
                    <?php if ($isLoggedIn && $user): ?>
                    <a href="user/career-guidance-ai.php" class="block px-4 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">Career Guide</a>
                    <?php endif; ?>
                    
                    <div class="pt-4 mt-4 border-t border-gray-200 dark:border-gray-800 space-y-2">
                        <!-- Dark Mode Toggle Mobile -->
                        <div class="flex items-center justify-between px-4 py-3 bg-gray-100 dark:bg-gray-800 rounded-lg mb-3">
                            <span class="text-gray-700 dark:text-gray-300 font-medium">Dark Mode</span>
                            <button onclick="toggleDarkMode()" class="theme-switch" id="themeSwitchMobile">
                                <div class="theme-switch-slider"></div>
                            </button>
                        </div>
                        
                        <?php if ($isLoggedIn && $user): ?>
                        <div class="flex items-center gap-3 p-4 bg-gray-100 dark:bg-gray-800 rounded-lg mb-3">
                            <?php if ($user['profile_pic']): ?>
                                <img src="<?php echo htmlspecialchars($user['profile_pic']); ?>" 
                                     alt="<?php echo htmlspecialchars($user['name']); ?>" 
                                     class="w-14 h-14 rounded-full object-cover border-2 border-avsar-blue">
                            <?php else: ?>
                                <div class="w-14 h-14 rounded-full bg-gradient-to-br from-avsar-blue to-avsar-cyan flex items-center justify-center text-white font-bold text-xl">
                                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                            <div class="flex-1">
                                <div class="text-gray-900 dark:text-white font-semibold"><?php echo htmlspecialchars($user['name']); ?></div>
                                <div class="text-gray-500 dark:text-gray-400 text-sm"><?php echo htmlspecialchars($user['email']); ?></div>
                            </div>
                        </div>
                        <?php
                        $dashboardLink = 'user/user-dashboard.php';
                        if ($user['role'] === 'admin') {
                            $dashboardLink = 'admin/admin-dashboard.php';
                        } elseif ($user['role'] === 'organizer') {
                            $dashboardLink = 'organizer/organizer-dashboard.php';
                        } elseif ($user['role'] === 'career_centre') {
                            $dashboardLink = 'career_centre/career-centre-dashboard.php';
                        }
                        ?>
                        <a href="<?php echo $dashboardLink; ?>" class="block w-full px-5 py-3 text-center bg-avsar-blue text-white font-medium rounded-lg hover:bg-avsar-navy transition-colors">Dashboard</a>
                        <a href="profile.php" class="block w-full px-5 py-3 text-center border-2 border-avsar-blue text-avsar-blue dark:text-avsar-cyan font-medium rounded-lg hover:bg-avsar-blue hover:text-white transition-colors">Profile</a>
                        <a href="logout.php" class="block w-full px-5 py-3 text-center bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition-colors">Logout</a>
                        <?php else: ?>
                        <a href="signin.php" class="block w-full px-5 py-3 text-center border-2 border-avsar-blue text-avsar-blue dark:text-avsar-cyan font-medium rounded-lg hover:bg-avsar-blue hover:text-white transition-colors">Sign up</a>
                        <a href="login.php" class="block w-full px-5 py-3 text-center bg-gradient-to-r from-avsar-blue to-avsar-cyan text-white font-medium rounded-lg hover:shadow-md transition-all duration-200">Log in</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative min-h-screen flex items-center justify-center overflow-hidden pt-16 lg:pt-20" style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 50%, #dbeafe 100%);">
        <div class="dark:bg-gradient-to-br dark:from-avsar-dark dark:via-gray-900 dark:to-avsar-navy absolute inset-0 transition-colors duration-300"></div>
        
        <!-- Particle Background -->
        <div class="particle-bg" id="particleContainer"></div>
        
        <!-- Background Hero Image -->
        <div class="absolute inset-0 z-0 overflow-hidden">
            <img src="hero-image.png" 
                 alt="" 
                 class="absolute inset-0 w-full h-full opacity-20 dark:opacity-10 object-cover pointer-events-none"
                 aria-hidden="true">
        </div>
        
        <!-- Content -->
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-20 text-center">
            <h1 class="text-5xl sm:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-6 reveal leading-tight" style="font-family: 'Poppins', sans-serif; line-height: 1.3;">
                <span class="inline-block" style="animation: fadeInUp 0.8s ease-out 0s backwards;">Your skills deserve</span><br>
                <span class="gradient-text inline-block" style="animation: fadeInUp 0.8s ease-out 0.2s backwards;">the right opportunity</span>
            </h1>
            
            <p class="text-xl sm:text-2xl text-gray-600 dark:text-gray-300 mb-12 max-w-3xl mx-auto reveal" style="animation-delay: 0.1s;">
                Avsar Nepal connects you there.
            </p>
            
            <!-- Search Bar -->
            <div class="max-w-5xl mx-auto mb-16 reveal" style="animation-delay: 0.2s;">
                <form action="opportunities.php" method="GET" class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-4 md:p-5 search-glow transition-all duration-300 relative" role="search" aria-label="Job search">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 md:gap-4">
                        <div class="md:col-span-5">
                            <label for="job-search" class="sr-only">Job title or keyword</label>
                            <div class="relative">
                                <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400" aria-hidden="true"></i>
                                <input type="text" 
                                       id="job-search"
                                       name="search" 
                                       placeholder="Job title or keyword"
                                       autocomplete="off"
                                       class="w-full pl-12 pr-4 py-2.5 md:py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-avsar-blue focus:border-avsar-blue focus:bg-white dark:focus:bg-gray-600 transition-all">
                            </div>
                        </div>
                        <div class="md:col-span-5">
                            <label for="location-search" class="sr-only">Location</label>
                            <div class="relative">
                                <i class="fas fa-map-marker-alt absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400" aria-hidden="true"></i>
                                <input type="text" 
                                       id="location-search"
                                       name="location" 
                                       placeholder="Location"
                                       autocomplete="off"
                                       class="w-full pl-12 pr-4 py-2.5 md:py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-avsar-blue focus:border-avsar-blue focus:bg-white dark:focus:bg-gray-600 transition-all">
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <button type="submit" class="btn-ripple w-full py-2.5 md:py-3 px-6 bg-gradient-to-r from-avsar-blue to-avsar-cyan text-white font-semibold rounded-xl hover:shadow-lg transition-all duration-200 relative overflow-hidden group" aria-label="Search for jobs">
                                <span class="relative z-10">Search</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Animated Counters -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-8 md:gap-12 max-w-5xl mx-auto reveal" style="animation-delay: 0.3s;">
                <div class="text-center">
                    <div class="counter text-5xl md:text-6xl font-bold mb-2" data-target="<?php echo max($stats_jobs, 10); ?>" style="background: linear-gradient(135deg, #10b981 0%, #06b6d4 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; filter: drop-shadow(0 0 20px rgba(16, 185, 129, 0.4));"><?php echo max($stats_jobs, 10); ?>+</div>
                    <p class="text-gray-700 dark:text-gray-300 font-medium text-base md:text-lg">Active Jobs</p>
                </div>
                <div class="text-center">
                    <div class="counter text-5xl md:text-6xl font-bold mb-2" data-target="<?php echo max($stats_internships, 5); ?>" style="background: linear-gradient(135deg, #06b6d4 0%, #0ea5e9 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; filter: drop-shadow(0 0 20px rgba(6, 182, 212, 0.4));"><?php echo max($stats_internships, 5); ?>+</div>
                    <p class="text-gray-700 dark:text-gray-300 font-medium text-base md:text-lg">Internships</p>
                </div>
                <div class="text-center">
                    <div class="counter text-5xl md:text-6xl font-bold mb-2" data-target="<?php echo max($stats_organizations, 5); ?>" style="background: linear-gradient(135deg, #14b8a6 0%, #2dd4bf 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; filter: drop-shadow(0 0 20px rgba(20, 184, 166, 0.4));"><?php echo max($stats_organizations, 5); ?>+</div>
                    <p class="text-gray-700 dark:text-gray-300 font-medium text-base md:text-lg">Partner Organizations</p>
                </div>
            </div>
        </div>
        
        <!-- Wave Divider -->
        <div class="absolute bottom-0 left-0 w-full overflow-hidden leading-none">
            <svg class="relative block w-full h-20 md:h-24 lg:h-32" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none" style="transform: rotate(180deg);">
                <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" 
                      class="fill-white dark:fill-avsar-dark transition-all duration-300"></path>
            </svg>
        </div>
    </section>
    
    <!-- Jobs and Internships Scrolling Section -->
    <section class="jobs-scroll-section" style="background: linear-gradient(180deg, #050a0f 0%, #08141a 30%, #0a2a2a 70%, #0d2530 100%); padding: 6rem 0 8rem; position: relative; overflow: hidden; width: 100%;">
        <div style="position: relative; z-index: 10;">
            <h2 class="jobs-scroll-title" style="font-size: 3rem; font-weight: 700; color: #fff; text-align: center; margin-bottom: 4rem; padding: 0 2rem; text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5); position: relative; z-index: 10; animation: fadeInUp 1s ease-out;">Jobs and internships you actually want</h2>
            
            <!-- Row 1: Scrolls Left -->
            <div class="jobs-row" style="display: block; overflow: hidden; margin-bottom: 1.5rem; position: relative;">
                <div class="jobs-track jobs-track-left" style="display: flex; flex-direction: row; gap: 1.5rem; width: fit-content; min-width: 200%;">
                    <?php 
                    if (!empty($row1_jobs_db)):
                        // Format jobs for display
                        $formatted_row1 = [];
                        foreach ($row1_jobs_db as $job) {
                            $formatted_row1[] = formatJobDisplay($job);
                        }
                        // Render original set twice for seamless loop
                        for ($i = 0; $i < 2; $i++):
                            foreach ($formatted_row1 as $job):
                    ?>
                    <a href="view-opportunity.php?id=<?php echo htmlspecialchars($job['id'] ?? '#'); ?>" class="job-card" style="background: #fff; border-radius: 16px; padding: 1.5rem; width: 340px; max-width: 340px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); flex-shrink: 0; cursor: pointer; will-change: transform; transition: transform 0.3s ease, box-shadow 0.3s ease; text-decoration: none; display: block; color: inherit;">
                        <div class="job-card-header" style="display: flex; flex-direction: row; gap: 1rem; margin-bottom: 1.25rem; align-items: flex-start;">
                            <div class="company-badge" style="flex-shrink: 0;">
                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($job['company']); ?>&background=10b981&color=fff&size=50" alt="<?php echo htmlspecialchars($job['company']); ?>" class="company-icon" style="width: 50px; height: 50px; border-radius: 8px; object-fit: cover; background: #f0f0f0; display: block;">
                            </div>
                            <div class="company-info" style="flex: 1; min-width: 0;">
                                <p class="company-name" style="font-size: 15px; font-weight: 600; color: #000; margin: 0 0 0.25rem 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; line-height: normal;"><?php echo htmlspecialchars($job['company']); ?></p>
                                <p class="company-category" style="font-size: 13px; font-weight: 400; color: #666; margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; line-height: normal;"><?php echo htmlspecialchars($job['category']); ?></p>
                            </div>
                        </div>
                        <h4 class="job-position" style="font-size: 18px; font-weight: 700; color: #000; margin: 0 0 0.75rem 0; line-height: 1.4; min-height: 50px; display: block;"><?php echo htmlspecialchars($job['position']); ?></h4>
                        <p class="job-salary" style="font-size: 14px; font-weight: 600; color: #333; margin: 0 0 0.5rem 0; display: block;"><?php echo htmlspecialchars($job['salary']); ?></p>
                        <p class="job-location" style="font-size: 14px; font-weight: 400; color: #666; margin: 0; display: block;"><?php echo htmlspecialchars($job['location']); ?></p>
                    </a>
                    <?php 
                            endforeach;
                        endfor;
                    else:
                        // Fallback: No jobs available message
                    ?>
                    <div class="job-card" style="background: #fff; border-radius: 16px; padding: 1.5rem; width: 340px; max-width: 340px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); flex-shrink: 0; text-align: center;">
                        <p style="color: #666; font-size: 14px;">No job opportunities available at the moment.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Row 2: Scrolls Right -->
            <div class="jobs-row" style="display: block; overflow: hidden; margin-bottom: 1.5rem; position: relative;">
                <div class="jobs-track jobs-track-right" style="display: flex; flex-direction: row; gap: 1.5rem; width: fit-content; min-width: 200%; transform: translate3d(-50%, 0, 0);">
                    <?php 
                    if (!empty($row2_jobs_db)):
                        // Format jobs for display
                        $formatted_row2 = [];
                        foreach ($row2_jobs_db as $job) {
                            $formatted_row2[] = formatJobDisplay($job);
                        }
                        // Render original set twice for seamless loop
                        for ($i = 0; $i < 2; $i++):
                            foreach ($formatted_row2 as $job):
                    ?>
                    <a href="view-opportunity.php?id=<?php echo htmlspecialchars($job['id'] ?? '#'); ?>" class="job-card" style="background: #fff; border-radius: 16px; padding: 1.5rem; width: 340px; max-width: 340px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); flex-shrink: 0; cursor: pointer; will-change: transform; transition: transform 0.3s ease, box-shadow 0.3s ease; text-decoration: none; display: block; color: inherit;">
                        <div class="job-card-header" style="display: flex; flex-direction: row; gap: 1rem; margin-bottom: 1.25rem; align-items: flex-start;">
                            <div class="company-badge" style="flex-shrink: 0;">
                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($job['company']); ?>&background=10b981&color=fff&size=50" alt="<?php echo htmlspecialchars($job['company']); ?>" class="company-icon" style="width: 50px; height: 50px; border-radius: 8px; object-fit: cover; background: #f0f0f0; display: block;">
                            </div>
                            <div class="company-info" style="flex: 1; min-width: 0;">
                                <p class="company-name" style="font-size: 15px; font-weight: 600; color: #000; margin: 0 0 0.25rem 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; line-height: normal;"><?php echo htmlspecialchars($job['company']); ?></p>
                                <p class="company-category" style="font-size: 13px; font-weight: 400; color: #666; margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; line-height: normal;"><?php echo htmlspecialchars($job['category']); ?></p>
                            </div>
                        </div>
                        <h4 class="job-position" style="font-size: 18px; font-weight: 700; color: #000; margin: 0 0 0.75rem 0; line-height: 1.4; min-height: 50px; display: block;"><?php echo htmlspecialchars($job['position']); ?></h4>
                        <p class="job-salary" style="font-size: 14px; font-weight: 600; color: #333; margin: 0 0 0.5rem 0; display: block;"><?php echo htmlspecialchars($job['salary']); ?></p>
                        <p class="job-location" style="font-size: 14px; font-weight: 400; color: #666; margin: 0; display: block;"><?php echo htmlspecialchars($job['location']); ?></p>
                    </a>
                    <?php 
                            endforeach;
                        endfor;
                    else:
                        // Fallback: No jobs available message
                    ?>
                    <div class="job-card" style="background: #fff; border-radius: 16px; padding: 1.5rem; width: 340px; max-width: 340px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); flex-shrink: 0; text-align: center;">
                        <p style="color: #666; font-size: 14px;">No job opportunities available at the moment.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Why Choose Avsar Nepal Section -->
    <section class="py-20 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-avsar-dark transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 reveal">
                <h2 class="text-4xl sm:text-5xl font-bold text-gray-900 dark:text-white mb-4" style="font-family: 'Poppins', sans-serif;">Why Choose Avsar Nepal?</h2>
                <p class="text-xl text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">Empowering your career journey with exceptional opportunities and expert guidance</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php
                $features = [
                    [
                        'icon' => 'fa-star',
                        'gradient' => 'from-yellow-400 to-orange-500',
                        'color' => 'yellow',
                        'title' => 'Get Your Dream Job for Just Rs. 99',
                        'description' => 'Unlock premium job opportunities at an affordable one-time cost.'
                    ],
                    [
                        'icon' => 'fa-building',
                        'gradient' => 'from-blue-500 to-cyan-500',
                        'color' => 'blue',
                        'title' => 'Connect with Top-Rated Companies',
                        'description' => 'Access verified employers from Nepal and beyond, trusted by professionals.'
                    ],
                    [
                        'icon' => 'fa-graduation-cap',
                        'gradient' => 'from-green-500 to-emerald-600',
                        'color' => 'green',
                        'title' => 'Start Earning from +2 Level',
                        'description' => 'Begin your career early and gain real-world experience while you learn.'
                    ],
                    [
                        'icon' => 'fa-user-tie',
                        'gradient' => 'from-purple-500 to-pink-500',
                        'color' => 'purple',
                        'title' => 'Expert Evaluation & Guidance',
                        'description' => 'Your skills are reviewed by industry experts to help you improve and stand out.'
                    ],
                    [
                        'icon' => 'fa-certificate',
                        'gradient' => 'from-indigo-500 to-purple-600',
                        'color' => 'indigo',
                        'title' => 'Certified by Avsar Nepal',
                        'description' => 'Gain recognized certification that strengthens your profile and credibility.'
                    ],
                    [
                        'icon' => 'fa-briefcase',
                        'gradient' => 'from-orange-500 to-red-500',
                        'color' => 'orange',
                        'title' => '100+ Opportunities in One Platform',
                        'description' => 'Explore internships, part-time jobs, and professional roles all in one place.'
                    ]
                ];
                
                $delay = 0.1;
                foreach ($features as $feature):
                ?>
                <div class="feature-card hover-card bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-lg reveal group" style="animation-delay: <?php echo $delay; ?>s;">
                    <div class="feature-icon w-16 h-16 bg-gradient-to-br <?php echo $feature['gradient']; ?> rounded-xl flex items-center justify-center mb-6 shadow-lg transform group-hover:scale-110 group-hover:rotate-6 transition-all duration-300">
                        <i class="fas <?php echo $feature['icon']; ?> text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3 transition-colors duration-300 group-hover:text-<?php echo $feature['color']; ?>-600 dark:group-hover:text-<?php echo $feature['color']; ?>-400" style="font-family: 'Poppins', sans-serif;">
                        <?php echo htmlspecialchars($feature['title']); ?>
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                        <?php echo htmlspecialchars($feature['description']); ?>
                    </p>
                </div>
                <?php 
                    $delay += 0.1;
                endforeach; 
                ?>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="bg-gradient-to-br from-gray-900 to-gray-800 dark:from-black dark:to-gray-900 text-white py-16 transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Footer Top -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8 mb-12">
                <!-- Brand Column -->
                <div class="lg:col-span-2">
                    <div class="flex items-center space-x-3 mb-4">
                        <img src="loogo.png" alt="Avsar Nepal" class="h-10 w-auto">
                        <span class="text-2xl font-bold gradient-text">Avsar Nepal</span>
                    </div>
                    <p class="text-gray-400 mb-6 max-w-sm">Empowering Nepali youth through meaningful opportunities. Connecting talent with the right career paths.</p>
                    <!-- Social Links -->
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 bg-gray-800 dark:bg-gray-700 rounded-lg flex items-center justify-center hover:bg-avsar-blue transition-colors duration-300">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 dark:bg-gray-700 rounded-lg flex items-center justify-center hover:bg-avsar-blue transition-colors duration-300">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 dark:bg-gray-700 rounded-lg flex items-center justify-center hover:bg-avsar-blue transition-colors duration-300">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 dark:bg-gray-700 rounded-lg flex items-center justify-center hover:bg-avsar-blue transition-colors duration-300">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>
                
                <!-- For Students -->
                <div>
                    <h4 class="text-lg font-semibold mb-4">For Students</h4>
                    <ul class="space-y-3">
                        <li><a href="opportunities.php" class="text-gray-400 hover:text-white transition-colors">Find Jobs</a></li>
                        <li><a href="opportunities.php?type=internship" class="text-gray-400 hover:text-white transition-colors">Internships</a></li>
                        <?php if ($isLoggedIn): ?>
                        <li><a href="user/career-guidance-ai.php" class="text-gray-400 hover:text-white transition-colors">Career Guidance</a></li>
                        <li><a href="user/my-skills.php" class="text-gray-400 hover:text-white transition-colors">My Skills</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <!-- For Employers -->
                <div>
                    <h4 class="text-lg font-semibold mb-4">For Employers</h4>
                    <ul class="space-y-3">
                        <li><a href="<?php echo $isLoggedIn ? 'organizer/add-opportunity.php' : 'login.php'; ?>" class="text-gray-400 hover:text-white transition-colors">Post a Job</a></li>
                        <li><a href="employers.php" class="text-gray-400 hover:text-white transition-colors">Hire Talent</a></li>
                        <li><a href="about.php" class="text-gray-400 hover:text-white transition-colors">Pricing</a></li>
                        <li><a href="employers.php" class="text-gray-400 hover:text-white transition-colors">Resources</a></li>
                    </ul>
                </div>
                
                <!-- Company -->
                <div>
                    <h4 class="text-lg font-semibold mb-4">Company</h4>
                    <ul class="space-y-3">
                        <li><a href="about.php" class="text-gray-400 hover:text-white transition-colors">About Us</a></li>
                        <li><a href="contact.php" class="text-gray-400 hover:text-white transition-colors">Contact</a></li>
                        <li><a href="#blog" class="text-gray-400 hover:text-white transition-colors">Blog</a></li>
                        <li><a href="#careers" class="text-gray-400 hover:text-white transition-colors">Careers</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Footer Bottom -->
            <div class="border-t border-gray-700 pt-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <p class="text-gray-400 text-sm mb-4 md:mb-0">&copy; <?php echo date('Y'); ?> Avsar Nepal. All rights reserved. Built by Team Elite</p>
                    <div class="flex flex-wrap justify-center md:justify-end gap-4 text-sm">
                        <a href="#privacy" class="text-gray-400 hover:text-white transition-colors">Privacy Policy</a>
                        <span class="text-gray-600">|</span>
                        <a href="#terms" class="text-gray-400 hover:text-white transition-colors">Terms of Service</a>
                        <span class="text-gray-600">|</span>
                        <a href="#cookies" class="text-gray-400 hover:text-white transition-colors">Cookie Policy</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- JavaScript -->
    <script>
        // Dark Mode Toggle
        function toggleDarkMode() {
            document.body.classList.toggle('dark');
            const isDark = document.body.classList.contains('dark');
            localStorage.setItem('darkMode', isDark ? 'enabled' : 'disabled');
            
            // Update toggle switches
            document.querySelectorAll('.theme-switch').forEach(toggle => {
                toggle.classList.toggle('active', isDark);
            });
        }
        
        // Load dark mode preference
        document.addEventListener('DOMContentLoaded', () => {
            const darkMode = localStorage.getItem('darkMode');
            if (darkMode === 'enabled') {
                document.body.classList.add('dark');
                document.querySelectorAll('.theme-switch').forEach(toggle => {
                    toggle.classList.add('active');
                });
            }
        });
        
        // Particle Background
        function createParticles() {
            const container = document.getElementById('particleContainer');
            if (!container) return;
            
            for (let i = 0; i < 50; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 15 + 's';
                particle.style.animationDuration = (15 + Math.random() * 10) + 's';
                container.appendChild(particle);
            }
        }
        
        // Counter Animation
        function animateCounter(element) {
            const target = parseInt(element.getAttribute('data-target'));
            const duration = 2000;
            const startTime = performance.now();
            
            // Easing function for smooth animation
            const easeOutExpo = (t) => {
                return t === 1 ? 1 : 1 - Math.pow(2, -10 * t);
            };
            
            const updateCounter = (currentTime) => {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const easedProgress = easeOutExpo(progress);
                const current = Math.floor(easedProgress * target);
                
                element.textContent = current + '+';
                
                if (progress < 1) {
                    requestAnimationFrame(updateCounter);
                }
            };
            
            requestAnimationFrame(updateCounter);
        }
        
        // Scroll Reveal
        function revealOnScroll() {
            const reveals = document.querySelectorAll('.reveal');
            
            reveals.forEach((element, index) => {
                const windowHeight = window.innerHeight;
                const elementTop = element.getBoundingClientRect().top;
                const elementVisible = 100;
                
                if (elementTop < windowHeight - elementVisible) {
                    element.classList.add('active');
                }
            });
        }
        
        // Smooth parallax scroll effect
        function initParallax() {
            const parallaxElements = document.querySelectorAll('.parallax');
            
            window.addEventListener('scroll', () => {
                const scrolled = window.pageYOffset;
                
                parallaxElements.forEach(el => {
                    const speed = el.dataset.speed || 0.5;
                    const yPos = -(scrolled * speed);
                    el.style.transform = `translateY(${yPos}px)`;
                });
            });
        }
        
        // Add hover sound effect (optional, subtle)
        function initInteractiveSounds() {
            const cards = document.querySelectorAll('.hover-card, .job-card-modern');
            
            cards.forEach(card => {
                card.addEventListener('mouseenter', () => {
                    // Subtle scale feedback
                    card.style.transition = 'all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1)';
                });
            });
        }
        
        // Initialize on load
        document.addEventListener('DOMContentLoaded', () => {
            createParticles();
            revealOnScroll();
            initParallax();
            initInteractiveSounds();
            
            // Animate counters when in view
            const counters = document.querySelectorAll('.counter');
            let countersAnimated = false;
            
            const observerOptions = {
                threshold: 0.5
            };
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting && !countersAnimated) {
                        counters.forEach((counter, index) => {
                            setTimeout(() => {
                                animateCounter(counter);
                            }, index * 100);
                        });
                        countersAnimated = true;
                    }
                });
            }, observerOptions);
            
            if (counters.length > 0) {
                observer.observe(counters[0].parentElement);
            }
            
            // Lazy load images
            const images = document.querySelectorAll('img[data-src]');
            const imageObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                        imageObserver.unobserve(img);
                    }
                });
            });
            
            images.forEach(img => imageObserver.observe(img));
        });
        
        // Scroll event listener with throttle for performance
        let scrollTimeout;
        window.addEventListener('scroll', () => {
            if (scrollTimeout) {
                window.cancelAnimationFrame(scrollTimeout);
            }
            
            scrollTimeout = window.requestAnimationFrame(() => {
                revealOnScroll();
            });
        });
    </script>
</body>
</html>

