-- Run this script in phpMyAdmin to add the 'is_backlog' column to the 'workflow_statuses' table.
-- This column allows the Bira platform to distinguish between normal columns and the globally dragged backlog area.

ALTER TABLE `workflow_statuses` 
ADD COLUMN `is_backlog` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_done`;
