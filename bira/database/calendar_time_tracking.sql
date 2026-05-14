-- ============================================================
-- Calendar & Time Tracking tables
-- ============================================================

CREATE TABLE IF NOT EXISTS `time_logs` (
    `id`           INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`      INT(10) UNSIGNED NOT NULL,
    `work_item_id` INT(10) UNSIGNED NULL,
    `logged_date`  DATE NOT NULL,
    `minutes`      INT UNSIGNED NOT NULL DEFAULT 0,
    `note`         TEXT NULL,
    `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `time_logs_user_id_index` (`user_id`),
    INDEX `time_logs_work_item_id_index` (`work_item_id`),
    INDEX `time_logs_logged_date_index` (`logged_date`),
    CONSTRAINT `time_logs_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `time_logs_work_item_id_fk` FOREIGN KEY (`work_item_id`) REFERENCES `work_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `calendar_notes` (
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    INT(10) UNSIGNED NOT NULL,
    `note_date`  DATE NOT NULL,
    `content`    TEXT NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `calendar_notes_user_date_unique` (`user_id`, `note_date`),
    INDEX `calendar_notes_user_id_index` (`user_id`),
    CONSTRAINT `calendar_notes_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
