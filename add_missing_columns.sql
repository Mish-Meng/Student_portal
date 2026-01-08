-- Add only the missing columns (skip username since it already exists)
-- Run this in phpMyAdmin SQL tab

-- Add password column (if it doesn't exist - you may get an error if it exists, that's okay)
ALTER TABLE teachers 
ADD COLUMN password VARCHAR(255) AFTER username;

-- Add email_verified column
ALTER TABLE teachers 
ADD COLUMN email_verified TINYINT(1) DEFAULT 0 AFTER password;

-- Add verification_token column
ALTER TABLE teachers 
ADD COLUMN verification_token VARCHAR(64) NULL AFTER email_verified;

-- Create index for faster token lookups (may give error if exists, that's okay)
CREATE INDEX idx_verification_token ON teachers(verification_token);

-- Done! Your teachers table should now have all required columns.

