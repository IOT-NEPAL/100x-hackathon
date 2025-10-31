-- Migration: Add career_centre role and remove disability_text
-- Run this if you have an existing database

USE inclusify;

-- Add career_centre to role ENUM
ALTER TABLE users MODIFY COLUMN role ENUM('user','organizer','career_centre','admin') DEFAULT 'user';

-- Remove disability_text column (if it exists and you want to remove it)
ALTER TABLE users DROP COLUMN IF EXISTS disability_text;

-- Update existing organizers that are actually career centres
-- (If you marked them with [CAREER_CENTRE] in verification_note)
UPDATE users 
SET role = 'career_centre' 
WHERE role = 'organizer' 
AND verification_note LIKE '[CAREER_CENTRE]%';

