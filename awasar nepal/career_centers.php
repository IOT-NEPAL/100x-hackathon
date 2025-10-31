<?php
// Pre-login version - no user session handling
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Career Centers - Avsar Nepal</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="css/students.css">
    <link rel="stylesheet" href="css/career_centers.css">
    
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
                    <li><a href="employers.php" class="hover:text-avsar-green transition-colors duration-300">Employers</a></li>
                    <li><a href="career_centers.php" class="text-avsar-green font-semibold">Career centers</a></li>
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
                    <a href="signup.php" class="btn-signup hover:bg-gray-100 transition-colors duration-300" style="text-decoration: none; display: inline-block;">Sign up</a>
                    <a href="login.php" class="btn-login hover:bg-avsar-green/90 transition-colors duration-300" style="text-decoration: none; display: inline-block;">Log in</a>
                </div>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div x-show="mobileMenuOpen" x-transition class="md:hidden bg-avsar-dark border-t border-gray-700">
            <div class="px-4 py-2 space-y-2">
                <a href="students.php" class="block py-2 text-white hover:text-avsar-green transition-colors duration-300">Students</a>
                <a href="employers.php" class="block py-2 text-white hover:text-avsar-green transition-colors duration-300">Employers</a>
                <a href="career_centers.php" class="block py-2 text-avsar-green font-semibold">Career centers</a>

                <div class="pt-4 space-y-2">
                    <a href="signup.php" class="w-full btn-signup" style="display: block; text-align: center; text-decoration: none;">Sign up</a>
                    <a href="login.php" class="w-full btn-login" style="display: block; text-align: center; text-decoration: none;">Log in</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Career Centers Hero Section -->
    <section class="students-hero-new">
        <div class="hero-container-new">
            <!-- Image with Text Overlay -->
            <div class="hero-image-container-full">
                <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=1920&h=1080&fit=crop" alt="Career center professionals" class="hero-professional-image">
                <div class="hero-text-overlay">
                    <h1 class="hero-main-title">
                        <span class="title-line">EMPOWER</span>
                        <span class="title-line">CONNECT</span>
                    </h1>
                </div>
            </div>
        </div>

        <!-- Bottom Section -->
        <div class="hero-bottom-section">
            <div class="bottom-text">
                <p>Partner with us to enhance student success and streamline career services.</p>
            </div>
            <div class="bottom-form">
                <form class="signup-form-new" id="careerCentersForm" x-data="{ 
                    email: '', 
                    isValid: false,
                    isSubmitting: false,
                    checkEmail() {
                        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        this.isValid = emailRegex.test(this.email) && this.email.length > 5;
                    }
                }" @submit.prevent="isSubmitting = true">
                    <input 
                        type="email" 
                        class="email-input-new" 
                        placeholder="Enter your institution email" 
                        required
                        id="careerCentersEmailInput"
                        x-model="email"
                        @input="checkEmail()"
                        :class="{ 'border-green-500': isValid, 'border-red-500': email && !isValid }"
                    >
                    <button 
                        type="submit" 
                        class="btn-signup-new"
                        :disabled="!isValid || isSubmitting"
                        :class="{ 'opacity-50 cursor-not-allowed': !isValid || isSubmitting }"
                    >
                        <span x-show="!isSubmitting">Contact Us</span>
                        <span x-show="isSubmitting">...</span>
                    </button>
                </form>
            </div>
        </div>
    </section>

    <!-- Features/Benefits Section -->
    <section class="features-section">
        <div class="features-container">
            <h2 class="features-heading">
                Everything you need to <span class="features-highlight">support your students</span>
            </h2>
            
            <div class="features-infographic">
                <!-- Step 1: Student Placement -->
                <div class="feature-step">
                    <p class="step-description">
                        Connect your students with top employers across Nepal. Track placement rates and student outcomes with our comprehensive analytics dashboard.
                    </p>
                    <div class="step-icon-circle step-icon-red">
                        <svg width="70" height="70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                </div>

                <!-- Step 2: Event Management -->
                <div class="feature-step">
                    <p class="step-description">
                        Organize career fairs, workshops, and networking events with ease. Manage registrations, track attendance, and measure engagement all in one platform.
                    </p>
                    <div class="step-icon-circle step-icon-teal">
                        <svg width="70" height="70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                    </div>
                </div>

                <!-- Step 3: Employer Partnerships -->
                <div class="feature-step">
                    <p class="step-description">
                        Build lasting relationships with hiring partners. Facilitate meaningful connections between employers and your student community with powerful matching tools.
                    </p>
                    <div class="step-icon-circle step-icon-yellow">
                        <svg width="70" height="70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                        </svg>
                    </div>
                </div>

                <!-- Paper Airplane -->
                <div class="paper-airplane">
                    <svg width="100" height="100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M22 2L11 13"/>
                        <path d="M22 2L15 22l-4-9-9-4 20-7z"/>
                    </svg>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="how-it-works-section">
        <div class="how-it-works-container">
            <h2 class="how-it-works-heading">
                Get started in <span class="how-it-works-highlight">3 simple steps</span>
            </h2>
            
            <div class="steps-layout">
                <!-- Left: Circular Visual with Arc -->
                <div class="steps-visual">
                    <div class="steps-circle-outer">
                        <div class="steps-circle-middle">
                            <div class="steps-circle-inner">
                                <div class="steps-big-number">3</div>
                                <div class="steps-label">STEP</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Arc Path with Dots -->
                    <svg class="steps-arc-svg" viewBox="0 0 300 400" xmlns="http://www.w3.org/2000/svg">
                        <path class="arc-path" d="M 150 50 Q 250 100, 280 200" fill="none" stroke="url(#arcGradient)" stroke-width="3"/>
                        <defs>
                            <linearGradient id="arcGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" style="stop-color:#4d9fff;stop-opacity:1" />
                                <stop offset="100%" style="stop-color:#1e3a8a;stop-opacity:1" />
                            </linearGradient>
                        </defs>
                        
                        <!-- Dots on arc -->
                        <circle class="arc-dot dot-1" cx="205" cy="88" r="8" fill="#6dd5ed"/>
                        <circle class="arc-dot dot-2" cx="260" cy="160" r="8" fill="#c44bd4"/>
                        <circle class="arc-dot dot-3" cx="282" cy="210" r="8" fill="#d84040"/>
                    </svg>
                </div>

                <!-- Right: Step Pills -->
                <div class="steps-pills">
                    <div class="step-pill" data-step="1">
                        <div class="pill-number pill-color-1">1</div>
                        <div class="pill-content">
                            <h3 class="pill-title">Register Your Institution</h3>
                            <p class="pill-text">Sign up and create your career center profile. Add your team members and customize your institution's page.</p>
                        </div>
                    </div>

                    <div class="step-pill" data-step="2">
                        <div class="pill-number pill-color-2">2</div>
                        <div class="pill-content">
                            <h3 class="pill-title">Invite Your Students</h3>
                            <p class="pill-text">Onboard students to the platform. They'll gain access to jobs, skill assessments, and career resources.</p>
                        </div>
                    </div>

                    <div class="step-pill" data-step="3">
                        <div class="pill-number pill-color-3">3</div>
                        <div class="pill-content">
                            <h3 class="pill-title">Connect & Track Success</h3>
                            <p class="pill-text">Partner with employers, organize events, and monitor student outcomes with comprehensive analytics and reporting tools.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section - Full Background Style -->
    <section class="career-testimonials-fullbg">
        <div class="career-testimonials-wrapper">
            <h3 class="career-testimonials-label">Testimonials</h3>
            
            <!-- Testimonial Slides Container -->
            <div class="testimonials-slides-container">

            <div class="testimonial-slide-fullbg active" style="background-image: url('https://images.unsplash.com/photo-1531482615713-2afd69097998?w=1920&h=1080&fit=crop');">
                <div class="testimonial-overlay"></div>
                <div class="testimonial-content-fullbg">
                    <blockquote class="testimonial-quote-fullbg">
                        "Our team has always worked hard to make as many industry contacts as possible, but with limited staff and time, we could only do so much. Avsar Nepal's employer network meant our students suddenly had direct access to jobs that weren't even on our radar."
                    </blockquote>
                    <cite class="testimonial-author-fullbg">
                        <span class="author-name-fullbg">Christian Garcia,</span>
                        <span class="author-institution-fullbg">University of Miami</span>
                    </cite>
                </div>
            </div>

            <div class="testimonial-slide-fullbg" style="background-image: url('https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=1920&h=1080&fit=crop');">
                <div class="testimonial-overlay"></div>
                <div class="testimonial-content-fullbg">
                    <blockquote class="testimonial-quote-fullbg">
                        "We're grateful to Avsar Nepal for providing our students with equal access to jobs, events, and employers who typically recruit at four-year institutions. The platform has leveled the playing field for community college students."
                    </blockquote>
                    <cite class="testimonial-author-fullbg">
                        <span class="author-name-fullbg">Amy Crawford,</span>
                        <span class="author-institution-fullbg">Howard Community College</span>
                    </cite>
                </div>
            </div>

            <div class="testimonial-slide-fullbg" style="background-image: url('https://images.unsplash.com/photo-1541339907198-e08756dedf3f?w=1920&h=1080&fit=crop');">
                <div class="testimonial-overlay"></div>
                <div class="testimonial-content-fullbg">
                    <blockquote class="testimonial-quote-fullbg">
                        "Avsar Nepal has been a game-changer for our university... helping us to achieve our goals of providing a cutting-edge, secure, and accessible online experience for all members of our community. The analytics dashboard gives us real-time insights into student engagement."
                    </blockquote>
                    <cite class="testimonial-author-fullbg">
                        <span class="author-name-fullbg">Lamark Shaw,</span>
                        <span class="author-institution-fullbg">Daemen College</span>
                    </cite>
                </div>
            </div>

            <div class="testimonial-slide-fullbg" style="background-image: url('https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=1920&h=1080&fit=crop');">
                <div class="testimonial-overlay"></div>
                <div class="testimonial-content-fullbg">
                    <blockquote class="testimonial-quote-fullbg">
                        "The platform's employer partnerships have opened doors for our students that we never thought possible. We've seen a 45% increase in placement rates since partnering with Avsar Nepal. The integration with our existing systems was seamless."
                    </blockquote>
                    <cite class="testimonial-author-fullbg">
                        <span class="author-name-fullbg">Dr. Sarah Mitchell,</span>
                        <span class="author-institution-fullbg">Boston College</span>
                    </cite>
                </div>
            </div>

            <div class="testimonial-slide-fullbg" style="background-image: url('https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?w=1920&h=1080&fit=crop');">
                <div class="testimonial-overlay"></div>
                <div class="testimonial-content-fullbg">
                    <blockquote class="testimonial-quote-fullbg">
                        "What sets Avsar Nepal apart is their genuine commitment to student success. They've helped us modernize our career services and engage students in ways we never imagined. Our career fair attendance increased by 200% this year."
                    </blockquote>
                    <cite class="testimonial-author-fullbg">
                        <span class="author-name-fullbg">James Rodriguez,</span>
                        <span class="author-institution-fullbg">Stanford University</span>
                    </cite>
                </div>
            </div>
            </div>

            <!-- Navigation Arrows -->
            <div class="testimonial-nav-fullbg">
                <button class="testimonial-arrow-fullbg testimonial-prev-fullbg" id="careerTestiPrev" aria-label="Previous testimonial">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M15 18l-6-6 6-6"/>
                    </svg>
                </button>
                <button class="testimonial-arrow-fullbg testimonial-next-fullbg" id="careerTestiNext" aria-label="Next testimonial">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M9 18l6-6-6-6"/>
                    </svg>
                </button>
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
                    <h4 class="footer-heading">Career Centers</h4>
                    <ul class="footer-links">
                        <li><a href="#partnership">Partnership</a></li>
                        <li><a href="#resources">Resources</a></li>
                        <li><a href="#support">Support</a></li>
                        <li><a href="#contact">Contact</a></li>
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
    
    <!-- GSAP Animations for Career Centers Page -->
    <script>
        // GSAP Animations
        gsap.registerPlugin(ScrollTrigger);

        // Animate hero section
        gsap.from(".hero-image-container-full", {
            duration: 1,
            scale: 0.95,
            opacity: 0,
            ease: "power2.out",
            delay: 0.3
        });

        gsap.from(".hero-main-title .title-line", {
            duration: 1.2,
            y: 50,
            opacity: 0,
            stagger: 0.15,
            ease: "power3.out",
            delay: 0.8
        });

        gsap.from(".bottom-text", {
            duration: 0.8,
            y: 30,
            opacity: 0,
            ease: "power2.out",
            delay: 1
        });

        gsap.from(".signup-form-new", {
            duration: 0.8,
            y: 30,
            opacity: 0,
            ease: "power2.out",
            delay: 1.2
        });

        // Features section animations
        gsap.from(".features-heading", {
            scrollTrigger: {
                trigger: ".features-section",
                start: "top 80%"
            },
            duration: 1,
            y: 50,
            opacity: 0,
            ease: "power2.out"
        });

        gsap.utils.toArray(".feature-step").forEach((step, i) => {
            gsap.from(step.querySelector('.step-description'), {
                scrollTrigger: { 
                    trigger: step, 
                    start: 'top 85%',
                    once: true
                },
                duration: 0.8,
                y: 20,
                opacity: 0,
                ease: "power2.out",
                delay: i * 0.2 + 0.1
            });

            gsap.from(step.querySelector('.step-icon-circle'), {
                scrollTrigger: { 
                    trigger: step, 
                    start: 'top 85%',
                    once: true
                },
                duration: 0.8,
                scale: 0,
                rotation: 180,
                ease: "back.out(1.5)",
                delay: i * 0.2 + 0.2
            });
        });

        gsap.from(".paper-airplane", {
            scrollTrigger: {
                trigger: ".features-infographic",
                start: "top 80%",
                once: true
            },
            duration: 1,
            x: 100,
            y: -50,
            opacity: 0,
            rotation: 90,
            ease: "power2.out",
            delay: 1.2
        });

        // How It Works section animations
        gsap.from(".how-it-works-heading", {
            scrollTrigger: {
                trigger: ".how-it-works-section",
                start: "top 80%"
            },
            duration: 1,
            y: 50,
            opacity: 0,
            ease: "power2.out"
        });

        gsap.from(".steps-circle-outer", {
            scrollTrigger: {
                trigger: ".how-it-works-section",
                start: "top 70%",
                once: true
            },
            duration: 1.2,
            scale: 0,
            opacity: 0,
            ease: "back.out(1.5)",
            delay: 0.3
        });

        gsap.utils.toArray(".step-pill").forEach((pill, i) => {
            gsap.from(pill.querySelector('.pill-number'), {
                scrollTrigger: { 
                    trigger: pill, 
                    start: 'top 85%',
                    once: true
                },
                duration: 0.6,
                scale: 0,
                rotation: 360,
                opacity: 0,
                ease: "back.out(1.5)",
                delay: i * 0.1
            });

            gsap.from(pill.querySelector('.pill-content'), {
                scrollTrigger: { 
                    trigger: pill, 
                    start: 'top 85%',
                    once: true
                },
                duration: 0.8,
                x: 100,
                opacity: 0,
                ease: "power2.out",
                delay: i * 0.1 + 0.2
            });
        });

        // Testimonials section animations
        gsap.from(".career-testimonials-label", {
            scrollTrigger: {
                trigger: ".career-testimonials-fullbg",
                start: "top 80%"
            },
            duration: 1,
            y: 50,
            opacity: 0,
            ease: "power2.out"
        });

        gsap.from(".testimonial-slide-fullbg.active .testimonial-content-fullbg", {
            scrollTrigger: {
                trigger: ".career-testimonials-fullbg",
                start: "top 70%",
                once: true
            },
            duration: 1.2,
            y: 60,
            opacity: 0,
            ease: "power2.out",
            delay: 0.3
        });

        gsap.from(".testimonial-nav-fullbg", {
            scrollTrigger: {
                trigger: ".career-testimonials-fullbg",
                start: "top 80%"
            },
            duration: 0.8,
            y: 30,
            opacity: 0,
            ease: "power2.out",
            delay: 0.6
        });

        // Footer animations
        gsap.from(".footer-column", {
            scrollTrigger: {
                trigger: ".footer",
                start: "top 90%",
                once: true
            },
            duration: 0.8,
            y: 40,
            opacity: 0,
            stagger: 0.1,
            ease: "power2.out"
        });

        // Add smooth hover effects to buttons
        document.querySelectorAll('.btn-signup-new, .btn-login, .btn-signup').forEach(btn => {
            btn.addEventListener('mouseenter', function() {
                gsap.to(this, {
                    scale: 1.05,
                    duration: 0.3,
                    ease: "power2.out"
                });
            });
            
            btn.addEventListener('mouseleave', function() {
                gsap.to(this, {
                    scale: 1,
                    duration: 0.3,
                    ease: "power2.out"
                });
            });
        });

        // Smooth scroll reveal for footer logo
        gsap.from(".footer-logo", {
            scrollTrigger: {
                trigger: ".footer",
                start: "top 90%",
                once: true
            },
            duration: 1,
            x: -30,
            opacity: 0,
            ease: "power2.out"
        });

        // Animate social icons
        gsap.from(".social-icon", {
            scrollTrigger: {
                trigger: ".footer",
                start: "top 90%",
                once: true
            },
            duration: 0.6,
            scale: 0,
            opacity: 0,
            stagger: 0.1,
            ease: "back.out(1.7)",
            delay: 0.5
        });

        // Add parallax effect to hero image
        gsap.to(".hero-professional-image", {
            scrollTrigger: {
                trigger: ".students-hero-new",
                start: "top top",
                end: "bottom top",
                scrub: 1
            },
            y: 100,
            ease: "none"
        });
    </script>
    
    <!-- Testimonials Carousel Functionality -->
    <script>
        let currentTestimonial = 0;
        const testimonialSlides = document.querySelectorAll('.testimonial-slide-fullbg');
        const totalTestimonials = testimonialSlides.length;
        const btnPrev = document.getElementById('careerTestiPrev');
        const btnNext = document.getElementById('careerTestiNext');

        function showTestimonial(index) {
            testimonialSlides.forEach((slide, i) => {
                if (i === index) {
                    slide.classList.add('active');
                } else {
                    slide.classList.remove('active');
                }
            });
        }

        function nextTestimonial() {
            currentTestimonial = (currentTestimonial + 1) % totalTestimonials;
            showTestimonial(currentTestimonial);
        }

        function prevTestimonial() {
            currentTestimonial = (currentTestimonial - 1 + totalTestimonials) % totalTestimonials;
            showTestimonial(currentTestimonial);
        }

        if (btnPrev && btnNext) {
            btnPrev.addEventListener('click', prevTestimonial);
            btnNext.addEventListener('click', nextTestimonial);

            // Auto-advance every 6 seconds
            setInterval(nextTestimonial, 6000);

            // Keyboard navigation
            document.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowLeft') prevTestimonial();
                if (e.key === 'ArrowRight') nextTestimonial();
            });
        }

        // Initialize first slide
        showTestimonial(0);
    </script>
</body>
</html>
