-- SQL script to add email verification fields to teachers table
-- Run this script in your MySQL database to enable email verification

-- Add email_verified and verification_token columns to teachers table
ALTER TABLE teachers 
ADD COLUMN email_verified TINYINT(1) DEFAULT 0 AFTER password,
ADD COLUMN verification_token VARCHAR(64) NULL AFTER email_verified;

-- Create index for faster token lookups
CREATE INDEX idx_verification_token ON teachers(verification_token);

-- Note: Existing teachers will have email_verified = 0 (unverified)
-- You may want to manually verify existing teachers:
-- UPDATE teachers SET email_verified = 1 WHERE id = [teacher_id];

