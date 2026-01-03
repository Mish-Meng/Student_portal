-- Safe SQL script to add columns only if they don't exist
-- This script checks for existing columns before adding them

-- Function to add column only if it doesn't exist
-- Note: MySQL doesn't support IF NOT EXISTS for ALTER TABLE ADD COLUMN
-- So we'll use a workaround with stored procedures

DELIMITER $$

-- Drop procedure if exists
DROP PROCEDURE IF EXISTS AddColumnIfNotExists$$

CREATE PROCEDURE AddColumnIfNotExists(
    IN tableName VARCHAR(128),
    IN columnName VARCHAR(128),
    IN columnDefinition TEXT
)
BEGIN
    DECLARE columnExists INT DEFAULT 0;
    
    SELECT COUNT(*) INTO columnExists
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = tableName
      AND COLUMN_NAME = columnName;
    
    IF columnExists = 0 THEN
        SET @sql = CONCAT('ALTER TABLE ', tableName, ' ADD COLUMN ', columnName, ' ', columnDefinition);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

DELIMITER ;

-- Now add columns using the procedure
CALL AddColumnIfNotExists('teachers', 'username', 'VARCHAR(100) UNIQUE AFTER id');
CALL AddColumnIfNotExists('teachers', 'password', 'VARCHAR(255) AFTER username');
CALL AddColumnIfNotExists('teachers', 'email_verified', 'TINYINT(1) DEFAULT 0 AFTER password');
CALL AddColumnIfNotExists('teachers', 'verification_token', 'VARCHAR(64) NULL AFTER email_verified');

-- Create index only if it doesn't exist
SET @index_exists = (
    SELECT COUNT(*) 
    FROM information_schema.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'teachers'
      AND INDEX_NAME = 'idx_verification_token'
);

SET @sql = IF(@index_exists = 0, 
    'CREATE INDEX idx_verification_token ON teachers(verification_token)',
    'SELECT "Index already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Clean up
DROP PROCEDURE IF EXISTS AddColumnIfNotExists;

-- Done! Check your table structure to confirm all columns are added.

