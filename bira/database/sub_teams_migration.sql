-- Sub-Team Migration
-- Įkelk šį failą į savo duomenų bazę per phpMyAdmin arba mysql klientą.

-- 1. Sub-komandų lentelė (priklauso board'ui)
CREATE TABLE `board_sub_teams` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `board_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `created_by` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_sub_team_board_name` (`board_id`, `name`),
  KEY `idx_st_board` (`board_id`),
  CONSTRAINT `fk_st_board` FOREIGN KEY (`board_id`) REFERENCES `boards` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_st_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Sub-komandos nariai
CREATE TABLE `board_sub_team_members` (
  `sub_team_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`sub_team_id`, `user_id`),
  CONSTRAINT `fk_stm_sub_team` FOREIGN KEY (`sub_team_id`) REFERENCES `board_sub_teams` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_stm_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Pridėti sub_team_id į work_items
-- (assignee_id lieka naudotojams; sub_team_id – sub-komandai; tik vienas gali būti nenulis)
ALTER TABLE `work_items`
  ADD COLUMN `sub_team_id` int(10) UNSIGNED DEFAULT NULL AFTER `assignee_id`,
  ADD KEY `idx_wi_sub_team` (`sub_team_id`),
  ADD CONSTRAINT `fk_wi_sub_team` FOREIGN KEY (`sub_team_id`) REFERENCES `board_sub_teams` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
