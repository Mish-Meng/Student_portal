-- SQL script to add username and password fields to teachers table
-- Run this script in your MySQL database to enable teacher login

-- Note: If columns already exist, you'll get an error. That's okay, just ignore it.

-- Add username column (if it doesn't exist, you may need to check manually)
ALTER TABLE teachers 
ADD COLUMN username VARCHAR(100) UNIQUE AFTER id;

-- Add password column
ALTER TABLE teachers 
ADD COLUMN password VARCHAR(255) AFTER username;

-- Note: After running this, you'll need to update existing teachers with usernames and passwords
-- Example update statement (replace with actual values):
-- UPDATE teachers SET username = 'teacher1', password = 'password123' WHERE id = 1;

