-- SQL script to link results table to exams table
-- Run this in phpMyAdmin SQL tab if the tables already exist

-- Step 1: Make sure exams table has exam_name as UNIQUE (if not already)
ALTER TABLE exams MODIFY exam_name VARCHAR(255) NOT NULL;
ALTER TABLE exams ADD UNIQUE KEY unique_exam_name (exam_name);

-- Step 2: Add foreign key constraint to link results.exam_name to exams.exam_name
-- First, check if the foreign key already exists, if it does, this will give an error (which is fine)
-- If it doesn't exist, this will add it

ALTER TABLE results 
ADD CONSTRAINT fk_results_exam_name 
FOREIGN KEY (exam_name) REFERENCES exams(exam_name) 
ON DELETE CASCADE 
ON UPDATE CASCADE;

-- Done! The results table is now properly linked to the exams table.
-- This ensures:
-- 1. You can only add results for exams that exist in the exams table
-- 2. If an exam is deleted, all related results are automatically deleted
-- 3. If an exam name is updated, all related results are automatically updated

