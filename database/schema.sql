-- 1. Add missing columns to students table
ALTER TABLE students 
ADD COLUMN IF NOT EXISTS applicant_number VARCHAR(20) UNIQUE,
ADD COLUMN IF NOT EXISTS password VARCHAR(255),
ADD COLUMN IF NOT EXISTS status ENUM('active', 'inactive') NOT NULL DEFAULT 'active';