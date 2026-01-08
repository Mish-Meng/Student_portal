-- SQL script to create the results table WITH proper link to exams
-- Run this in phpMyAdmin SQL tab (use this if results table doesn't exist yet)

CREATE TABLE IF NOT EXISTS results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    exam_name VARCHAR(255) NOT NULL,
    score INT NOT NULL,
    result VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (exam_name) REFERENCES exams(exam_name) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_student_id (student_id),
    INDEX idx_exam_name (exam_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Done! The results table is now properly linked to:
-- 1. students table (via student_id)
-- 2. exams table (via exam_name)

-- Important: Make sure exams table exists first and has exam_name as UNIQUE!

