-- Migration to add password_changed column to users table
-- Run this after updating the schema.sql file

ALTER TABLE `users` ADD COLUMN `password_changed` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0 = using universal password, 1 = changed to personal password' AFTER `status`;

-- Update existing approved teachers to require password change
UPDATE `users` SET `password_changed` = 0 WHERE `role` = 'teacher' AND `status` = 'approved';