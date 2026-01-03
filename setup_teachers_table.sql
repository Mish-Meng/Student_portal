-- Complete SQL script to set up teachers table for signup and email verification
-- Run this entire script in your MySQL database (phpMyAdmin or MySQL command line)

-- Step 1: Add username and password columns (if they don't exist)
-- If you get an error saying column exists, that's okay - just continue

ALTER TABLE teachers 
ADD COLUMN username VARCHAR(100) UNIQUE AFTER id;

ALTER TABLE teachers 
ADD COLUMN password VARCHAR(255) AFTER username;

-- Step 2: Add email verification columns
ALTER TABLE teachers 
ADD COLUMN email_verified TINYINT(1) DEFAULT 0 AFTER password;

ALTER TABLE teachers 
ADD COLUMN verification_token VARCHAR(64) NULL AFTER email_verified;

-- Step 3: Create index for faster token lookups
CREATE INDEX idx_verification_token ON teachers(verification_token);

-- Done! Your teachers table now has:
-- - username (for login)
-- - password (for login)
-- - email_verified (0 = not verified, 1 = verified)
-- - verification_token (for email verification links)

-- Note: Existing teachers will have email_verified = 0 (unverified)
-- You may want to manually verify existing teachers if needed:
-- UPDATE teachers SET email_verified = 1 WHERE id = [teacher_id];

