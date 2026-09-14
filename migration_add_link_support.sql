-- Migration to add link upload support and increase file size limits
-- Run this after backing up your database
-- Date: April 19, 2026

-- Add new columns to papers table for link support
ALTER TABLE `papers`
ADD COLUMN `upload_type` ENUM('file', 'link') NOT NULL DEFAULT 'file' AFTER `track`,
ADD COLUMN `link_url` VARCHAR(500) DEFAULT NULL AFTER `upload_type`;

-- Update existing records to have upload_type = 'file'
UPDATE `papers` SET `upload_type` = 'file' WHERE `upload_type` = '';

-- Add index for better performance
ALTER TABLE `papers` ADD KEY `idx_upload_type` (`upload_type`);