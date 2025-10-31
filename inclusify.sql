-- Inclusify Database Schema
-- Drop database if exists and create new
DROP DATABASE IF EXISTS inclusify;
CREATE DATABASE inclusify CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE inclusify;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(30),
    password VARCHAR(255) NOT NULL,
    role ENUM('user','organizer','career_centre','admin') DEFAULT 'user',
    skills TEXT NULL,
    profile_pic VARCHAR(255) NULL,
    org_name VARCHAR(255) NULL,
    contact_person VARCHAR(150) NULL,
    verification_note TEXT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Opportunities table
CREATE TABLE opportunities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    type ENUM('employment','internship') NOT NULL,
    organizer_id INT,
    location VARCHAR(255) NULL,
    salary_range VARCHAR(100) NULL,
    contact_email VARCHAR(255) NULL,
    contact_phone VARCHAR(30) NULL,
    application_deadline DATE NULL,
    file_path VARCHAR(255) NULL,
    date_posted TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    requirements TEXT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    is_featured BOOLEAN DEFAULT FALSE,
    views_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (organizer_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_type (type),
    INDEX idx_organizer (organizer_id),
    INDEX idx_active (is_active),
    INDEX idx_featured (is_featured)
);

-- Applications table
CREATE TABLE applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    opportunity_id INT NOT NULL,
    status ENUM('applied','under_review','accepted','rejected') DEFAULT 'applied',
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    cover_letter TEXT NULL,
    resume_path VARCHAR(255) NULL,
    additional_info TEXT NULL,
    reviewed_at TIMESTAMP NULL,
    notes TEXT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (opportunity_id) REFERENCES opportunities(id) ON DELETE CASCADE,
    UNIQUE KEY unique_application (user_id, opportunity_id),
    INDEX idx_status (status),
    INDEX idx_user (user_id),
    INDEX idx_opportunity (opportunity_id)
);

-- Activity logs table for admin analytics
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_action (action),
    INDEX idx_user (user_id),
    INDEX idx_created (created_at)
);

-- Insert sample data

