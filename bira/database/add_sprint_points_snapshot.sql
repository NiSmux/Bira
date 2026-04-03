-- Add point snapshot columns to releases (sprints) for velocity report
-- These are saved when a sprint is completed so historical data is preserved
-- Run this in phpMyAdmin or your DB client

ALTER TABLE `releases`
    ADD COLUMN `completed_points` INT UNSIGNED NULL DEFAULT NULL AFTER `created_by`,
    ADD COLUMN `total_points`     INT UNSIGNED NULL DEFAULT NULL AFTER `completed_points`;
