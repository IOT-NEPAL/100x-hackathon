-- Create Database
CREATE DATABASE IF NOT EXISTS avsar_db;
USE avsar_db;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'organizer', 'career_centre', 'admin') DEFAULT 'user',
    org_name VARCHAR(255),
    contact_person VARCHAR(150),
    phone VARCHAR(20),
    profile_pic VARCHAR(255),
    skills TEXT,  -- Comma-separated skills
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Opportunities Table
CREATE TABLE IF NOT EXISTS opportunities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organizer_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    requirements TEXT,
    type ENUM('employment', 'internship') NOT NULL,
    location VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    views_count INT DEFAULT 0,
    date_posted TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (organizer_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_organizer (organizer_id),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Applications Table
CREATE TABLE IF NOT EXISTS applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    opportunity_id INT NOT NULL,
    cover_letter TEXT,
    resume_path VARCHAR(255),
    status ENUM('applied', 'under_review', 'accepted', 'rejected') DEFAULT 'applied',
    notes TEXT,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (opportunity_id) REFERENCES opportunities(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_opportunity (opportunity_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Job Offers Table
CREATE TABLE IF NOT EXISTS job_offers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    opportunity_id INT NULL,
    organizer_id INT NOT NULL,
    student_id INT NOT NULL,
    message TEXT NOT NULL,
    status ENUM('pending', 'accepted', 'declined', 'expired') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (opportunity_id) REFERENCES opportunities(id) ON DELETE CASCADE,
    FOREIGN KEY (organizer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_student (student_id),
    INDEX idx_organizer (organizer_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Activity Logs Table (Optional - for tracking actions)
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100),
    description TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Login Sessions Table (For remember me functionality)
CREATE TABLE IF NOT EXISTS sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_session_token (session_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Skill Verification Requests
CREATE TABLE IF NOT EXISTS skill_verifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    skill_name VARCHAR(100) NOT NULL,
    linkedin_url VARCHAR(255),
    github_url VARCHAR(255),
    resume_path VARCHAR(255),
    years_experience VARCHAR(20),
    payment_amount DECIMAL(10,2) DEFAULT 500.00,
    payment_status ENUM('pending', 'paid', 'refunded') DEFAULT 'paid',
    verification_status ENUM('pending', 'ai_analyzing', 'awaiting_interview', 'interview_scheduled', 'verified', 'rejected') DEFAULT 'pending',
    
    -- AI Analysis Results
    ai_github_score INT,
    ai_linkedin_score INT,
    ai_resume_score INT,
    ai_overall_score INT,
    ai_summary TEXT,
    ai_recommendation TEXT,
    ai_analyzed_at TIMESTAMP NULL,
    
    -- Interview Details
    interview_link VARCHAR(255),
    interview_scheduled_at DATETIME,
    interview_notes TEXT,
    interview_completed BOOLEAN DEFAULT FALSE,
    
    -- Final Verification
    admin_rating INT, -- 1-10
    admin_notes TEXT,
    rejection_reason TEXT,
    verified_at TIMESTAMP NULL,
    expires_at DATETIME NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_status (verification_status),
    INDEX idx_skill (skill_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Verification Payments
CREATE TABLE IF NOT EXISTS verification_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    verification_id INT NOT NULL,
    user_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) DEFAULT 'demo_card',
    transaction_id VARCHAR(100),
    payment_status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'completed',
    paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (verification_id) REFERENCES skill_verifications(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_verification (verification_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Interview Sessions
CREATE TABLE IF NOT EXISTS interview_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    verification_id INT NOT NULL,
    student_id INT NOT NULL,
    meet_link VARCHAR(255) NOT NULL,
    scheduled_at DATETIME NOT NULL,
    duration_minutes INT DEFAULT 30,
    completed BOOLEAN DEFAULT FALSE,
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (verification_id) REFERENCES skill_verifications(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_verification (verification_id),
    INDEX idx_student (student_id),
    INDEX idx_scheduled (scheduled_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Premium Subscriptions Table
CREATE TABLE IF NOT EXISTS premium_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(10,2) DEFAULT 10000.00,
    payment_method VARCHAR(50),
    transaction_id VARCHAR(255),
    payment_status ENUM('pending', 'paid', 'expired', 'cancelled') DEFAULT 'pending',
    start_date DATETIME,
    end_date DATETIME,
    is_active BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_active (is_active),
    INDEX idx_end_date (end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

