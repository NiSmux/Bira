-- Planning Poker tables for Bira
-- Run this SQL on your `domase` database via phpMyAdmin

CREATE TABLE `poker_sessions` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `team_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `time_limit` int(11) NOT NULL DEFAULT 300,
  `status` enum('active','completed') NOT NULL DEFAULT 'active',
  `created_by` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `finished_at` datetime DEFAULT NULL,
  CONSTRAINT `fk_ps_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ps_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `poker_session_items` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `session_id` int(10) UNSIGNED NOT NULL,
  `work_item_id` int(10) UNSIGNED NOT NULL,
  `order_index` int(11) NOT NULL DEFAULT 0,
  `final_points` int(11) DEFAULT NULL,
  CONSTRAINT `fk_psi_session` FOREIGN KEY (`session_id`) REFERENCES `poker_sessions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_psi_item` FOREIGN KEY (`work_item_id`) REFERENCES `work_items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `poker_votes` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `poker_session_item_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `points` int(11) DEFAULT NULL,
  `voted_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_vote` (`poker_session_item_id`, `user_id`),
  CONSTRAINT `fk_pv_psi` FOREIGN KEY (`poker_session_item_id`) REFERENCES `poker_session_items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_pv_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
