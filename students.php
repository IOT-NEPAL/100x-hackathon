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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students - Avsar Nepal</title>
    
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
                        'avsar-dark': '#0f172a',
                        'avsar-gold': '#ccff00',
                        'avsar-green': '#00ff41'
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
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/students.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
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
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar-students" role="navigation" aria-label="Main navigation">
        <div class="nav-container-students">
            <a href="index.php" class="nav-logo-students">
                <img src="loogo.png" alt="Avsar Nepal Logo">
                <span>Avsar Nepal</span>
            </a>
            
            <!-- Desktop Menu -->
            <ul class="nav-menu-students">
                <li><a href="students.php" class="active">Students</a></li>
                <li><a href="index.php#employers">Employers</a></li>
                <li><a href="index.php#career-centers">Career centers</a></li>
            </ul>
            
            <!-- User Section -->
            <div class="nav-buttons-students" x-data="{ mobileMenuOpen: false }">
                <a href="signin.php" class="btn-nav-students btn-outline">Sign up</a>
                <a href="login.php" class="btn-nav-students btn-solid">Log in</a>
                
                <!-- Mobile Menu Button -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="mobile-menu-btn" aria-label="Toggle mobile menu">
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
                    <a href="index.php#employers" class="block text-white py-2 px-4 hover:bg-gray-900 rounded">Employers</a>
                    <a href="index.php#career-centers" class="block text-white py-2 px-4 hover:bg-gray-900 rounded">Career centers</a>
                    <div class="border-t border-gray-700 pt-2 mt-2 space-y-2">
                        <a href="signin.php" class="block text-center py-2 px-4 border-2 rounded transition-colors" style="border-color: #00ff41; color: #00ff41;" onmouseover="this.style.backgroundColor='#00ff41'; this.style.color='#000';" onmouseout="this.style.backgroundColor='transparent'; this.style.color='#00ff41';">Sign up</a>
                        <a href="login.php" class="block text-center py-2 px-4 rounded transition-colors" style="background-color: #00ff41; color: #000;" onmouseover="this.style.backgroundColor='#ccff00';" onmouseout="this.style.backgroundColor='#00ff41';">Log in</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="students-hero-new">
        <div class="hero-container-new">
            <!-- Image Container with Text Overlay -->
            <div class="hero-image-container-full">
                <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=1920&h=1080&fit=crop" alt="Students networking and learning">
                <div class="hero-text-overlay">
                    <h1 class="hero-main-title">
                        <span class="title-line">SHOW UP</span>
                        <span class="title-line">GET HIRED</span>
                    </h1>
                </div>
            </div>
            
            <!-- Bottom Section -->
            <div class="hero-bottom-section">
                <div class="hero-bottom-text">
                    Join the network built for starting or restarting your career.
                </div>
                
                <form class="signup-form-new" 
                      x-data="{ 
                          email: '', 
                          isValid: false,
                          isSubmitting: false,
                          validateEmail() {
                              const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                              this.isValid = regex.test(this.email);
                              return this.isValid;
                          }
                      }"
                      @submit.prevent="if(validateEmail() && !isSubmitting) { isSubmitting = true; setTimeout(() => { window.location.href = 'signin.php'; }, 1000); }">
                    <input 
                        type="email" 
                        class="email-input-new"
                        :class="{ 'valid': isValid && email, 'invalid': email && !isValid }"
                        placeholder="Type school or personal email here"
                        x-model="email"
                        @input="validateEmail()"
                        required
                        aria-label="Email address">
                    <button 
                        type="submit" 
                        class="btn-signup-new"
                        :disabled="!isValid || isSubmitting">
                        <span x-show="!isSubmitting">Sign up</span>
                        <span x-show="isSubmitting">Signing up...</span>
                    </button>
                </form>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="features-container">
            <h2 class="features-heading">
                Everything you need to <span class="features-highlight">launch your career</span>
            </h2>
            
            <div class="features-infographic" style="position: relative;">
                <!-- Paper Airplane SVG -->
                <svg class="paper-airplane" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M10 50 L50 10 L90 50 L50 70 Z" fill="#00ff41" opacity="0.5"/>
                    <path d="M30 50 L50 30 L70 50 L50 60 Z" fill="#ccff00" opacity="0.7"/>
                </svg>
                
                <!-- Dotted Line -->
                <div class="dotted-line"></div>
                
                <!-- Feature Steps -->
                <div class="feature-step">
                    <div class="step-icon-circle red">
                        <i class="fas fa-compass"></i>
                    </div>
                    <p class="step-description">
                        Our advanced algorithm matches you with jobs that fit your skills and career goals.
                    </p>
                </div>
                
                <div class="feature-step">
                    <div class="step-icon-circle teal">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <p class="step-description">
                        Test your abilities and get certified by industry experts to boost your profile.
                    </p>
                </div>
                
                <div class="feature-step">
                    <div class="step-icon-circle yellow">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <p class="step-description">
                        Get personalized career counseling and guidance from experienced professionals.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="how-it-works-section">
        <div class="how-it-works-container">
            <h2 class="how-it-works-heading">
                Your journey to success in <span class="features-highlight">5 simple steps</span>
            </h2>
            
            <div class="steps-layout">
                <!-- Left: Visual -->
                <div class="steps-visual">
                    <div class="steps-circle-outer">
                        <div class="steps-circle-middle">
                            <div class="steps-circle-inner">
                                5 STEP
                            </div>
                        </div>
                    </div>
                    
                    <!-- SVG Arc with 5 Dots -->
                    <svg class="steps-arc" viewBox="0 0 350 350">
                        <circle cx="175" cy="175" r="150" fill="none" stroke="#00ff41" stroke-width="2" stroke-dasharray="5,5" opacity="0.5"/>
                        <!-- 5 Colored Dots -->
                        <circle cx="175" cy="25" r="8" fill="#ff4444"/>
                        <circle cx="325" cy="125" r="8" fill="#ffaa00"/>
                        <circle cx="325" cy="225" r="8" fill="#00ff41"/>
                        <circle cx="175" cy="325" r="8" fill="#00ccaa"/>
                        <circle cx="25" cy="225" r="8" fill="#ccff00"/>
                    </svg>
                </div>
                
                <!-- Right: Steps Pills -->
                <div class="steps-pills">
                    <div class="step-pill">
                        <div class="pill-number one">1</div>
                        <div class="pill-content">
                            <h3>Create Your Profile</h3>
                            <p>Sign up and build your professional profile with your skills, education, and experience.</p>
                        </div>
                    </div>
                    
                    <div class="step-pill">
                        <div class="pill-number two">2</div>
                        <div class="pill-content">
                            <h3>Complete Skill Assessments</h3>
                            <p>Take skill tests and get verified by industry experts to showcase your abilities.</p>
                        </div>
                    </div>
                    
                    <div class="step-pill">
                        <div class="pill-number three">3</div>
                        <div class="pill-content">
                            <h3>Browse Jobs</h3>
                            <p>Explore hundreds of job postings and internships from verified employers.</p>
                        </div>
                    </div>
                    
                    <div class="step-pill">
                        <div class="pill-number four">4</div>
                        <div class="pill-content">
                            <h3>Get Matched with Jobs</h3>
                            <p>Our AI-powered matching algorithm connects you with opportunities that fit you best.</p>
                        </div>
                    </div>
                    
                    <div class="step-pill">
                        <div class="pill-number five">5</div>
                        <div class="pill-content">
                            <h3>Apply and Succeed</h3>
                            <p>Submit applications and start your career journey with confidence.</p>
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
                For people who are <span class="features-highlight">(or have been)</span> in your shoes
            </h2>
            
            <div class="testimonials-carousel-new" 
                 x-data="{ 
                     currentIndex: 0,
                     scrollContainer: null,
                     init() {
                         this.scrollContainer = this.$el.querySelector('.testimonials-track-new');
                         this.updateScroll();
                     },
                     next() {
                         if (this.currentIndex < 5) this.currentIndex++;
                         this.updateScroll();
                     },
                     prev() {
                         if (this.currentIndex > 0) this.currentIndex--;
                         this.updateScroll();
                     },
                     updateScroll() {
                         if (this.scrollContainer) {
                             const cardWidth = 350 + 32; // card width + gap
                             this.scrollContainer.style.transform = `translateX(-${this.currentIndex * cardWidth}px)`;
                         }
                     }
                 }"
                 @keydown.left.window="$event.key === 'ArrowLeft' && prev()"
                 @keydown.right.window="$event.key === 'ArrowRight' && next()">
                <div class="testimonials-track-new">
                    <!-- Testimonial 1 -->
                    <div class="testimonial-card-new">
                        <div class="testimonial-text-box">
                            <p class="testimonial-quote-new">
                                "Avsar Nepal helped me find my first internship right after graduation. The platform made it so easy to connect with employers."
                            </p>
                        </div>
                        <div class="testimonial-author-new">
                            <div class="testimonial-author-info">
                                <div class="testimonial-author-name">Rohit Adhikari <span style="color: #666; font-weight: normal;">(he/him)</span></div>
                                <div class="testimonial-author-details">Tribhuvan University<br>Class of 2024</div>
                            </div>
                            <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&h=400&fit=crop&crop=face" alt="Rohit Adhikari" class="testimonial-image-new">
                        </div>
                    </div>
                    
                    <!-- Testimonial 2 -->
                    <div class="testimonial-card-new orange">
                        <div class="testimonial-text-box">
                            <p class="testimonial-quote-new">
                                "The skill verification feature boosted my confidence and helped me stand out to employers. Highly recommend!"
                            </p>
                        </div>
                        <div class="testimonial-author-new">
                            <div class="testimonial-author-info">
                                <div class="testimonial-author-name">Sujata Karki <span style="color: #666; font-weight: normal;">(she/her)</span></div>
                                <div class="testimonial-author-details">Pokhara University<br>Class of 2026</div>
                            </div>
                            <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=400&h=400&fit=crop&crop=face" alt="Sujata Karki" class="testimonial-image-new">
                        </div>
                    </div>
                    
                    <!-- Testimonial 3 -->
                    <div class="testimonial-card-new green">
                        <div class="testimonial-text-box">
                            <p class="testimonial-quote-new">
                                "I found multiple job opportunities that matched my skills perfectly. The AI matching is incredibly accurate."
                            </p>
                        </div>
                        <div class="testimonial-author-new">
                            <div class="testimonial-author-info">
                                <div class="testimonial-author-name">Aayush Shrestha <span style="color: #666; font-weight: normal;">(he/him)</span></div>
                                <div class="testimonial-author-details">Kathford International College<br>Class of 2025</div>
                            </div>
                            <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=400&h=400&fit=crop&crop=face" alt="Aayush Shrestha" class="testimonial-image-new">
                        </div>
                    </div>
                    
                    <!-- Testimonial 4 -->
                    <div class="testimonial-card-new purple">
                        <div class="testimonial-text-box">
                            <p class="testimonial-quote-new">
                                "After a career break, Avsar Nepal helped me restart my journey. The support and resources are amazing."
                            </p>
                        </div>
                        <div class="testimonial-author-new">
                            <div class="testimonial-author-info">
                                <div class="testimonial-author-name">Nisha Khadka <span style="color: #666; font-weight: normal;">(she/her)</span></div>
                                <div class="testimonial-author-details">Patan College<br>Class of 2023</div>
                            </div>
                            <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=400&h=400&fit=crop&crop=face" alt="Nisha Khadka" class="testimonial-image-new">
                        </div>
                    </div>
                    
                    <!-- Testimonial 5 -->
                    <div class="testimonial-card-new teal">
                        <div class="testimonial-text-box">
                            <p class="testimonial-quote-new">
                                "The career guidance feature provided personalized advice that helped me choose the right path for my future."
                            </p>
                        </div>
                        <div class="testimonial-author-new">
                            <div class="testimonial-author-info">
                                <div class="testimonial-author-name">Bibek Rai <span style="color: #666; font-weight: normal;">(he/him)</span></div>
                                <div class="testimonial-author-details">Islington College<br>Class of 2026</div>
                            </div>
                            <img src="https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=400&h=400&fit=crop&crop=face" alt="Bibek Rai" class="testimonial-image-new">
                        </div>
                    </div>
                    
                    <!-- Testimonial 6 -->
                    <div class="testimonial-card-new pink">
                        <div class="testimonial-text-box">
                            <p class="testimonial-quote-new">
                                "I love how easy it is to apply for jobs and track my applications. This platform is a game-changer!"
                            </p>
                        </div>
                        <div class="testimonial-author-new">
                            <div class="testimonial-author-info">
                                <div class="testimonial-author-name">Priya Tamang <span style="color: #666; font-weight: normal;">(she/her)</span></div>
                                <div class="testimonial-author-details">Tribhuvan University<br>Class of 2025</div>
                            </div>
                            <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=400&h=400&fit=crop&crop=face" alt="Priya Tamang" class="testimonial-image-new">
                        </div>
                    </div>
                </div>
                
                <!-- Navigation Buttons -->
                <div class="testimonials-navigation-new">
                    <button @click="prev()" class="btn-carousel" id="testiPrevNew" aria-label="Previous testimonial">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button @click="next()" class="btn-carousel" id="testiNextNew" aria-label="Next testimonial">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer-students">
        <div class="footer-container-students">
            <div class="footer-grid-students">
                <!-- Column 1: Brand -->
                <div class="footer-brand-students">
                    <div class="footer-logo-students">
                        <img src="loogo.png" alt="Avsar Nepal Logo">
                        <span>Avsar Nepal</span>
                    </div>
                    <p class="footer-tagline-students">
                        Connecting talent with opportunity across Nepal
                    </p>
                </div>
                
                <!-- Column 2: For Students -->
                <div class="footer-column-students">
                    <h4>For Students</h4>
                    <ul>
                        <li><a href="opportunities.php">Find Jobs</a></li>
                        <li><a href="opportunities.php?type=internship">Internships</a></li>
                        <li><a href="user/career-guidance-ai.php">Career Advice</a></li>
                        <li><a href="#events">Events</a></li>
                    </ul>
                </div>
                
                <!-- Column 3: For Employers -->
                <div class="footer-column-students">
                    <h4>For Employers</h4>
                    <ul>
                        <li><a href="organizer/add-opportunity.php">Post a Job</a></li>
                        <li><a href="organizer/hire-talent.php">Hire Talent</a></li>
                        <li><a href="#pricing">Pricing</a></li>
                        <li><a href="#resources">Resources</a></li>
                    </ul>
                </div>
                
                <!-- Column 4: Company -->
                <div class="footer-column-students">
                    <h4>Company</h4>
                    <ul>
                        <li><a href="#about">About Us</a></li>
                        <li><a href="#contact">Contact</a></li>
                        <li><a href="#blog">Blog</a></li>
                        <li><a href="#careers">Careers</a></li>
                    </ul>
                </div>
                
                <!-- Column 5: Connect -->
                <div class="footer-column-students">
                    <h4>Connect</h4>
                    <div class="footer-social-students">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            
            <!-- Footer Bottom -->
            <div class="footer-bottom-students">
                <p>&copy; 2024 Avsar Nepal. All rights reserved.</p>
                <div class="footer-links-students">
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

        // Focus management - focus title on page load
        document.addEventListener('DOMContentLoaded', () => {
            const heroTitle = document.querySelector('.hero-main-title');
            if (heroTitle) {
                heroTitle.setAttribute('tabindex', '-1');
                heroTitle.focus();
            }
        });

        // GSAP Animations
        // Hero Section
        gsap.from('.hero-image-container-full', {
            opacity: 0,
            y: 50,
            duration: 1,
            ease: 'power3.out'
        });

        gsap.from('.title-line', {
            opacity: 0,
            y: 30,
            stagger: 0.2,
            duration: 0.8,
            ease: 'power3.out',
            delay: 0.3
        });

        gsap.from('.hero-bottom-section > *', {
            opacity: 0,
            x: -30,
            stagger: 0.2,
            duration: 0.8,
            ease: 'power3.out',
            delay: 0.6
        });

        // Features Section
        gsap.from('.features-heading', {
            scrollTrigger: {
                trigger: '.features-section',
                start: 'top 80%'
            },
            opacity: 0,
            y: 30,
            duration: 0.8,
            ease: 'power3.out'
        });

        gsap.from('.feature-step', {
            scrollTrigger: {
                trigger: '.features-infographic',
                start: 'top 80%'
            },
            opacity: 0,
            y: 30,
            stagger: 0.2,
            duration: 0.8,
            ease: 'power3.out'
        });

        gsap.from('.step-icon-circle', {
            scrollTrigger: {
                trigger: '.features-infographic',
                start: 'top 80%'
            },
            scale: 0,
            rotation: -180,
            stagger: 0.2,
            duration: 0.8,
            ease: 'back.out(1.7)'
        });

        gsap.from('.paper-airplane', {
            scrollTrigger: {
                trigger: '.features-section',
                start: 'top 80%'
            },
            x: -100,
            opacity: 0,
            duration: 1,
            ease: 'power3.out'
        });

        // How It Works Section
        gsap.from('.how-it-works-heading', {
            scrollTrigger: {
                trigger: '.how-it-works-section',
                start: 'top 80%'
            },
            opacity: 0,
            y: 30,
            duration: 0.8,
            ease: 'power3.out'
        });

        gsap.from('.steps-visual', {
            scrollTrigger: {
                trigger: '.steps-layout',
                start: 'top 80%'
            },
            scale: 0,
            duration: 1,
            ease: 'back.out(1.7)'
        });

        gsap.from('.step-pill', {
            scrollTrigger: {
                trigger: '.steps-pills',
                start: 'top 80%'
            },
            opacity: 0,
            x: -50,
            stagger: 0.15,
            duration: 0.6,
            ease: 'power3.out'
        });

        gsap.from('.pill-number', {
            scrollTrigger: {
                trigger: '.steps-pills',
                start: 'top 80%'
            },
            rotation: -360,
            scale: 0,
            stagger: 0.15,
            duration: 0.8,
            ease: 'back.out(1.7)'
        });

        // Testimonials Section
        gsap.from('.testimonials-heading-new', {
            scrollTrigger: {
                trigger: '.testimonials-section-new',
                start: 'top 80%'
            },
            opacity: 0,
            y: 30,
            duration: 0.8,
            ease: 'power3.out'
        });

        gsap.from('.testimonial-card-new', {
            scrollTrigger: {
                trigger: '.testimonials-carousel-new',
                start: 'top 80%'
            },
            opacity: 0,
            x: -100,
            stagger: 0.15,
            duration: 0.8,
            ease: 'power3.out'
        });

        gsap.from('.testimonial-image-new', {
            scrollTrigger: {
                trigger: '.testimonials-carousel-new',
                start: 'top 80%'
            },
            scale: 0,
            stagger: 0.15,
            duration: 0.6,
            ease: 'back.out(1.7)'
        });
    </script>
</body>
</html>

