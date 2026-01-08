-- SQL script to check if results and exams tables are connected
-- Run this in phpMyAdmin SQL tab to see the current state

-- Check if exams table has exam_name as UNIQUE
SHOW INDEX FROM exams WHERE Key_name = 'unique_exam_name';

-- Check if results table has foreign key to exams
SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM 
    INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE 
    TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'results'
    AND REFERENCED_TABLE_NAME = 'exams';

-- If the above query returns no rows, the tables are NOT connected
-- If it returns a row with REFERENCED_TABLE_NAME = 'exams', they ARE connected

