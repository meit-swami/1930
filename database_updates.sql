-- Database updates for Omni Dimension integration
-- Run these SQL commands to update your database schema

-- Add Omni Dimension fields to sessions table
ALTER TABLE `sessions` 
ADD COLUMN IF NOT EXISTS `omni_call_id` VARCHAR(255) NULL AFTER `id`,
ADD COLUMN IF NOT EXISTS `omni_chat_id` VARCHAR(255) NULL AFTER `omni_call_id`,
ADD COLUMN IF NOT EXISTS `call_type` ENUM('inbound', 'outbound', 'chat', 'web') DEFAULT 'inbound' AFTER `language`,
ADD COLUMN IF NOT EXISTS `phone_number` VARCHAR(20) NULL AFTER `call_type`;

-- Add index for faster lookups
CREATE INDEX IF NOT EXISTS `idx_omni_call_id` ON `sessions` (`omni_call_id`);
CREATE INDEX IF NOT EXISTS `idx_omni_chat_id` ON `sessions` (`omni_chat_id`);

-- Create table for Omni Dimension account credits tracking
CREATE TABLE IF NOT EXISTS `omnidimension_credits` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `credits_available` DECIMAL(10, 2) DEFAULT 0,
    `credits_used` DECIMAL(10, 2) DEFAULT 0,
    `last_updated` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert initial credits record
INSERT INTO `omnidimension_credits` (`credits_available`, `credits_used`) 
VALUES (0, 0) 
ON DUPLICATE KEY UPDATE `credits_available` = `credits_available`;

-- Create table for call/chat events log
CREATE TABLE IF NOT EXISTS `omnidimension_events` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `event_type` VARCHAR(50) NOT NULL,
    `call_id` VARCHAR(255) NULL,
    `chat_id` VARCHAR(255) NULL,
    `session_id` VARCHAR(255) NULL,
    `event_data` JSON NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_event_type` (`event_type`),
    INDEX `idx_call_id` (`call_id`),
    INDEX `idx_chat_id` (`chat_id`),
    INDEX `idx_session_id` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

