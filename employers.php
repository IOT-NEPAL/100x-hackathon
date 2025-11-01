<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avsar Nepal - For Employers</title>
    
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
                        sans: ['Inter', 'Segoe UI', 'sans-serif']
                    }
                }
            }
        }
    </script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- GSAP -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/employers.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            overflow-x: hidden;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar-employers" role="navigation" aria-label="Main navigation">
        <div class="nav-container-employers">
            <a href="index.php" class="nav-logo-employers">
                <img src="loogo.png" alt="Avsar Nepal Logo">
                <span>Avsar Nepal</span>
            </a>
            
            <!-- Desktop Menu -->
            <ul class="nav-menu-employers">
                <li><a href="students.php">Students</a></li>
                <li><a href="employers.php" class="active">Employers</a></li>
                <li><a href="index.php#career-centers">Career centers</a></li>
            </ul>
            
            <!-- User Section -->
            <div class="nav-buttons-employers" x-data="{ mobileMenuOpen: false }">
                <a href="signin.php?type=employer" class="btn-nav-employers btn-outline">Sign up</a>
                <a href="login.php" class="btn-nav-employers btn-solid">Log in</a>
                
                <!-- Mobile Menu Button -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="mobile-menu-btn-employers" aria-label="Toggle mobile menu">
                    <i class="fas fa-bars" x-show="!mobileMenuOpen"></i>
                    <i class="fas fa-times" x-show="mobileMenuOpen"></i>
                </button>
                
                <!-- Mobile Menu -->
                <div x-show="mobileMenuOpen" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 transform scale-100"
                     x-transition:leave-end="opacity-0 transform scale-95"
                     class="absolute top-full left-0 right-0 bg-black p-4 space-y-2"
                     style="display: none;">
                    <a href="students.php" class="block text-white py-2 px-4 hover:bg-gray-900 rounded">Students</a>
                    <a href="employers.php" class="block text-white py-2 px-4 hover:bg-gray-900 rounded">Employers</a>
                    <a href="index.php#career-centers" class="block text-white py-2 px-4 hover:bg-gray-900 rounded">Career centers</a>
                    <div class="border-t border-gray-700 pt-2 mt-2 space-y-2">
                        <a href="signin.php?type=employer" class="block text-center py-2 px-4 border-2 rounded transition-colors" style="border-color: #00ff41; color: #00ff41;" onmouseover="this.style.backgroundColor='#00ff41'; this.style.color='#000';" onmouseout="this.style.backgroundColor='transparent'; this.style.color='#00ff41';">Sign up</a>
                        <a href="login.php" class="block text-center py-2 px-4 rounded transition-colors" style="background-color: #00ff41; color: #000;" onmouseover="this.style.backgroundColor='#ccff00';" onmouseout="this.style.backgroundColor='#00ff41';">Log in</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="employers-hero-new">
        <div class="employers-hero-container-new">
            <div class="employers-image-container-full">
                <img src="https://images.unsplash.com/photo-1521737711867-e3b97375f902?w=1920&h=1080&fit=crop" alt="Professional team working together">
                <div class="employers-text-overlay">
                    <h1 class="employers-main-title">
                        <span class="employers-title-line">HIRE FAST</span>
                        <span class="employers-title-line">HIRE RIGHT</span>
                    </h1>
                    <p class="employers-subtitle">
                        Connect with Nepal's top talent pool and build your dream team
                    </p>
                    <div class="employers-cta-group">
                        <a href="signin.php?type=employer" class="employers-cta-button primary">Get Started Now</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="employers-how-section-new">
        <div class="employers-how-container-new">
            <div class="section-badge">HOW IT WORKS</div>
            <div class="how-works-label">HOW AVSAR NEPAL WORKS</div>
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
                        <path d="M10 50 Q100 20, 190 50" stroke="rgba(255, 255, 255, 0.3)" stroke-width="2" stroke-dasharray="5,5" fill="none"/>
                        <path d="M175 45 L190 50 L175 55" stroke="rgba(255, 255, 255, 0.3)" stroke-width="2" fill="none"/>
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
                        <path d="M10 50 Q100 20, 190 50" stroke="rgba(255, 255, 255, 0.3)" stroke-width="2" stroke-dasharray="5,5" fill="none"/>
                        <path d="M175 45 L190 50 L175 55" stroke="rgba(255, 255, 255, 0.3)" stroke-width="2" fill="none"/>
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

    <!-- Testimonials Section -->
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
                        <p class="testimonial-text">
                            Avsar Nepal helped us reduce our hiring time by 60%. The AI matching is incredibly accurate and saves us countless hours.
                        </p>
                    </div>
                    <div class="testimonial-footer">
                        <div class="company-logo-wrapper">
                            <img src="https://images.unsplash.com/photo-1560179707-f14e90ef3623?w=100&h=100&fit=crop" alt="TechCo Nepal" class="company-logo-img">
                            <div class="testimonial-author">
                                <div class="author-name">Sarah Johnson</div>
                                <div class="author-title">HR Director, TechCo Nepal</div>
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
                </div>
                
                <!-- Testimonial 2 -->
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <div class="quote-icon">"</div>
                        <p class="testimonial-text">
                            The quality of candidates we receive is outstanding. We've built an amazing team thanks to Avsar Nepal's platform.
                        </p>
                    </div>
                    <div class="testimonial-footer">
                        <div class="company-logo-wrapper">
                            <img src="https://images.unsplash.com/photo-1549923746-c502d488b3ea?w=100&h=100&fit=crop" alt="InnovateLabs" class="company-logo-img">
                            <div class="testimonial-author">
                                <div class="author-name">Raj Sharma</div>
                                <div class="author-title">CEO, InnovateLabs</div>
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
                </div>
                
                <!-- Testimonial 3 -->
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <div class="quote-icon">"</div>
                        <p class="testimonial-text">
                            As a startup, we needed cost-effective hiring solutions. Avsar Nepal delivered beyond our expectations.
                        </p>
                    </div>
                    <div class="testimonial-footer">
                        <div class="company-logo-wrapper">
                            <img src="https://images.unsplash.com/photo-1572021335469-31706a17aaef?w=100&h=100&fit=crop" alt="StartupHub Nepal" class="company-logo-img">
                            <div class="testimonial-author">
                                <div class="author-name">Priya Thapa</div>
                                <div class="author-title">Founder, StartupHub Nepal</div>
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
        </div>
    </section>

    <!-- Features Section -->
    <section class="employers-features-section">
        <div class="employers-features-container">
            <div class="section-badge" style="background: rgba(26, 47, 58, 0.2); color: #1a2f3a;">FEATURES</div>
            <h2 class="employers-features-heading">
                Everything you need<br>to hire top talent
            </h2>
            <p class="section-description" style="color: #666; margin-bottom: 0;">
                Powerful tools designed to make your hiring process efficient and effective
            </p>
            
            <div class="employers-features-grid">
                <!-- Feature 1 -->
                <div class="employers-feature-card">
                    <div class="employers-feature-image-wrapper">
                        <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=600&h=400&fit=crop" alt="Build your brand" class="employers-feature-img">
                    </div>
                    <h3 class="employers-feature-title">Build your brand</h3>
                    <p class="employers-feature-desc">
                        Stay top of mind for 20M+ verified candidates and drive consistent touch points to boost engagement.
                    </p>
                </div>
                
                <!-- Feature 2 -->
                <div class="employers-feature-card">
                    <div class="employers-feature-image-wrapper">
                        <img src="https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=600&h=400&fit=crop" alt="Find the right candidates" class="employers-feature-img">
                    </div>
                    <h3 class="employers-feature-title">Find the right candidates</h3>
                    <p class="employers-feature-desc">
                        Refine your talent pool using enhanced filtering and targeting capabilities.
                    </p>
                </div>
                
                <!-- Feature 3 -->
                <div class="employers-feature-card">
                    <div class="employers-feature-image-wrapper">
                        <img src="https://images.unsplash.com/photo-1556761175-b413da4baf72?w=600&h=400&fit=crop" alt="Connect with Gen Z" class="employers-feature-img">
                    </div>
                    <h3 class="employers-feature-title">Connect with Gen Z</h3>
                    <p class="employers-feature-desc">
                        Build meaningful relationships by posting and engaging with candidates on the feed.
                    </p>
                </div>
                
                <!-- Feature 4 -->
                <div class="employers-feature-card">
                    <div class="employers-feature-image-wrapper">
                        <img src="https://images.unsplash.com/photo-1521791136064-7986c2920216?w=600&h=400&fit=crop" alt="Reduce time to hire" class="employers-feature-img">
                    </div>
                    <h3 class="employers-feature-title">Reduce time to hire</h3>
                    <p class="employers-feature-desc">
                        Create a seamless hiring experience for your team with tools for end-to-end recruiting.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer-employers">
        <div class="footer-container-employers">
            <div class="footer-grid-employers">
                <!-- Column 1: Brand -->
                <div class="footer-brand-employers">
                    <div class="footer-logo-employers">
                        <img src="loogo.png" alt="Avsar Nepal Logo">
                        <span>Avsar Nepal</span>
                    </div>
                    <p class="footer-tagline-employers">
                        Connecting talent with opportunity across Nepal
                    </p>
                </div>
                
                <!-- Column 2: For Students -->
                <div class="footer-column-employers">
                    <h4>For Students</h4>
                    <ul>
                        <li><a href="opportunities.php">Find Jobs</a></li>
                        <li><a href="opportunities.php?type=internship">Internships</a></li>
                        <li><a href="user/career-guidance-ai.php">Career Advice</a></li>
                        <li><a href="#events">Events</a></li>
                    </ul>
                </div>
                
                <!-- Column 3: For Employers -->
                <div class="footer-column-employers">
                    <h4>For Employers</h4>
                    <ul>
                        <li><a href="organizer/add-opportunity.php">Post a Job</a></li>
                        <li><a href="organizer/hire-talent.php">Hire Talent</a></li>
                        <li><a href="#pricing">Pricing</a></li>
                        <li><a href="#resources">Resources</a></li>
                    </ul>
                </div>
                
                <!-- Column 4: Company -->
                <div class="footer-column-employers">
                    <h4>Company</h4>
                    <ul>
                        <li><a href="#about">About Us</a></li>
                        <li><a href="#contact">Contact</a></li>
                        <li><a href="#blog">Blog</a></li>
                        <li><a href="#careers">Careers</a></li>
                    </ul>
                </div>
                
                <!-- Column 5: Connect -->
                <div class="footer-column-employers">
                    <h4>Connect</h4>
                    <div class="footer-social-employers">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            
            <!-- Footer Bottom -->
            <div class="footer-bottom-employers">
                <p>&copy; 2024 Avsar Nepal. All rights reserved.</p>
                <div class="footer-links-employers">
                    <a href="#privacy">Privacy Policy</a>
                    <a href="#terms">Terms</a>
                    <a href="#cookies">Cookies</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        // Register ScrollTrigger plugin
        gsap.registerPlugin(ScrollTrigger);

        // GSAP Animations
        // Hero Section
        gsap.from('.employers-image-container-full', {
            scale: 0.95,
            opacity: 0,
            duration: 1,
            delay: 0.3,
            ease: 'power3.out'
        });

        gsap.from('.employers-title-line', {
            y: 50,
            opacity: 0,
            stagger: 0.15,
            duration: 0.8,
            delay: 0.8,
            ease: 'power3.out'
        });

        gsap.from('.employers-subtitle', {
            y: 30,
            opacity: 0,
            duration: 0.8,
            delay: 1.1,
            ease: 'power3.out'
        });

        gsap.from('.employers-cta-button', {
            y: 30,
            opacity: 0,
            duration: 0.8,
            delay: 1.4,
            ease: 'power3.out'
        });

        // How It Works Section
        gsap.from('.how-works-label', {
            scrollTrigger: {
                trigger: '.employers-how-section-new',
                start: 'top 80%'
            },
            y: 30,
            opacity: 0,
            duration: 0.8,
            ease: 'power3.out'
        });

        gsap.from('.employers-how-heading-new', {
            scrollTrigger: {
                trigger: '.employers-how-section-new',
                start: 'top 80%'
            },
            y: 50,
            opacity: 0,
            duration: 0.8,
            delay: 0.2,
            ease: 'power3.out'
        });

        gsap.from('.how-step-item', {
            scrollTrigger: {
                trigger: '.how-steps-grid',
                start: 'top 80%'
            },
            y: 50,
            opacity: 0,
            stagger: 0.2,
            duration: 0.8,
            ease: 'power3.out'
        });

        gsap.from('.curved-arrow-wrapper', {
            scrollTrigger: {
                trigger: '.how-steps-grid',
                start: 'top 80%'
            },
            scale: 0,
            opacity: 0,
            stagger: 0.2,
            duration: 0.6,
            delay: 0.3,
            ease: 'back.out(1.7)'
        });

        // Testimonials Section
        gsap.from('.employers-testimonials-heading', {
            scrollTrigger: {
                trigger: '.employers-testimonials-section',
                start: 'top 80%'
            },
            y: 50,
            opacity: 0,
            duration: 0.8,
            ease: 'power3.out'
        });

        gsap.from('.employers-testimonials-subheading', {
            scrollTrigger: {
                trigger: '.employers-testimonials-section',
                start: 'top 80%'
            },
            y: 30,
            opacity: 0,
            duration: 0.8,
            delay: 0.2,
            ease: 'power3.out'
        });

        gsap.from('.testimonial-card', {
            scrollTrigger: {
                trigger: '.testimonials-grid',
                start: 'top 80%'
            },
            y: 60,
            opacity: 0,
            stagger: 0.15,
            duration: 0.8,
            ease: 'power3.out'
        });

        // Features Section
        gsap.from('.employers-features-heading', {
            scrollTrigger: {
                trigger: '.employers-features-section',
                start: 'top 80%'
            },
            y: 50,
            opacity: 0,
            duration: 0.8,
            ease: 'power3.out'
        });

        gsap.from('.employers-feature-image-wrapper', {
            scrollTrigger: {
                trigger: '.employers-features-grid',
                start: 'top 80%'
            },
            scale: 0.8,
            opacity: 0,
            stagger: 0.1,
            duration: 0.8,
            ease: 'power3.out'
        });

        gsap.from('.employers-feature-title', {
            scrollTrigger: {
                trigger: '.employers-features-grid',
                start: 'top 80%'
            },
            y: 30,
            opacity: 0,
            stagger: 0.1,
            duration: 0.8,
            delay: 0.3,
            ease: 'power3.out'
        });

        gsap.from('.employers-feature-desc', {
            scrollTrigger: {
                trigger: '.employers-features-grid',
                start: 'top 80%'
            },
            y: 20,
            opacity: 0,
            stagger: 0.1,
            duration: 0.8,
            delay: 0.5,
            ease: 'power3.out'
        });
    </script>
</body>
</html>

