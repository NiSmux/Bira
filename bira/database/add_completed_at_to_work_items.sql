-- Add completed_at timestamp to work_items for burndown chart tracking
-- Run this in phpMyAdmin or your DB client

ALTER TABLE `work_items`
    ADD COLUMN `completed_at` DATETIME NULL DEFAULT NULL AFTER `release_id`;
