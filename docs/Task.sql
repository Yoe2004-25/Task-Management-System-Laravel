CREATE TABLE `users` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(255),
  `email` varchar(255),
  `email_verified_at` timestamp,
  `password` varchar(255),
  `role` varchar(255) DEFAULT 'user' COMMENT 'Enum: user, admin',
  `remember_token` varchar(255),
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `tasks` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `user_id` bigint COMMENT 'onDelete: cascade',
  `title` varchar(255),
  `description` text,
  `status` varchar(255) DEFAULT 'pending' COMMENT 'Enum: pending, in_progress, completed',
  `priority` varchar(255) DEFAULT 'medium' COMMENT 'Enum: low, medium, high',
  `due_date` date,
  `completed_at` timestamp,
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `task_logs` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `task_id` bigint COMMENT 'onDelete: cascade',
  `user_id` bigint,
  `action` varchar(255),
  `old_value` text,
  `new_value` text,
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE INDEX `tasks_index_0` ON `tasks` (`user_id`, `status`);

CREATE INDEX `tasks_index_1` ON `tasks` (`due_date`);

ALTER TABLE `tasks` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `task_logs` ADD FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`);

ALTER TABLE `task_logs` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
