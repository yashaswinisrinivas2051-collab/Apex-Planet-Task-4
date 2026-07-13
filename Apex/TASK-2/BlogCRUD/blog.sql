-- ============================================================
-- Blog Management System - Database Schema (Security Enhanced)
-- ============================================================
-- Database: blog
-- 
-- Run this SQL file in phpMyAdmin or MySQL CLI to set up the database.
-- Example: mysql -u root -p < blog.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS `blog` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `blog`;

-- ------------------------------------------------------------
-- Users Table
-- Stores registered user authentication data and roles
-- Role determines access permissions:
--   'admin'  - Full access (CRUD all posts, manage users)
--   'editor' - Can create, read, update own posts
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(100) NOT NULL UNIQUE,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'editor') NOT NULL DEFAULT 'editor',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Posts Table
-- Stores blog posts created by authenticated users
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `posts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `content` TEXT NOT NULL,
    `user_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Login Attempts Table (for rate limiting)
-- Tracks failed login attempts to prevent brute-force attacks
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `login_attempts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ip_address` VARCHAR(45) NOT NULL,
    `username` VARCHAR(100) NOT NULL,
    `attempted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_ip_username` (`ip_address`, `username`),
    INDEX `idx_attempted_at` (`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Activity Logs Table (for audit trail)
-- Tracks all user actions for security monitoring
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `activity_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `action` VARCHAR(100) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_action` (`action`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Sample Data (Optional)
-- Username: admin  |  Password: Admin@123  |  Role: admin
-- Email: admin@blog.com
-- ------------------------------------------------------------
INSERT INTO `users` (`username`, `email`, `password`, `role`) VALUES
('admin', 'admin@blog.com', '$2y$10$pZyLYkaUFIs8Z91.n63AjuHTrvCPMDObqk.EalEAXn8bRsCbyloLq', 'admin');

INSERT INTO `posts` (`title`, `content`, `user_id`) VALUES
('Welcome to BlogCRUD', 'This is your first blog post. You can edit or delete it from the dashboard.', 1),
('Getting Started with PHP', 'PHP is a powerful server-side scripting language used for web development. This blog system is built with core PHP, MySQL, Bootstrap 5, and JavaScript.', 1);
