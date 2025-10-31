<?php
// Pre-login version - no user session handling
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students - Avsar Nepal</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="css/students.css">
    
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
                    <li><a href="students.php" class="text-avsar-green font-semibold">Students</a></li>
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
                    <a href="signup.php" class="btn-signup hover:bg-gray-100 transition-colors duration-300" style="text-decoration: none; display: inline-block;">Sign up</a>
                    <a href="login.php" class="btn-login hover:bg-avsar-green/90 transition-colors duration-300" style="text-decoration: none; display: inline-block;">Log in</a>
                </div>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div x-show="mobileMenuOpen" x-transition class="md:hidden bg-avsar-dark border-t border-gray-700">
            <div class="px-4 py-2 space-y-2">
                <a href="students.php" class="block py-2 text-avsar-green font-semibold">Students</a>
                <a href="employers.php" class="block py-2 text-white hover:text-avsar-green transition-colors duration-300">Employers</a>
                <a href="career_centers.php" class="block py-2 text-white hover:text-avsar-green transition-colors duration-300">Career centers</a>

                <div class="pt-4 space-y-2">
                    <a href="signup.php" class="w-full btn-signup" style="display: block; text-align: center; text-decoration: none;">Sign up</a>
                    <a href="login.php" class="w-full btn-login" style="display: block; text-align: center; text-decoration: none;">Log in</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Students Hero Section -->
    <section class="students-hero-new">
        <div class="hero-container-new">
            <!-- Image with Text Overlay -->
            <div class="hero-image-container-full">
                <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=1920&h=1080&fit=crop" alt="Professional student" class="hero-professional-image">
                <div class="hero-text-overlay">
                    <h1 class="hero-main-title">
                        <span class="title-line">SHOW UP</span>
                        <span class="title-line">GET HIRED</span>
                    </h1>
                </div>
            </div>
        </div>

        <!-- Bottom Section -->
        <div class="hero-bottom-section">
            <div class="bottom-text">
                <p>Join the network built for starting or restarting your career.</p>
            </div>
            <div class="bottom-form">
                <form class="signup-form-new" id="studentsSignupForm" x-data="{ 
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
                        placeholder="Type school or personal email here" 
                        required
                        id="studentsEmailInput"
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
                        <span x-show="!isSubmitting">Sign up</span>
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
                Everything you need to <span class="features-highlight">launch your career</span>
            </h2>
            
            <div class="features-infographic">
                <!-- Step 1: Smart Job Matching -->
                <div class="feature-step">
                    <p class="step-description">
                        Our advanced algorithm matches you with jobs based on your skills, interests, and career goals. Get personalized job recommendations.
                    </p>
                    <div class="step-icon-circle step-icon-red">
                        <svg width="70" height="70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="12" cy="12" r="2"/>
                            <path d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83"/>
                        </svg>
                    </div>
                </div>

                <!-- Step 2: Skill Assessments -->
                <div class="feature-step">
                    <p class="step-description">
                        Test your abilities and get certified in various skills. Showcase your expertise to employers with verified skill badges and performance reports.
                    </p>
                    <div class="step-icon-circle step-icon-teal">
                        <svg width="70" height="70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M9 11l3 3L22 4"/>
                            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                        </svg>
                    </div>
                </div>

                <!-- Step 3: Career Guidance -->
                <div class="feature-step">
                    <p class="step-description">
                        Get personalized career counseling from industry experts. Receive guidance on career paths, interview preparation, and professional development.
                    </p>
                    <div class="step-icon-circle step-icon-yellow">
                        <svg width="70" height="70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="12" cy="12" r="10"/>
                            <circle cx="12" cy="12" r="6"/>
                            <circle cx="12" cy="12" r="2"/>
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
                Your journey to success in <span class="how-it-works-highlight">5 simple steps</span>
            </h2>
            
            <div class="steps-layout">
                <!-- Left: Circular Visual with Arc -->
                <div class="steps-visual">
                    <div class="steps-circle-outer">
                        <div class="steps-circle-middle">
                            <div class="steps-circle-inner">
                                <div class="steps-big-number">5</div>
                                <div class="steps-label">STEP</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Arc Path with Dots -->
                    <svg class="steps-arc-svg" viewBox="0 0 300 400" xmlns="http://www.w3.org/2000/svg">
                        <path class="arc-path" d="M 150 50 Q 250 100, 280 200 Q 290 300, 280 350" fill="none" stroke="url(#arcGradient)" stroke-width="3"/>
                        <defs>
                            <linearGradient id="arcGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" style="stop-color:#4d9fff;stop-opacity:1" />
                                <stop offset="100%" style="stop-color:#1e3a8a;stop-opacity:1" />
                            </linearGradient>
                        </defs>
                        
                        <!-- Dots on arc -->
                        <circle class="arc-dot dot-1" cx="205" cy="88" r="8" fill="#6dd5ed"/>
                        <circle class="arc-dot dot-2" cx="260" cy="160" r="8" fill="#c44bd4"/>
                        <circle class="arc-dot dot-3" cx="282" cy="245" r="8" fill="#d84040"/>
                        <circle class="arc-dot dot-4" cx="280" cy="305" r="8" fill="#9bc24b"/>
                        <circle class="arc-dot dot-5" cx="268" cy="355" r="8" fill="#2d3e87"/>
                    </svg>
                </div>

                <!-- Right: Step Pills -->
                <div class="steps-pills">
                    <div class="step-pill" data-step="1">
                        <div class="pill-number pill-color-1">1</div>
                        <div class="pill-content">
                            <h3 class="pill-title">Create Your Profile</h3>
                            <p class="pill-text">Sign up and build your professional profile. Add your education, skills, interests, and career goals.</p>
                        </div>
                    </div>

                    <div class="step-pill" data-step="2">
                        <div class="pill-number pill-color-2">2</div>
                        <div class="pill-content">
                            <h3 class="pill-title">Complete Skill Assessments</h3>
                            <p class="pill-text">Take comprehensive skill tests to validate your abilities and earn certifications employers trust.</p>
                        </div>
                    </div>

                    <div class="step-pill" data-step="3">
                        <div class="pill-number pill-color-3">3</div>
                        <div class="pill-content">
                            <h3 class="pill-title">Browse Jobs</h3>
                            <p class="pill-text">Explore thousands of job postings and internships from top companies across Nepal.</p>
                        </div>
                    </div>

                    <div class="step-pill" data-step="4">
                        <div class="pill-number pill-color-4">4</div>
                        <div class="pill-content">
                            <h3 class="pill-title">Get Matched with Jobs</h3>
                            <p class="pill-text">Our AI-powered matching system connects you with the most relevant jobs for your profile.</p>
                        </div>
                    </div>

                    <div class="step-pill" data-step="5">
                        <div class="pill-number pill-color-5">5</div>
                        <div class="pill-content">
                            <h3 class="pill-title">Apply and Succeed</h3>
                            <p class="pill-text">Submit applications with confidence and track your progress. Get hired for your dream job!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials-section-new">
        <div class="testimonials-container-new">
            <h2 class="testimonials-heading-new">
                For people who are <span class="testimonials-highlight">(or have been)</span> in your shoes
            </h2>

            <!-- Testimonials Carousel -->
            <div class="testimonials-carousel-new">
                <div class="testimonials-track-new">
                    <!-- Testimonial 1 -->
                    <div class="testimonial-card-new">
                        <div class="testimonial-content-new">
                            <div class="testimonial-text-box">
                                <blockquote class="testimonial-quote-new">
                                    "Avsar Nepal has helped me so much beyond just finding a job... being able to track my progress, showcase my experiences, and see how others are growing... has truly helped me build confidence, gain clarity about my field, and feel more empowered in my professional journey."
                                </blockquote>
                                <div class="testimonial-author-new">
                                    <div class="author-name-new">Rohit Adhikari (he/him)</div>
                                    <div class="author-university-new">Tribhuvan University</div>
                                    <div class="author-class-new">Class of 2024</div>
                                </div>
                            </div>
                            <div class="testimonial-image-new">
                                <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=500&h=500&fit=crop&crop=face" alt="Rohit Adhikari" class="testimonial-img-circular">
                            </div>
                        </div>
                    </div>

                    <!-- Testimonial 2 -->
                    <div class="testimonial-card-new">
                        <div class="testimonial-content-new">
                            <div class="testimonial-text-box testimonial-box-orange">
                                <blockquote class="testimonial-quote-new">
                                    "Avsar Nepal allowed me to gain tips and tricks and connect with other students who are in the same boat. I've been able to learn a lot about resumes, career fairs, and other things that have helped me learn how to navigate college and be successful."
                                </blockquote>
                                <div class="testimonial-author-new">
                                    <div class="author-name-new">Sujata Karki (she/her)</div>
                                    <div class="author-university-new">Pokhara University</div>
                                    <div class="author-class-new">Class of 2026</div>
                                </div>
                            </div>
                            <div class="testimonial-image-new">
                                <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=500&h=500&fit=crop&crop=face" alt="Sujata Karki" class="testimonial-img-circular">
                            </div>
                        </div>
                    </div>

                    <!-- Testimonial 3 -->
                    <div class="testimonial-card-new">
                        <div class="testimonial-content-new">
                            <div class="testimonial-text-box testimonial-box-green">
                                <blockquote class="testimonial-quote-new">
                                    "Through Avsar Nepal, I discovered my passion for tech and secured my first internship. The mentorship and resources available helped me build projects that got me noticed by recruiters. I'm now working at my dream company!"
                                </blockquote>
                                <div class="testimonial-author-new">
                                    <div class="author-name-new">Aayush Shrestha (he/him)</div>
                                    <div class="author-university-new">Kathford International College</div>
                                    <div class="author-class-new">Class of 2025</div>
                                </div>
                            </div>
                            <div class="testimonial-image-new">
                                <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=500&h=500&fit=crop&crop=face" alt="Aayush Shrestha" class="testimonial-img-circular">
                            </div>
                        </div>
                    </div>

                    <!-- Testimonial 4 -->
                    <div class="testimonial-card-new">
                        <div class="testimonial-content-new">
                            <div class="testimonial-text-box testimonial-box-purple">
                                <blockquote class="testimonial-quote-new">
                                    "The networking on Avsar Nepal is incredible. I connected with alumni who gave me valuable advice and even referred me to their companies. It's more than a job board—it's a community that cares about your success."
                                </blockquote>
                                <div class="testimonial-author-new">
                                    <div class="author-name-new">Nisha Khadka (she/her)</div>
                                    <div class="author-university-new">Patan College</div>
                                    <div class="author-class-new">Class of 2023</div>
                                </div>
                            </div>
                            <div class="testimonial-image-new">
                                <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=500&h=500&fit=crop&crop=face" alt="Nisha Khadka" class="testimonial-img-circular">
                            </div>
                        </div>
                    </div>

                    <!-- Testimonial 5 -->
                    <div class="testimonial-card-new">
                        <div class="testimonial-content-new">
                            <div class="testimonial-text-box testimonial-box-teal">
                                <blockquote class="testimonial-quote-new">
                                    "From workshops to real job leads, Avsar Nepal gave me clarity on where I'm headed and how to get there. The skill assessments helped me identify my strengths and the career guidance was personalized and actionable."
                                </blockquote>
                                <div class="testimonial-author-new">
                                    <div class="author-name-new">Bibek Rai (he/him)</div>
                                    <div class="author-university-new">Islington College</div>
                                    <div class="author-class-new">Class of 2026</div>
                                </div>
                            </div>
                            <div class="testimonial-image-new">
                                <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=500&h=500&fit=crop&crop=face" alt="Bibek Rai" class="testimonial-img-circular">
                            </div>
                        </div>
                    </div>

                    <!-- Testimonial 6 -->
                    <div class="testimonial-card-new">
                        <div class="testimonial-content-new">
                            <div class="testimonial-text-box testimonial-box-pink">
                                <blockquote class="testimonial-quote-new">
                                    "I was struggling to balance my studies and job search, but Avsar Nepal made it so much easier. The platform is intuitive, the jobs are relevant, and I found multiple internships that fit my schedule perfectly. Highly recommend!"
                                </blockquote>
                                <div class="testimonial-author-new">
                                    <div class="author-name-new">Priya Tamang (she/her)</div>
                                    <div class="author-university-new">Tribhuvan University</div>
                                    <div class="author-class-new">Class of 2025</div>
                                </div>
                            </div>
                            <div class="testimonial-image-new">
                                <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=500&h=500&fit=crop&crop=face" alt="Priya Tamang" class="testimonial-img-circular">
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Navigation Arrows -->
            <div class="testimonials-navigation-new">
                <button class="testimonial-arrow-new testimonial-prev-new" id="testiPrevNew" aria-label="Previous testimonial">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M15 18l-6-6 6-6"/>
                    </svg>
                </button>
                <button class="testimonial-arrow-new testimonial-next-new" id="testiNextNew" aria-label="Next testimonial">
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
    <script src="students.js"></script>
    
    <!-- GSAP Animations for Students Page -->
    <script>
        // GSAP Animations
        gsap.registerPlugin(ScrollTrigger);

        // Animate hero image container
        gsap.from(".hero-image-container-full", {
            duration: 1,
            scale: 0.95,
            opacity: 0,
            ease: "power2.out",
            delay: 0.3
        });

        // Animate hero title
        gsap.from(".hero-main-title .title-line", {
            duration: 1.2,
            y: 50,
            opacity: 0,
            stagger: 0.15,
            ease: "power3.out",
            delay: 0.8
        });

        // Animate bottom section
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

        // Animate dotted line
        gsap.from(".features-infographic::before", {
            scrollTrigger: {
                trigger: ".features-infographic",
                start: "top 80%",
                once: true
            },
            duration: 1.5,
            scaleX: 0,
            ease: "power2.out",
            delay: 0.3
        });

        // Animate feature steps
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

        // Animate paper airplane
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

        // Animate circular visual
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

        // Animate step pills
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
        gsap.from(".testimonials-heading-new", {
            scrollTrigger: {
                trigger: ".testimonials-section-new",
                start: "top 80%"
            },
            duration: 1,
            y: 50,
            opacity: 0,
            ease: "power2.out"
        });

        gsap.utils.toArray(".testimonial-card-new").forEach((card, i) => {
            gsap.from(card.querySelector('.testimonial-text-box'), {
                scrollTrigger: { 
                    trigger: card, 
                    start: 'top 80%',
                    once: true
                },
                duration: 1,
                x: -100,
                opacity: 0,
                ease: "power2.out"
            });

            gsap.from(card.querySelector('.testimonial-image-new'), {
                scrollTrigger: { 
                    trigger: card, 
                    start: 'top 80%',
                    once: true
                },
                duration: 1,
                scale: 0.8,
                opacity: 0,
                ease: "back.out(1.2)",
                delay: 0.2
            });
        });

        gsap.from(".testimonials-navigation-new", {
            scrollTrigger: {
                trigger: ".testimonials-section-new",
                start: "top 80%"
            },
            duration: 0.8,
            y: 30,
            opacity: 0,
            ease: "power2.out",
            delay: 0.6
        });

        // Testimonials carousel functionality
        const carouselNew = document.querySelector('.testimonials-carousel-new');
        const btnPrevNew = document.getElementById('testiPrevNew');
        const btnNextNew = document.getElementById('testiNextNew');
        
        if (carouselNew && btnPrevNew && btnNextNew) {
            const slideWidth = () => carouselNew.clientWidth;
            
            btnPrevNew.addEventListener('click', () => {
                carouselNew.scrollBy({ 
                    left: -slideWidth(), 
                    behavior: 'smooth' 
                });
            });
            
            btnNextNew.addEventListener('click', () => {
                carouselNew.scrollBy({ 
                    left: slideWidth(), 
                    behavior: 'smooth' 
                });
            });

            // Optional: Add keyboard navigation
            document.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowLeft') {
                    carouselNew.scrollBy({ 
                        left: -slideWidth(), 
                        behavior: 'smooth' 
                    });
                } else if (e.key === 'ArrowRight') {
                    carouselNew.scrollBy({ 
                        left: slideWidth(), 
                        behavior: 'smooth' 
                    });
                }
            });
        }

    </script>
</body>
</html>
