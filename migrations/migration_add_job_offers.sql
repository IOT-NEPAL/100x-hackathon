-- Migration: Add job_offers table for employer-to-student job offers
-- Run this to add job offer functionality

USE inclusify;

-- Create job_offers table
CREATE TABLE IF NOT EXISTS job_offers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    opportunity_id INT NOT NULL,
    organizer_id INT NOT NULL,
    student_id INT NOT NULL,
    message TEXT NOT NULL,
    status ENUM('pending', 'accepted', 'declined', 'expired') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,
    FOREIGN KEY (opportunity_id) REFERENCES opportunities(id) ON DELETE CASCADE,
    FOREIGN KEY (organizer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_opportunity (opportunity_id),
    INDEX idx_student (student_id),
    INDEX idx_organizer (organizer_id),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
);

