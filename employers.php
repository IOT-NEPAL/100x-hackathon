<?php
session_start();
require_once 'includes/db.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$user = null;

if ($isLoggedIn) {
    $stmt = $pdo->prepare("SELECT name, email, profile_pic, role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avsar Nepal - For Employers</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="css/employers.css">
    
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
    
    <!-- GSAP - Latest stable version -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar" x-data="{ mobileMenuOpen: false }">
        <div class="nav-container">
            <div class="nav-left">
                <div class="nav-logo">
                    <a href="index.php" class="hover:scale-105 transition-transform duration-300">
                        <img src="logo.png" alt="Avsar Nepal" class="logo-img">
                    </a>
                </div>
                
                <!-- Desktop Menu -->
                <ul class="nav-menu hidden md:flex">
                    <li><a href="students.php" class="hover:text-avsar-green transition-colors duration-300">Students</a></li>
                    <li><a href="employers.php" class="text-avsar-green font-semibold">Employers</a></li>
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
                    <?php if ($isLoggedIn && $user): ?>
                        <!-- User Profile Dropdown -->
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="flex items-center gap-2 hover:opacity-80 transition-opacity">
                                <?php if ($user['profile_pic']): ?>
                                    <img src="<?php echo htmlspecialchars($user['profile_pic']); ?>"
                                         alt="<?php echo htmlspecialchars($user['name']); ?>"
                                         class="w-10 h-10 rounded-full object-cover border-2 border-avsar-green">
                                <?php else: ?>
                                    <div class="w-10 h-10 rounded-full bg-avsar-green flex items-center justify-center text-black font-bold">
                                        <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                                <span class="text-white font-medium hidden lg:block"><?php echo htmlspecialchars($user['name']); ?></span>
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50"
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
                                <a href="<?php echo $dashboardLink; ?>" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                                    <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                                </a>
                                <a href="profile.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                                    <i class="fas fa-user mr-2"></i>Profile
                                </a>
                                <hr class="my-2">
                                <a href="logout.php" class="block px-4 py-2 text-red-600 hover:bg-gray-100">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="signup.php" class="btn-signup hover:bg-gray-100 transition-colors duration-300" style="text-decoration: none; display: inline-block;">Sign up</a>
                        <a href="login.php" class="btn-login hover:bg-avsar-green/90 transition-colors duration-300" style="text-decoration: none; display: inline-block;">Log in</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div x-show="mobileMenuOpen" x-transition class="md:hidden bg-avsar-dark border-t border-gray-700">
            <div class="px-4 py-2 space-y-2">
                <a href="students.php" class="block py-2 text-white hover:text-avsar-green transition-colors duration-300">Students</a>
                <a href="employers.php" class="block py-2 text-avsar-green font-semibold">Employers</a>
                <a href="career_centers.php" class="block py-2 text-white hover:text-avsar-green transition-colors duration-300">Career centers</a>

                <div class="pt-4 space-y-2">
                    <?php if ($isLoggedIn && $user): ?>
                        <div class="flex items-center gap-3 p-3 bg-gray-800 rounded-lg mb-3">
                            <?php if ($user['profile_pic']): ?>
                                <img src="<?php echo htmlspecialchars($user['profile_pic']); ?>"
                                     alt="<?php echo htmlspecialchars($user['name']); ?>"
                                     class="w-12 h-12 rounded-full object-cover border-2 border-avsar-green">
                            <?php else: ?>
                                <div class="w-12 h-12 rounded-full bg-avsar-green flex items-center justify-center text-black font-bold text-lg">
                                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                            <div class="flex-1">
                                <div class="text-white font-semibold"><?php echo htmlspecialchars($user['name']); ?></div>
                                <div class="text-gray-400 text-sm"><?php echo htmlspecialchars($user['email']); ?></div>
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
                        <a href="<?php echo $dashboardLink; ?>" class="w-full btn-signup" style="display: block; text-align: center; text-decoration: none;">Dashboard</a>
                        <a href="profile.php" class="w-full btn-login" style="display: block; text-align: center; text-decoration: none;">Profile</a>
                        <a href="logout.php" class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors" style="display: block; text-align: center; text-decoration: none;">Logout</a>
                    <?php else: ?>
                        <a href="signup.php" class="w-full btn-signup" style="display: block; text-align: center; text-decoration: none;">Sign up</a>
                        <a href="login.php" class="w-full btn-login" style="display: block; text-align: center; text-decoration: none;">Log in</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Employers Hero Section -->
    <section class="employers-hero-new">
        <div class="employers-hero-container-new">
            <!-- Image with Text Overlay -->
            <div class="employers-image-container-full">
                <img src="https://images.unsplash.com/photo-1521737711867-e3b97375f902?w=1920&h=1080&fit=crop" alt="Professional employer" class="employers-professional-image">
                <div class="employers-text-overlay">
                    <h1 class="employers-main-title">
                        <span class="employers-title-line">HIRE FAST</span>
                        <span class="employers-title-line">HIRE RIGHT</span>
                    </h1>
                    <p class="employers-subtitle">Connect with Nepal's top talent pool and build your dream team</p>
                    <div class="employers-cta-group">
                        <button class="employers-cta-button primary">Get Started Now</button>
                    </div>
                </div>
            </div>
        </div>
    
    </section>

    <!-- How It Works Section -->
    <section class="employers-how-section-new">
        <div class="employers-how-container-new">
            <div class="section-badge">HOW IT WORKS</div>
            <p class="how-works-label">HOW AVSAR NEPAL WORKS</p>
            <h2 class="employers-how-heading-new">Three steps to better hiring</h2>
            <p class="section-description">Streamline your recruitment process with our AI-powered platform</p>
            
            <div class="how-steps-grid">
                <!-- Step 1 -->
                <div class="how-step-item">
                    <div class="step-number-badge">1</div>
                    <h3 class="how-step-title">Post a Job & Get Matched</h3>
                    <p class="how-step-text">Create job postings and let our AI match you with the most qualified candidates from Nepal's top talent pool.</p>
                </div>

                <!-- Curved Arrow 1 -->
                <div class="curved-arrow-wrapper">
                    <svg class="curved-arrow" viewBox="0 0 200 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 50 Q 100 10, 190 50" stroke="rgba(255, 255, 255, 0.3)" stroke-width="2" fill="none" stroke-dasharray="5,5"/>
                    </svg>
                </div>

                <!-- Step 2 -->
                <div class="how-step-item">
                    <div class="step-number-badge">2</div>
                    <h3 class="how-step-title">Review & Interview</h3>
                    <p class="how-step-text">Screen candidates efficiently with our platform tools. Conduct video interviews and skill assessments seamlessly.</p>
                </div>

                <!-- Curved Arrow 2 -->
                <div class="curved-arrow-wrapper">
                    <svg class="curved-arrow" viewBox="0 0 200 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 50 Q 100 10, 190 50" stroke="rgba(255, 255, 255, 0.3)" stroke-width="2" fill="none" stroke-dasharray="5,5"/>
                    </svg>
                </div>

                <!-- Step 3 -->
                <div class="how-step-item">
                    <div class="step-number-badge">3</div>
                    <h3 class="how-step-title">Hire Your Team</h3>
                    <p class="how-step-text">Make offers and onboard new team members quickly. Track all your hires in one centralized dashboard.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Success Stories/Testimonials Section -->
    <section class="employers-testimonials-section">
        <div class="employers-testimonials-container">
            <div class="section-badge">SUCCESS STORIES</div>
            <h2 class="employers-testimonials-heading">Trusted by leading companies</h2>
            <p class="employers-testimonials-subheading">See how companies are transforming their hiring process with Avsar Nepal</p>
            
            <div class="testimonials-grid">
                <!-- Testimonial 1 -->
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <div class="quote-icon">"</div>
                        <p class="testimonial-text">"Avsar Nepal helped us reduce our hiring time by 60%. The AI matching is incredibly accurate and saves us countless hours."</p>
                    </div>
                    <div class="testimonial-footer">
                        <div class="company-logo-wrapper">
                            <img src="https://images.unsplash.com/photo-1560179707-f14e90ef3623?w=100&h=100&fit=crop" alt="TechCo" class="company-logo-img">
                        </div>
                        <div class="testimonial-author">
                            <h4 class="author-name">Sarah Johnson</h4>
                            <p class="author-title">HR Director, TechCo Nepal</p>
                        </div>
                    </div>
                    <div class="testimonial-metrics">
                        <div class="metric-item">
                            <span class="metric-value">60%</span>
                            <span class="metric-label">Faster Hiring</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-value">50+</span>
                            <span class="metric-label">Hires Made</span>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 2 -->
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <div class="quote-icon">"</div>
                        <p class="testimonial-text">"The quality of candidates we receive is outstanding. We've built an amazing team thanks to Avsar Nepal's platform."</p>
                    </div>
                    <div class="testimonial-footer">
                        <div class="company-logo-wrapper">
                            <img src="https://images.unsplash.com/photo-1549923746-c502d488b3ea?w=100&h=100&fit=crop" alt="InnovateLabs" class="company-logo-img">
                        </div>
                        <div class="testimonial-author">
                            <h4 class="author-name">Raj Sharma</h4>
                            <p class="author-title">CEO, InnovateLabs</p>
                        </div>
                    </div>
                    <div class="testimonial-metrics">
                        <div class="metric-item">
                            <span class="metric-value">95%</span>
                            <span class="metric-label">Success Rate</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-value">30+</span>
                            <span class="metric-label">Team Members</span>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 3 -->
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <div class="quote-icon">"</div>
                        <p class="testimonial-text">"As a startup, we needed cost-effective hiring solutions. Avsar Nepal delivered beyond our expectations."</p>
                    </div>
                    <div class="testimonial-footer">
                        <div class="company-logo-wrapper">
                            <img src="https://images.unsplash.com/photo-1572021335469-31706a17aaef?w=100&h=100&fit=crop" alt="StartupHub" class="company-logo-img">
                        </div>
                        <div class="testimonial-author">
                            <h4 class="author-name">Priya Thapa</h4>
                            <p class="author-title">Founder, StartupHub Nepal</p>
                        </div>
                    </div>
                    <div class="testimonial-metrics">
                        <div class="metric-item">
                            <span class="metric-value">$10K</span>
                            <span class="metric-label">Cost Saved</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-value">20+</span>
                            <span class="metric-label">Positions Filled</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- Features/Benefits Section -->
    <section class="employers-features-section">
        <div class="employers-features-container">
            <div class="section-badge">FEATURES</div>
            <h2 class="employers-features-heading">
                Everything you need<br>to hire top talent
            </h2>
            <p class="section-description">Powerful tools designed to make your hiring process efficient and effective</p>
            
            <div class="employers-features-grid">
                <!-- Feature 1: Build your brand -->
                <div class="employers-feature-card">
                    <div class="employers-feature-image-wrapper">
                        <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=600&h=400&fit=crop" alt="Build your brand" class="employers-feature-img">
                    </div>
                    <h3 class="employers-feature-title">Build your brand</h3>
                    <p class="employers-feature-desc">Stay top of mind for 20M+ verified candidates and drive consistent touch points to boost engagement.</p>
                </div>

                <!-- Feature 2: Find the right candidates -->
                <div class="employers-feature-card">
                    <div class="employers-feature-image-wrapper">
                        <img src="https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=600&h=400&fit=crop" alt="Find the right candidates" class="employers-feature-img">
                    </div>
                    <h3 class="employers-feature-title">Find the right candidates</h3>
                    <p class="employers-feature-desc">Refine your talent pool using enhanced filtering and targeting capabilities.</p>
                </div>

                <!-- Feature 3: Connect with Gen Z -->
                <div class="employers-feature-card">
                    <div class="employers-feature-image-wrapper">
                        <img src="https://images.unsplash.com/photo-1556761175-b413da4baf72?w=600&h=400&fit=crop" alt="Connect with Gen Z" class="employers-feature-img">
                    </div>
                    <h3 class="employers-feature-title">Connect with Gen Z</h3>
                    <p class="employers-feature-desc">Build meaningful relationships by posting and engaging with candidates on the feed.</p>
                </div>

                <!-- Feature 4: Reduce time to hire -->
                <div class="employers-feature-card">
                    <div class="employers-feature-image-wrapper">
                        <img src="https://images.unsplash.com/photo-1521791136064-7986c2920216?w=600&h=400&fit=crop" alt="Reduce time to hire" class="employers-feature-img">
                    </div>
                    <h3 class="employers-feature-title">Reduce time to hire</h3>
                    <p class="employers-feature-desc">Create a seamless hiring experience for your team with tools for end-to-end recruiting.</p>
                </div>
            </div>
        </div>
    </section>


    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <!-- Footer Top -->
            <div class="footer-top">
                <div class="footer-column">
                    <div class="footer-logo">
                        <img src="logo.png" alt="Avsar Nepal" class="logo-img">
                        <span class="footer-brand">Avsar Nepal</span>
                    </div>
                    <p class="footer-tagline">Connecting talent with opportunity across Nepal</p>
                </div>

                <div class="footer-column">
                    <h4 class="footer-heading">For Students</h4>
                    <ul class="footer-links">
                        <li><a href="#jobs">Find Jobs</a></li>
                        <li><a href="#internships">Internships</a></li>
                        <li><a href="#career-advice">Career Advice</a></li>
                        <li><a href="#events">Events</a></li>
                    </ul>
                </div>

                <div class="footer-column">
                    <h4 class="footer-heading">For Employers</h4>
                    <ul class="footer-links">
                        <li><a href="#post-job">Post a Job</a></li>
                        <li><a href="#hire">Hire Talent</a></li>
                        <li><a href="#pricing">Pricing</a></li>
                        <li><a href="#resources">Resources</a></li>
                    </ul>
                </div>

                <div class="footer-column">
                    <h4 class="footer-heading">Company</h4>
                    <ul class="footer-links">
                        <li><a href="#about">About Us</a></li>
                        <li><a href="#contact">Contact</a></li>
                        <li><a href="#blog">Blog</a></li>
                        <li><a href="#careers">Careers</a></li>
                    </ul>
                </div>

                <div class="footer-column">
                    <h4 class="footer-heading">Connect</h4>
                    <div class="social-links">
                        <a href="#facebook" class="social-icon" aria-label="Facebook">f</a>
                        <a href="#twitter" class="social-icon" aria-label="Twitter">𝕏</a>
                        <a href="#linkedin" class="social-icon" aria-label="LinkedIn">in</a>
                        <a href="#instagram" class="social-icon" aria-label="Instagram">📷</a>
                    </div>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="footer-bottom">
                <div class="footer-legal">
                    <p>&copy; 2024 Avsar Nepal. All rights reserved.</p>
                    <div class="legal-links">
                        <a href="#privacy">Privacy Policy</a>
                        <span class="separator">|</span>
                        <a href="#terms">Terms of Service</a>
                        <span class="separator">|</span>
                        <a href="#cookies">Cookie Policy</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="script.js"></script>
    
    <!-- GSAP Animations for Employers Page -->
    <script>
        // GSAP Animations
        gsap.registerPlugin(ScrollTrigger);

        // Animate hero section
        gsap.from(".employers-image-container-full", {
            duration: 1,
            scale: 0.95,
            opacity: 0,
            ease: "power2.out",
            delay: 0.3
        });

        gsap.from(".employers-main-title .employers-title-line", {
            duration: 1.2,
            y: 50,
            opacity: 0,
            stagger: 0.15,
            ease: "power3.out",
            delay: 0.8
        });

        gsap.from(".employers-cta-button", {
            duration: 0.8,
            y: 30,
            opacity: 0,
            ease: "power2.out",
            delay: 1.4
        });

        // How It Works section animations
        gsap.from(".how-works-label", {
            scrollTrigger: {
                trigger: ".employers-how-section-new",
                start: "top 80%"
            },
            duration: 0.8,
            y: 30,
            opacity: 0,
            ease: "power2.out"
        });

        gsap.from(".employers-how-heading-new", {
            scrollTrigger: {
                trigger: ".employers-how-section-new",
                start: "top 80%"
            },
            duration: 1,
            y: 50,
            opacity: 0,
            ease: "power2.out",
            delay: 0.2
        });

        // Animate step items
        gsap.utils.toArray(".how-step-item").forEach((step, i) => {
            gsap.from(step, {
                scrollTrigger: { 
                    trigger: step, 
                    start: 'top 85%',
                    once: true
                },
                duration: 0.8,
                y: 50,
                opacity: 0,
                ease: "power2.out",
                delay: i * 0.2
            });
        });

        // Animate arrows
        gsap.utils.toArray(".curved-arrow-wrapper").forEach((arrow, i) => {
            gsap.from(arrow, {
                scrollTrigger: { 
                    trigger: arrow, 
                    start: 'top 85%',
                    once: true
                },
                duration: 0.6,
                scale: 0,
                opacity: 0,
                ease: "back.out(1.5)",
                delay: i * 0.2 + 0.3
            });
        });

        // Testimonials section animations
        gsap.from(".employers-testimonials-heading", {
            scrollTrigger: {
                trigger: ".employers-testimonials-section",
                start: "top 80%"
            },
            duration: 1,
            y: 50,
            opacity: 0,
            ease: "power2.out"
        });

        gsap.from(".employers-testimonials-subheading", {
            scrollTrigger: {
                trigger: ".employers-testimonials-section",
                start: "top 80%"
            },
            duration: 0.8,
            y: 30,
            opacity: 0,
            ease: "power2.out",
            delay: 0.2
        });

        gsap.utils.toArray(".testimonial-card").forEach((card, i) => {
            gsap.from(card, {
                scrollTrigger: { 
                    trigger: card, 
                    start: 'top 85%',
                    once: true
                },
                duration: 1,
                y: 60,
                opacity: 0,
                ease: "power2.out",
                delay: i * 0.15
            });
        });

        // Features section animations
        gsap.from(".employers-features-heading", {
            scrollTrigger: {
                trigger: ".employers-features-section",
                start: "top 80%"
            },
            duration: 1,
            y: 50,
            opacity: 0,
            ease: "power2.out"
        });

        gsap.utils.toArray(".employers-feature-card").forEach((card, i) => {
            gsap.from(card.querySelector('.employers-feature-image-wrapper'), {
                scrollTrigger: { 
                    trigger: card, 
                    start: 'top 85%',
                    once: true
                },
                duration: 1,
                scale: 0.8,
                opacity: 0,
                ease: "back.out(1.2)",
                delay: i * 0.1
            });

            gsap.from(card.querySelector('.employers-feature-title'), {
                scrollTrigger: { 
                    trigger: card, 
                    start: 'top 85%',
                    once: true
                },
                duration: 0.8,
                y: 30,
                opacity: 0,
                ease: "power2.out",
                delay: i * 0.1 + 0.3
            });

            gsap.from(card.querySelector('.employers-feature-desc'), {
                scrollTrigger: { 
                    trigger: card, 
                    start: 'top 85%',
                    once: true
                },
                duration: 0.8,
                y: 20,
                opacity: 0,
                ease: "power2.out",
                delay: i * 0.1 + 0.5
            });
        });
    </script>
</body>
</html>
