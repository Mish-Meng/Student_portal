-- SQL script to create the timetable table
-- Run this in phpMyAdmin SQL tab

-- First, make sure classes.grade has an index (if not already)
-- ALTER TABLE classes ADD INDEX idx_grade (grade);

CREATE TABLE IF NOT EXISTS timetable (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_grade VARCHAR(255) NOT NULL,
    day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday') NOT NULL,
    time_slot VARCHAR(50) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    teacher VARCHAR(255) NOT NULL,
    room VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_class_grade (class_grade),
    INDEX idx_day (day_of_week),
    INDEX idx_teacher (teacher),
    UNIQUE KEY unique_class_day_time (class_grade, day_of_week, time_slot)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Note: Foreign key to classes(grade) is commented out to avoid issues
-- If classes.grade is UNIQUE, you can uncomment this:
-- ALTER TABLE timetable ADD FOREIGN KEY (class_grade) REFERENCES classes(grade) ON DELETE CASCADE ON UPDATE CASCADE;

-- Done! The timetable table is now created.
-- This table stores the weekly timetable for each class.
-- Each row represents one time slot for a specific class on a specific day.

