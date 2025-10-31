-- Migration: Remove scholarship, volunteer, and training opportunity types
-- Date: 2024-10-30
-- Description: Updates the opportunities table to only support employment (full-time/part-time) and internship types

USE inclusify;

-- Step 1: Delete all opportunities with type scholarship, volunteer, or training
DELETE FROM applications WHERE opportunity_id IN (
    SELECT id FROM opportunities WHERE type IN ('scholarship', 'volunteer', 'training')
);

DELETE FROM opportunities WHERE type IN ('scholarship', 'volunteer', 'training');

-- Step 2: Modify the ENUM type to only include employment and internship
ALTER TABLE opportunities 
MODIFY COLUMN type ENUM('employment','internship') NOT NULL;

-- Note: After running this migration:
-- 1. All scholarship, volunteer, and training opportunities will be permanently deleted
-- 2. All applications to those opportunities will also be deleted
-- 3. The type field will only accept 'employment' or 'internship' values

