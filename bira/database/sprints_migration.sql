-- Sprint migration: repurpose releases table
-- Run this in phpMyAdmin or your DB client

-- Step 1: Drop old FK constraints
ALTER TABLE `releases`
  DROP FOREIGN KEY `fk_rel_type`,
  DROP FOREIGN KEY `fk_rel_status`,
  DROP FOREIGN KEY `fk_rel_parent`;

-- Step 2: Drop old indexes
ALTER TABLE `releases`
  DROP INDEX `idx_rel_type`,
  DROP INDEX `idx_rel_status`,
  DROP INDEX `idx_rel_parent`;

-- Step 3: Replace columns
ALTER TABLE `releases`
  DROP COLUMN `release_type_id`,
  DROP COLUMN `status_id`,
  DROP COLUMN `parent_release_id`,
  ADD COLUMN `board_id` int(10) UNSIGNED NOT NULL AFTER `id`,
  ADD COLUMN `goal` text DEFAULT NULL AFTER `name`,
  ADD COLUMN `status` enum('planned','active','completed') NOT NULL DEFAULT 'planned' AFTER `end_date`,
  ADD COLUMN `created_by` int(10) UNSIGNED DEFAULT NULL AFTER `status`;

-- Step 4: Add new indexes and FK constraints
ALTER TABLE `releases`
  ADD KEY `idx_sprint_board` (`board_id`),
  ADD KEY `idx_sprint_created_by` (`created_by`),
  ADD CONSTRAINT `fk_sprint_board` FOREIGN KEY (`board_id`) REFERENCES `boards` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_sprint_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

-- Step 5: Drop now-unused tables (empty, never used)
DROP TABLE IF EXISTS `release_statuses`;
DROP TABLE IF EXISTS `release_types`;
