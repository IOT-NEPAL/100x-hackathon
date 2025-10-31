<?php
$page_title = 'About Us';
include 'includes/header.php';
?>

<div class="container mt-4">
    <!-- Hero Section -->
    <section class="hero-section text-center py-5 mb-5">
        <div class="container">
            <h1 class="display-4 fw-bold mb-4">About Inclusify</h1>
            <p class="lead">Empowering differently-abled individuals through accessible opportunities and inclusive technology</p>
        </div>
    </section>
    
    <!-- Mission Section -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-body">
                    <h2 class="mb-4"><i class="fas fa-bullseye me-3"></i>Our Mission</h2>
                    <p class="lead">
                        Inclusify is dedicated to creating a world where differently-abled individuals have equal access to opportunities for employment, education, and personal growth. We believe that diversity in abilities brings unique perspectives and strengths to every workplace and community.
                    </p>
                    <p>
                        Our platform serves as a bridge between talented individuals with diverse abilities and organizations committed to inclusive practices. We're not just a job board – we're a community that celebrates differences and champions accessibility.
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Values Section -->
    <div class="row mb-5">
        <div class="col-12 text-center mb-4">
            <h2 class="display-6 fw-bold">Our Values</h2>
        </div>
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <i class="fas fa-universal-access fa-3x text-primary mb-3"></i>
                    <h5>Accessibility First</h5>
                    <p>Every feature is designed with accessibility in mind, ensuring everyone can participate fully.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <i class="fas fa-handshake fa-3x text-success mb-3"></i>
                    <h5>Inclusion</h5>
                    <p>We celebrate diversity and create spaces where everyone feels valued and respected.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <i class="fas fa-lightbulb fa-3x text-warning mb-3"></i>
                    <h5>Innovation</h5>
                    <p>We continuously improve our platform to better serve our community's evolving needs.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <i class="fas fa-heart fa-3x text-danger mb-3"></i>
                    <h5>Empowerment</h5>
                    <p>We empower individuals to showcase their abilities and achieve their professional goals.</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Features Section -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-body">
                    <h2 class="mb-4"><i class="fas fa-star me-3"></i>What Makes Us Different</h2>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li class="mb-3">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <strong>Built-in Accessibility:</strong> High contrast mode, text-to-speech, keyboard navigation
                                </li>
                                <li class="mb-3">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <strong>Inclusive Employers:</strong> Partner with organizations committed to diversity
                                </li>
                                <li class="mb-3">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <strong>Comprehensive Support:</strong> Jobs, scholarships, training, and resources
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li class="mb-3">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <strong>Community Focus:</strong> Connect with like-minded individuals and mentors
                                </li>
                                <li class="mb-3">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <strong>Privacy Respected:</strong> Share only what you're comfortable sharing
                                </li>
                                <li class="mb-3">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <strong>Free Platform:</strong> No cost to job seekers, ever
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Call to Action -->
    <div class="row">
        <div class="col-12 text-center">
            <div class="card bg-primary text-white">
                <div class="card-body py-5">
                    <h2 class="mb-4">Ready to Get Started?</h2>
                    <p class="lead mb-4">Join thousands of individuals and organizations building a more inclusive future.</p>
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <?php if (!isLoggedIn()): ?>
                            <a href="signup.php" class="btn btn-light btn-lg">
                                <i class="fas fa-user-plus me-2"></i>Join as Job Seeker
                            </a>
                            <a href="signup.php" class="btn btn-outline-light btn-lg">
                                <i class="fas fa-building me-2"></i>Join as Organization
                            </a>
                        <?php else: ?>
                            <a href="opportunities.php" class="btn btn-light btn-lg">
                                <i class="fas fa-search me-2"></i>Explore Opportunities
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
