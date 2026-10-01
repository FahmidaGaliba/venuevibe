-- ============================================================
-- VenueVibe - Database Creation & Table Schema Definition
-- File: create_database.sql
-- ============================================================

-- Create database if it does not exist and select it
DROP DATABASE IF EXISTS venuevibe;
CREATE DATABASE venuevibe CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE venuevibe;

-- ------------------------------------------------------------
-- 1. USERS TABLE
-- Stores credentials and profiles for students, faculty, and admins
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('student', 'faculty', 'admin') NOT NULL DEFAULT 'student',
    is_admin TINYINT(1) NOT NULL DEFAULT 0,
    student_id VARCHAR(50) DEFAULT NULL,
    department VARCHAR(100) DEFAULT NULL,
    phone VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 2. VENUES TABLE
-- Master catalog of all bookable campus spaces
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS venues (
    id VARCHAR(50) PRIMARY KEY,              -- Slug identifier, e.g. 'electrical-lab-4'
    name VARCHAR(100) NOT NULL,
    type VARCHAR(50) NOT NULL,
    location VARCHAR(150) NOT NULL,
    capacity INT NOT NULL,
    infrastructure VARCHAR(50) NOT NULL,
    rating DECIMAL(2,1) NOT NULL DEFAULT 5.0,
    status VARCHAR(20) NOT NULL DEFAULT 'Available', -- 'Available' or 'Booked'
    image VARCHAR(255) NOT NULL,
    thumbnail VARCHAR(255) NOT NULL,
    about TEXT NOT NULL,
    review_count INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 3. EQUIPMENT TABLE
-- Equipment and facilities available inside each venue
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS equipment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    venue_id VARCHAR(50) NOT NULL,
    item VARCHAR(100) NOT NULL,
    FOREIGN KEY (venue_id) REFERENCES venues(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 4. SCHEDULE TABLE
-- Upcoming reservations, lectures, and events for each venue
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS schedule (
    id INT AUTO_INCREMENT PRIMARY KEY,
    venue_id VARCHAR(50) NOT NULL,
    event_date VARCHAR(20) NOT NULL,
    title VARCHAR(150) NOT NULL,
    time_range VARCHAR(50) NOT NULL,
    host VARCHAR(150) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'confirmed', -- 'confirmed' or 'tentative'
    FOREIGN KEY (venue_id) REFERENCES venues(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 5. REVIEWS TABLE
-- User and faculty feedback for campus venues
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    venue_id VARCHAR(50) NOT NULL,
    initials VARCHAR(5) NOT NULL,
    reviewer_name VARCHAR(150) NOT NULL,
    time_ago VARCHAR(50) NOT NULL,
    review_text TEXT NOT NULL,
    FOREIGN KEY (venue_id) REFERENCES venues(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 6. REQUESTS TABLE
-- Booking requests submitted by students and faculty
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    venue_name VARCHAR(100) NOT NULL,
    venue_location VARCHAR(150) NOT NULL,
    booking_date DATE NOT NULL,
    booking_time VARCHAR(50) NOT NULL,
    purpose VARCHAR(100) NOT NULL,
    description TEXT,
    attachment VARCHAR(255) NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Pending', -- 'Pending', 'Approved', 'Rejected'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_booking (venue_name, booking_date, booking_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 7. MESSAGES TABLE
-- Support and communication threads between users and administration
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    sender_name VARCHAR(150) NOT NULL,
    text TEXT NOT NULL,
    time_sent VARCHAR(50) NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 8. NOTIFICATIONS TABLE
-- Real-time announcements, approvals, rejections, and maintenance alerts
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    title VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50) NOT NULL DEFAULT 'general', -- 'approval', 'rejection', 'maintenance', 'general'
    time_ago VARCHAR(50) NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
