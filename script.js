/**
 * Main script for Avsar Nepal
 * Handles common functionality across the application
 */

document.addEventListener('DOMContentLoaded', function() {
    // Handle form submissions on landing pages
    const signupForms = document.querySelectorAll('#signupForm, #studentsSignupForm, #careerCentersForm');
    
    signupForms.forEach(form => {
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const emailInput = this.querySelector('input[type="email"]');
                if (emailInput && emailInput.value) {
                    // Redirect to signup page with email pre-filled
                    window.location.href = `signup.php?email=${encodeURIComponent(emailInput.value)}`;
                }
            });
        }
    });
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href !== '#' && document.querySelector(href)) {
                e.preventDefault();
                document.querySelector(href).scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Add loading state to buttons
    const submitButtons = document.querySelectorAll('button[type="submit"]');
    submitButtons.forEach(button => {
        button.addEventListener('click', function() {
            const form = this.closest('form');
            if (form && form.checkValidity()) {
                this.disabled = true;
                const originalText = this.textContent;
                this.textContent = 'Processing...';
                
                // Re-enable after 3 seconds as fallback
                setTimeout(() => {
                    this.disabled = false;
                    this.textContent = originalText;
                }, 3000);
            }
        });
    });
    
    console.log('Avsar Nepal script loaded successfully');
});



