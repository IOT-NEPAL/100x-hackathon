<?php
// Pre-login version - no user session handling
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avsar Nepal - Careers Start Here</title>
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
    
    <!-- GSAP -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

    <!-- Hero Section -->
    <section class="hero">
        <!-- Floating Images/Videos -->
        <div class="floating-media">
            <!-- Top Row - Left -->
            <div class="media-item" style="top: 2%; left: 8%;">
                <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=300&h=200&fit=crop" alt="Student Success Story" style="width: 100%; height: 100%; object-fit: cover; border-radius: 12px;">
            </div>
            
            <!-- Top Row - Center Left -->
            <div class="media-item" style="top: 5%; left: 28%;">
                <img src="https://images.unsplash.com/photo-1531482615713-2afd69097998?w=300&h=200&fit=crop" alt="Career Workshop" style="width: 100%; height: 100%; object-fit: cover; border-radius: 12px;">
            </div>
            
            <!-- Top Row - Center Right -->
            <div class="media-item" style="top: 3%; right: 28%;">
                <img src="https://images.unsplash.com/photo-1541339907198-e08756dedf3f?w=300&h=200&fit=crop" alt="University Students" style="width: 100%; height: 100%; object-fit: cover; border-radius: 12px;">
            </div>
            
            <!-- Top Row - Right -->
            <div class="media-item" style="top: 6%; right: 8%;">
                <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=300&h=200&fit=crop" alt="Team Collaboration" style="width: 100%; height: 100%; object-fit: cover; border-radius: 12px;">
            </div>
            
            <!-- Middle Row - Far Left -->
            <div class="media-item" style="top: 48%; left: 3%;">
                <img src="https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?w=300&h=200&fit=crop" alt="Business Meeting" style="width: 100%; height: 100%; object-fit: cover; border-radius: 12px;">
            </div>
            
            <!-- Middle Row - Far Right -->
            <div class="media-item" style="top: 45%; right: 3%;">
                <img src="https://images.unsplash.com/photo-1556761175-5973dc0f32e7?w=300&h=200&fit=crop" alt="Networking Event" style="width: 100%; height: 100%; object-fit: cover; border-radius: 12px;">
            </div>
            
            <!-- Bottom Row - Left -->
            <div class="media-item" style="bottom: 8%; left: 6%;">
                <img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=300&h=200&fit=crop" alt="Job Interview" style="width: 100%; height: 100%; object-fit: cover; border-radius: 12px;">
            </div>
            
            <!-- Bottom Row - Center Left -->
            <div class="media-item" style="bottom: 5%; left: 26%;">
                <img src="https://images.unsplash.com/photo-1521737711867-e3b97375f902?w=300&h=200&fit=crop" alt="Office Team" style="width: 100%; height: 100%; object-fit: cover; border-radius: 12px;">
            </div>
            
            <!-- Bottom Row - Center Right -->
            <div class="media-item" style="bottom: 6%; right: 26%;">
                <img src="https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=300&h=200&fit=crop" alt="Study Group" style="width: 100%; height: 100%; object-fit: cover; border-radius: 12px;">
            </div>
            
            <!-- Bottom Row - Right -->
            <div class="media-item" style="bottom: 10%; right: 6%;">
                <img src="https://images.unsplash.com/photo-1556761175-4b46a572b786?w=300&h=200&fit=crop" alt="Career Fair" style="width: 100%; height: 100%; object-fit: cover; border-radius: 12px;">
            </div>
        </div>

        <!-- Main Content -->
        <div class="hero-content">
            <h1 class="hero-subtitle">Avsar Nepal</h1>
            <h2 class="hero-title">
                Careers<br>
                start here
            </h2>
            
            <!-- Email Input Form -->
            <form class="phone-form" id="signupForm" x-data="{ 
                email: '', 
                isValid: false,
                isSubmitting: false,
                checkEmail() {
                    this.isValid = this.email.includes('@') && this.email.length > 5;
                }
            }" @submit="isSubmitting = true">
                <input 
                    type="email" 
                    class="phone-input transition-all duration-300 focus:ring-2 focus:ring-gray-400 focus:border-gray-400" 
                    placeholder="Enter your email *" 
                    required
                    id="emailInput"
                    x-model="email"
                    @input="checkEmail()"
                    :class="{ 'border-gray-400': isValid, 'border-red-500': email && !isValid }"
                    style="color: #000000;"
                >
                <button 
                    type="submit" 
                    class="btn-get-app transition-all duration-300 hover:scale-105 hover:shadow-lg"
                    :disabled="!isValid || isSubmitting"
                    :class="{ 'opacity-50 cursor-not-allowed': !isValid || isSubmitting }"
                >
                    <span x-show="!isSubmitting">Get started now</span>
                    <span x-show="isSubmitting" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-black" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Processing...
                    </span>
                </button>
            </form>

            <!-- Disclaimer -->
            <p class="disclaimer">
                *By entering my email and clicking 'Get started now', I agree to Avsar Nepal's 
                <a href="#terms">terms of service</a> and <a href="#privacy">privacy policy</a>.
            </p>
        </div>
    </section>

    <!-- Jobs Section -->
    <section class="jobs-section">
        <h2 class="jobs-title">Jobs and internships you actually want</h2>
        
        <!-- Row 1 - Scrolls Left -->
        <div class="jobs-row">
            <div class="jobs-track jobs-track-left">
                <!-- Job Card 1 -->
                <div class="job-card">
                    <div class="job-card-header">
                        <div class="company-badge">
                            <img src="https://via.placeholder.com/50" alt="USDA" class="company-icon">
                        </div>
                        <div class="company-info">
                            <h3 class="company-name">USDA Forest Service</h3>
                            <p class="company-category">Forestry</p>
                        </div>
                    </div>
                    <h4 class="job-position">Wildland Firefighter</h4>
                    <p class="job-salary">$32–78K/yr · Full-time job</p>
                    <p class="job-location">Eureka, CA</p>
                </div>

                <!-- Job Card 2 -->
                <div class="job-card">
                    <div class="job-card-header">
                        <div class="company-badge">
                            <img src="https://via.placeholder.com/50" alt="Waffle House" class="company-icon">
                        </div>
                        <div class="company-info">
                            <h3 class="company-name">Waffle House, Inc.</h3>
                            <p class="company-category">Restaurants & food service</p>
                        </div>
                    </div>
                    <h4 class="job-position">Restaurant Operations Management</h4>
                    <p class="job-salary">$63–80K/yr · Full-time job</p>
                    <p class="job-location">Fayetteville, AR</p>
                </div>

                <!-- Job Card 3 -->
                <div class="job-card">
                    <div class="job-card-header">
                        <div class="company-badge">
                            <img src="https://via.placeholder.com/50" alt="Qualcomm" class="company-icon">
                        </div>
                        <div class="company-info">
                            <h3 class="company-name">Qualcomm</h3>
                            <p class="company-category">Electronic & computer hardware</p>
                        </div>
                    </div>
                    <h4 class="job-position">Machine Learning & Artificial Intelligence Engineering Intern</h4>
                    <p class="job-salary">$17–98/hr · Internship</p>
                    <p class="job-location">San Diego, CA</p>
                </div>

                <!-- Job Card 4 -->
                <div class="job-card">
                    <div class="job-card-header">
                        <div class="company-badge">
                            <img src="https://via.placeholder.com/50" alt="Activision" class="company-icon">
                        </div>
                        <div class="company-info">
                            <h3 class="company-name">Activision Blizzard</h3>
                            <p class="company-category">Internet & software</p>
                        </div>
                    </div>
                    <h4 class="job-position">Graphic Design Intern</h4>
                    <p class="job-salary">$25–43/hr · Internship</p>
                    <p class="job-location">New York, NY (Hybrid)</p>
                </div>

                <!-- Job Card 5 -->
                <div class="job-card">
                    <div class="job-card-header">
                        <div class="company-badge">
                            <img src="https://via.placeholder.com/50" alt="ARCO" class="company-icon">
                        </div>
                        <div class="company-info">
                            <h3 class="company-name">ARCO</h3>
                            <p class="company-category">Construction</p>
                        </div>
                    </div>
                    <h4 class="job-position">Construction Project Manager</h4>
                    <p class="job-salary">$70–80K/yr · Full-time job</p>
                    <p class="job-location">Charleston, NC</p>
                </div>

                <!-- Duplicate cards for seamless loop -->
                <div class="job-card">
                    <div class="job-card-header">
                        <div class="company-badge">
                            <img src="https://via.placeholder.com/50" alt="USDA" class="company-icon">
                        </div>
                        <div class="company-info">
                            <h3 class="company-name">USDA Forest Service</h3>
                            <p class="company-category">Forestry</p>
                        </div>
                    </div>
                    <h4 class="job-position">Wildland Firefighter</h4>
                    <p class="job-salary">$32–78K/yr · Full-time job</p>
                    <p class="job-location">Eureka, CA</p>
                </div>

                <div class="job-card">
                    <div class="job-card-header">
                        <div class="company-badge">
                            <img src="https://via.placeholder.com/50" alt="Waffle House" class="company-icon">
                        </div>
                        <div class="company-info">
                            <h3 class="company-name">Waffle House, Inc.</h3>
                            <p class="company-category">Restaurants & food service</p>
                        </div>
                    </div>
                    <h4 class="job-position">Restaurant Operations Management</h4>
                    <p class="job-salary">$63–80K/yr · Full-time job</p>
                    <p class="job-location">Fayetteville, AR</p>
                </div>

                <div class="job-card">
                    <div class="job-card-header">
                        <div class="company-badge">
                            <img src="https://via.placeholder.com/50" alt="Qualcomm" class="company-icon">
                        </div>
                        <div class="company-info">
                            <h3 class="company-name">Qualcomm</h3>
                            <p class="company-category">Electronic & computer hardware</p>
                        </div>
                    </div>
                    <h4 class="job-position">Machine Learning & Artificial Intelligence Engineering Intern</h4>
                    <p class="job-salary">$17–98/hr · Internship</p>
                    <p class="job-location">San Diego, CA</p>
                </div>

                <div class="job-card">
                    <div class="job-card-header">
                        <div class="company-badge">
                            <img src="https://via.placeholder.com/50" alt="Activision" class="company-icon">
                        </div>
                        <div class="company-info">
                            <h3 class="company-name">Activision Blizzard</h3>
                            <p class="company-category">Internet & software</p>
                        </div>
                    </div>
                    <h4 class="job-position">Graphic Design Intern</h4>
                    <p class="job-salary">$25–43/hr · Internship</p>
                    <p class="job-location">New York, NY (Hybrid)</p>
                </div>

                <div class="job-card">
                    <div class="job-card-header">
                        <div class="company-badge">
                            <img src="https://via.placeholder.com/50" alt="ARCO" class="company-icon">
                        </div>
                        <div class="company-info">
                            <h3 class="company-name">ARCO</h3>
                            <p class="company-category">Construction</p>
                        </div>
                    </div>
                    <h4 class="job-position">Construction Project Manager</h4>
                    <p class="job-salary">$70–80K/yr · Full-time job</p>
                    <p class="job-location">Charleston, NC</p>
                </div>
            </div>
        </div>

        <!-- Row 2 - Scrolls Right -->
        <div class="jobs-row">
            <div class="jobs-track jobs-track-right">
                <!-- Job Card 6 -->
                <div class="job-card">
                    <div class="job-card-header">
                        <div class="company-badge">
                            <img src="https://via.placeholder.com/50" alt="Green Bay Packers" class="company-icon">
                        </div>
                        <div class="company-info">
                            <h3 class="company-name">Green Bay Packers</h3>
                            <p class="company-category">Sports & leisure</p>
                        </div>
                    </div>
                    <h4 class="job-position">Football Technology Multimedia Designer</h4>
                    <p class="job-salary">$80–110K/yr · Full-time job</p>
                    <p class="job-location">Green Bay, WI</p>
                </div>

                <!-- Job Card 7 -->
                <div class="job-card">
                    <div class="job-card-header">
                        <div class="company-badge">
                            <img src="https://via.placeholder.com/50" alt="Mattel" class="company-icon">
                        </div>
                        <div class="company-info">
                            <h3 class="company-name">Mattel, Inc.</h3>
                            <p class="company-category">Consumer packaged goods</p>
                        </div>
                    </div>
                    <h4 class="job-position">Summer Undergrad Packaging Engineering Intern</h4>
                    <p class="job-salary">$23–30/hr · Internship</p>
                    <p class="job-location">East Aurora, NY</p>
                </div>

                <!-- Job Card 8 -->
                <div class="job-card">
                    <div class="job-card-header">
                        <div class="company-badge">
                            <img src="https://via.placeholder.com/50" alt="Goldman Sachs" class="company-icon">
                        </div>
                        <div class="company-info">
                            <h3 class="company-name">Goldman Sachs</h3>
                            <p class="company-category">Investment banking</p>
                        </div>
                    </div>
                    <h4 class="job-position">Engineering Summer Analyst Program</h4>
                    <p class="job-salary">$50–75/hr · Internship</p>
                    <p class="job-location">Multiple locations</p>
                </div>

                <!-- Job Card 9 -->
                <div class="job-card">
                    <div class="job-card-header">
                        <div class="company-badge">
                            <img src="https://via.placeholder.com/50" alt="NASA" class="company-icon">
                        </div>
                        <div class="company-info">
                            <h3 class="company-name">NASA - National Aeronautics...</h3>
                            <p class="company-category">Aerospace</p>
                        </div>
                    </div>
                    <h4 class="job-position">Aerospace Engineer</h4>
                    <p class="job-salary">$55–98K/yr · Full-time job</p>
                    <p class="job-location">Huntsville, AL</p>
                </div>

                <!-- Job Card 10 -->
                <div class="job-card">
                    <div class="job-card-header">
                        <div class="company-badge">
                            <img src="https://via.placeholder.com/50" alt="Gap" class="company-icon">
                        </div>
                        <div class="company-info">
                            <h3 class="company-name">Gap, Inc.</h3>
                            <p class="company-category">Fashion</p>
                        </div>
                    </div>
                    <h4 class="job-position">Gap Brand Marketing Intern</h4>
                    <p class="job-salary">$33–35/hr · Internship</p>
                    <p class="job-location">New York, NY (Hybrid)</p>
                </div>

                <!-- Duplicate cards for seamless loop -->
                <div class="job-card">
                    <div class="job-card-header">
                        <div class="company-badge">
                            <img src="https://via.placeholder.com/50" alt="Green Bay Packers" class="company-icon">
                        </div>
                        <div class="company-info">
                            <h3 class="company-name">Green Bay Packers</h3>
                            <p class="company-category">Sports & leisure</p>
                        </div>
                    </div>
                    <h4 class="job-position">Football Technology Multimedia Designer</h4>
                    <p class="job-salary">$80–110K/yr · Full-time job</p>
                    <p class="job-location">Green Bay, WI</p>
                </div>

                <div class="job-card">
                    <div class="job-card-header">
                        <div class="company-badge">
                            <img src="https://via.placeholder.com/50" alt="Mattel" class="company-icon">
                        </div>
                        <div class="company-info">
                            <h3 class="company-name">Mattel, Inc.</h3>
                            <p class="company-category">Consumer packaged goods</p>
                        </div>
                    </div>
                    <h4 class="job-position">Summer Undergrad Packaging Engineering Intern</h4>
                    <p class="job-salary">$23–30/hr · Internship</p>
                    <p class="job-location">East Aurora, NY</p>
                </div>

                <div class="job-card">
                    <div class="job-card-header">
                        <div class="company-badge">
                            <img src="https://via.placeholder.com/50" alt="Goldman Sachs" class="company-icon">
                        </div>
                        <div class="company-info">
                            <h3 class="company-name">Goldman Sachs</h3>
                            <p class="company-category">Investment banking</p>
                        </div>
                    </div>
                    <h4 class="job-position">Engineering Summer Analyst Program</h4>
                    <p class="job-salary">$50–75/hr · Internship</p>
                    <p class="job-location">Multiple locations</p>
                </div>

                <div class="job-card">
                    <div class="job-card-header">
                        <div class="company-badge">
                            <img src="https://via.placeholder.com/50" alt="NASA" class="company-icon">
                        </div>
                        <div class="company-info">
                            <h3 class="company-name">NASA - National Aeronautics...</h3>
                            <p class="company-category">Aerospace</p>
                        </div>
                    </div>
                    <h4 class="job-position">Aerospace Engineer</h4>
                    <p class="job-salary">$55–98K/yr · Full-time job</p>
                    <p class="job-location">Huntsville, AL</p>
                </div>

                <div class="job-card">
                    <div class="job-card-header">
                        <div class="company-badge">
                            <img src="https://via.placeholder.com/50" alt="Gap" class="company-icon">
                        </div>
                        <div class="company-info">
                            <h3 class="company-name">Gap, Inc.</h3>
                            <p class="company-category">Fashion</p>
                        </div>
                    </div>
                    <h4 class="job-position">Gap Brand Marketing Intern</h4>
                    <p class="job-salary">$33–35/hr · Internship</p>
                    <p class="job-location">New York, NY (Hybrid)</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Network Section -->
    <section class="network-section">
        <h2 class="network-title">
            <span class="highlight" id="network-size-stat">15M+ job seekers</span> on the largest network for early careers
        </h2>

        <div class="feature-cards">
            <!-- Students Card -->
            <div class="feature-card">
                <div class="feature-image">
                    <img src="https://via.placeholder.com/600x400" alt="Find jobs" class="feature-img">
                </div>
                <h3 class="feature-heading">Find your dream internship or job</h3>
                <button class="btn-feature">Avsar for students</button>
            </div>

            <!-- Employers Card -->
            <div class="feature-card">
                <div class="feature-image">
                    <img src="https://via.placeholder.com/600x400" alt="Hire early talent" class="feature-img">
                </div>
                <h3 class="feature-heading">Hire early talent fast</h3>
                <button class="btn-feature">Avsar for employers</button>
            </div>

            <!-- Career Centers Card -->
            <div class="feature-card">
                <div class="feature-image">
                    <img src="https://via.placeholder.com/600x400" alt="Give guidance" class="feature-img">
                </div>
                <h3 class="feature-heading">Give meaningful guidance</h3>
                <button class="btn-feature">Avsar for career centers</button>
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
                        <img src="/inclusify/logo.png" alt="Avsar Nepal" class="logo-img">
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
    
    
    <!-- GSAP Animations -->
    <script>
        // GSAP Animations
        gsap.registerPlugin(ScrollTrigger);

        // Hero animations - Clean approach with .to()
        const heroTimeline = gsap.timeline({ defaults: { ease: "power2.out" } });
        
        heroTimeline
            .to(".hero-subtitle", { 
                duration: 1, 
                y: 0, 
                opacity: 1,
                delay: 0.2
            })
            .to(".hero-title", { 
                duration: 1, 
                y: 0, 
                opacity: 1
            }, "-=0.7")
            .to(".phone-form", { 
                duration: 0.8, 
                y: 0, 
                opacity: 1
            }, "-=0.5");

        // Floating media animations
        gsap.to(".media-item", {
            duration: 3,
            y: "random(-20, 20)",
            rotation: "random(-5, 5)",
            repeat: -1,
            yoyo: true,
            ease: "power2.inOut",
            stagger: 0.5
        });

    </script>
</body>
</html>

