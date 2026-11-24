-- Database setup for Groupe SEB registration form
-- Run this in phpMyAdmin or MySQL command line

CREATE DATABASE IF NOT EXISTS groupeseb_registrations CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE groupeseb_registrations;

CREATE TABLE IF NOT EXISTS registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    civility VARCHAR(10) NOT NULL,
    lastname VARCHAR(100) NOT NULL,
    firstname VARCHAR(100) NOT NULL,
    birthdate DATE NOT NULL,
    city VARCHAR(50) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    cin VARCHAR(50) NOT NULL,
    store VARCHAR(100) NOT NULL,
    receipt_filename VARCHAR(255) NOT NULL,
    receipt_path VARCHAR(500) NOT NULL,
    consent BOOLEAN DEFAULT TRUE,
    submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    INDEX idx_email (email),
    INDEX idx_submission_date (submission_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
