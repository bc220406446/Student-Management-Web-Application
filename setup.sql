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
