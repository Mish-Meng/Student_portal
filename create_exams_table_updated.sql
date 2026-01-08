-- SQL script to create the exams table (updated to link with results)
-- Run this in phpMyAdmin SQL tab

CREATE TABLE IF NOT EXISTS exams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_name VARCHAR(255) NOT NULL UNIQUE,
    exam_date DATE NOT NULL,
    file_path VARCHAR(500) NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_exam_date (exam_date),
    INDEX idx_exam_name (exam_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Done! The exams table is now created with exam_name as UNIQUE
-- This allows the results table to reference exams by exam_name