-- Admin users (passwords: admin123)
INSERT INTO users (name, email, phone, password, role, created_at) VALUES
('Admin User', 'admin@inclusify.com', '+1-555-0001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NOW()),
('Super Admin', 'superadmin@inclusify.com', '+1-555-0002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NOW());

-- Organizer users (passwords: org123, except demo organizer password: password)
INSERT INTO users (name, email, phone, password, role, org_name, contact_person, created_at) VALUES
('Ability Foundation', 'contact@abilityfoundation.org', '+1-555-1001', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'organizer', 'Ability Foundation', 'Sarah Johnson', NOW()),
('Inclusive Tech Corp', 'hr@inclusivetech.com', '+1-555-1002', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'organizer', 'Inclusive Tech Corp', 'Michael Chen', NOW()),
('Equal Opportunity Institute', 'jobs@equalopportunity.edu', '+1-555-1003', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'organizer', 'Equal Opportunity Institute', 'Dr. Lisa Rodriguez', NOW()),
('Demo Organization', 'shreeyani@gmail.com', '+1-555-8888', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'organizer', 'Demo Inclusion Services', 'Shreeyani Demo', NOW());

-- User accounts (passwords: user123, except demo user password: password)
INSERT INTO users (name, email, phone, password, role, created_at) VALUES
('Alex Thompson', 'alex.thompson@email.com', '+1-555-2001', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'user', NOW()),
('Maria Garcia', 'maria.garcia@email.com', '+1-555-2002', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'user', NOW()),
('David Kim', 'david.kim@email.com', '+1-555-2003', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'user', NOW()),
('Emma Wilson', 'emma.wilson@email.com', '+1-555-2004', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'user', NOW()),
('James Brown', 'james.brown@email.com', '+1-555-2005', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'user', NOW()),
('Demo User', 'nishavp2007@gmail.com', '+1-555-9999', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', NOW()),
('Demo User 2', 'nishavpradhan7@gmail.com', '+1-555-7777', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', NOW());

-- Sample opportunities
INSERT INTO opportunities (title, description, type, organizer_id, location, salary_range, contact_email, application_deadline, requirements, created_at) VALUES
('Remote Web Developer Position', 'Full-time remote web developer role. Work with modern frameworks and contribute to innovative solutions.', 'employment', 3, 'Remote', '$60,000 - $80,000', 'jobs@inclusivetech.com', '2024-06-30', 'Experience with HTML, CSS, JavaScript. Knowledge of modern web standards preferred.', NOW()),
('UX Designer Internship', 'Paid internship program focusing on user experience design. Mentorship and professional development included.', 'internship', 3, 'New York, NY', '$25/hour', 'internships@inclusivetech.com', '2024-07-20', 'Portfolio demonstrating UX/UI skills. Interest in user-centered design.', NOW()),
('Marketing Coordinator - Full Time', 'Join our dynamic marketing team to create campaigns and manage social media presence. Full-time position with benefits.', 'employment', 4, 'Kathmandu, Nepal', 'NPR 40,000 - 60,000', 'hr@nepaltech.com', '2024-08-15', 'Bachelor degree in Marketing or related field. 1-2 years experience preferred.', NOW()),
('Software Engineering Intern', 'Summer internship program for aspiring software engineers. Work on real projects with experienced mentors.', 'internship', 3, 'Remote', '$20/hour', 'internships@inclusivetech.com', '2024-07-01', 'Currently enrolled in Computer Science or related program. Strong programming fundamentals.', NOW()),
('Part-Time Content Writer', 'Flexible part-time position creating engaging content for our blog and social media channels.', 'employment', 4, 'Remote', 'NPR 25,000 - 35,000', 'content@nepaltech.com', '2024-09-30', 'Excellent writing skills. Experience with SEO and content management systems.', NOW());

-- Sample applications
INSERT INTO applications (user_id, opportunity_id, status, cover_letter, applied_at) VALUES
(6, 1, 'applied', 'I am excited to apply for this remote web developer position. My experience with modern web technologies and passion for creating innovative solutions make me a great fit.', NOW()),
(7, 2, 'under_review', 'I am currently pursuing my Computer Science degree and am passionate about user experience design. This internship would be a perfect opportunity to grow my skills.', NOW()),
(8, 3, 'accepted', 'I am very interested in this marketing coordinator position. My background in digital marketing and social media management aligns well with your requirements.', NOW()),
(9, 4, 'applied', 'As a computer science student, I would love to gain hands-on experience through this software engineering internship and contribute to real-world projects.', NOW()),
(10, 5, 'applied', 'I am a skilled content writer with SEO experience and would be thrilled to create engaging content for your platforms on a part-time basis.', NOW());

-- Sample activity logs
INSERT INTO activity_logs (user_id, action, description, created_at) VALUES
(6, 'registration', 'New user registered', NOW() - INTERVAL 30 DAY),
(7, 'registration', 'New user registered', NOW() - INTERVAL 25 DAY),
(8, 'registration', 'New user registered', NOW() - INTERVAL 20 DAY),
(9, 'registration', 'New user registered', NOW() - INTERVAL 18 DAY),
(10, 'registration', 'New user registered', NOW() - INTERVAL 16 DAY),
(3, 'registration', 'New organizer registered', NOW() - INTERVAL 35 DAY),
(4, 'registration', 'New organizer registered', NOW() - INTERVAL 32 DAY),
(5, 'registration', 'New organizer registered', NOW() - INTERVAL 28 DAY),
(3, 'opportunity_posted', 'Posted remote web developer position', NOW() - INTERVAL 15 DAY),
(3, 'opportunity_posted', 'Posted UX designer internship', NOW() - INTERVAL 12 DAY),
(4, 'opportunity_posted', 'Posted marketing coordinator position', NOW() - INTERVAL 10 DAY),
(3, 'opportunity_posted', 'Posted software engineering internship', NOW() - INTERVAL 8 DAY),
(4, 'opportunity_posted', 'Posted part-time content writer position', NOW() - INTERVAL 6 DAY),
(6, 'application_submitted', 'Applied to remote web developer position', NOW() - INTERVAL 3 DAY),
(7, 'application_submitted', 'Applied to UX designer internship', NOW() - INTERVAL 2 DAY),
(8, 'application_submitted', 'Applied to marketing coordinator position', NOW() - INTERVAL 1 DAY);

-- Create indexes for better performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_opportunities_type_active ON opportunities(type, is_active);
CREATE INDEX idx_applications_status_date ON applications(status, applied_at);

-- ================================================================
-- LOGIN CREDENTIALS FOR TESTING
-- ================================================================

-- ADMIN ACCOUNTS (Full System Access):
-- Email: admin@inclusify.com, Password: admin123
-- Email: superadmin@inclusify.com, Password: admin123

-- ORGANIZER ACCOUNTS (Post Opportunities, Manage Applications):
-- Email: contact@abilityfoundation.org, Password: org123
-- Email: hr@inclusivetech.com, Password: org123  
-- Email: jobs@equalopportunity.edu, Password: org123

-- USER ACCOUNTS (Apply for Opportunities):
-- Email: alex.thompson@email.com, Password: user123
-- Email: maria.garcia@email.com, Password: user123
-- Email: david.kim@email.com, Password: user123
-- Email: emma.wilson@email.com, Password: user123
-- Email: james.brown@email.com, Password: user123

-- ================================================================
-- FEATURES INCLUDED:
-- ✅ Complete user management system
-- ✅ Role-based access control (Admin/Organizer/User)
-- ✅ Opportunity management with filtering
-- ✅ Application system with status tracking
-- ✅ Activity logging and analytics
-- ✅ Full accessibility features (voice commands, high contrast, etc.)
-- ✅ File upload support for resumes and attachments
-- ✅ Admin panel with user/opportunity/application management
-- ✅ Responsive design with Bootstrap 5
-- ================================================================
