CREATE DATABASE IF NOT EXISTS SMWA;
USE SMWA;

-- Admin Table
CREATE TABLE IF NOT EXISTS admin (
    AdminID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    Email VARCHAR(100) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL
);

-- Default Admin Account
INSERT INTO admin (Name, Email, Password)
VALUES (
    'System Admin',
    'admin@example.com',
    '$2a$12$Ob5anLakmDjF1zwk/gSN9utuN0IozkZuOfyjAEtk/EHrPle0J2eby'
)
ON DUPLICATE KEY UPDATE
    Name = VALUES(Name),
    Password = VALUES(Password);

-- Password: Test@123

-- Student Table
CREATE TABLE IF NOT EXISTS student (
    StudentID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    Email VARCHAR(100) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    Department VARCHAR(100) NOT NULL,
    Marks DECIMAL(5,2) DEFAULT 0.00,
    Status ENUM('Approved', 'Not Approved', 'Pending') DEFAULT 'Pending'
);

-- Upgrade older SMS installations without deleting existing student records.
ALTER TABLE student
    ADD COLUMN IF NOT EXISTS Department VARCHAR(100) NOT NULL DEFAULT 'Unassigned' AFTER Password,
    ADD COLUMN IF NOT EXISTS Marks DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER Department;

UPDATE student SET Status = 'Not Approved' WHERE Status = 'Rejected';

ALTER TABLE student
    MODIFY COLUMN Status ENUM('Approved', 'Not Approved', 'Pending') NOT NULL DEFAULT 'Pending';
