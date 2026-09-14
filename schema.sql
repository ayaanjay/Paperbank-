-- PaperBank Database Schema
-- Created: March 20, 2026
-- Database: paperbank
-- MySQL/MariaDB Version: 5.7+

-- Create users table
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `role` enum('student','teacher','admin') NOT NULL DEFAULT 'student',
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `password_changed` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0 = using universal password, 1 = changed to personal password',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_email` (`email`),
  KEY `idx_role` (`role`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create papers table
CREATE TABLE IF NOT EXISTS `papers` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `title` varchar(255) NOT NULL,
  `grade` int(11) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `year` int(11) NOT NULL,
  `track` varchar(50) DEFAULT NULL COMMENT 'UK or US track for grades 9-12',
  `upload_type` enum('file','link') NOT NULL DEFAULT 'file' COMMENT 'Type of upload: file or link',
  `file_path` varchar(255) DEFAULT NULL,
  `link_url` varchar(500) DEFAULT NULL,
  `file_size` int(11) NOT NULL DEFAULT 0,
  `teacher_id` int(11) DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'approved',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  KEY `idx_grade` (`grade`),
  KEY `idx_subject` (`subject`),
  KEY `idx_year` (`year`),
  KEY `idx_status` (`status`),
  KEY `idx_teacher_id` (`teacher_id`),
  KEY `idx_upload_type` (`upload_type`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create downloads_log table (optional, for audit trail)
CREATE TABLE IF NOT EXISTS `downloads_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `paper_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `downloaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`paper_id`) REFERENCES `papers` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  KEY `idx_paper_id` (`paper_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_downloaded_at` (`downloaded_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create activity_log table (optional, for security/audit)
CREATE TABLE IF NOT EXISTS `activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `details` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample admin user (optional - use this for first login)
-- Username: admin@paperbank.local / Password: admin123
-- IMPORTANT: Change this password immediately after first login!
INSERT INTO `users` (full_name, email, password, role, status) 
VALUES ('System Administrator', 'admin@paperbank.local', '$2y$10$6gg.QGHfW5oCnW5cN2yUWujJb..DXLpBavG0LCMA3lJhJu0YsqCB.', 'admin', 'approved')
ON DUPLICATE KEY UPDATE id=id;
