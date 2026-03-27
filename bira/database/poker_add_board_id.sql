-- Add board_id to poker_sessions
-- Run this SQL on your `domase` database via phpMyAdmin

ALTER TABLE `poker_sessions`
    ADD COLUMN `board_id` int(10) UNSIGNED NULL AFTER `team_id`,
    ADD CONSTRAINT `fk_ps_board` FOREIGN KEY (`board_id`) REFERENCES `boards` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
