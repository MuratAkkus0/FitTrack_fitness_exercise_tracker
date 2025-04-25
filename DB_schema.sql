-- TABLES
CREATE TABLE `user` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(25) NOT NULL,
  `surname` VARCHAR(25) NOT NULL,
  `email` VARCHAR(180) UNIQUE NOT NULL,
  `roles` JSON NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `username` VARCHAR(50) UNIQUE NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `profile_image` VARCHAR(255) NULL
) ENGINE=InnoDB;

CREATE TABLE `exercise` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `muscle_group` ENUM('chest', 'back', 'legs', 'shoulders', 'arms', 'core') NOT NULL,
  `description` TEXT NULL
) ENGINE=InnoDB;

CREATE TABLE `training_program` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `user`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `program_exercise` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `program_id` INT NOT NULL,
  `exercise_id` INT NOT NULL,
  `sets` INT NOT NULL,
  `reps` INT NOT NULL,
  `rest_interval` INT COMMENT 'in seconds',
  FOREIGN KEY (`program_id`) REFERENCES `training_program`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`exercise_id`) REFERENCES `exercise`(`id`)
) ENGINE=InnoDB;

CREATE TABLE `workout_log` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `program_id` INT NOT NULL,
  `completed_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `notes` TEXT NULL,
  `duration` INT COMMENT 'in minutes',
  FOREIGN KEY (`user_id`) REFERENCES `user`(`id`),
  FOREIGN KEY (`program_id`) REFERENCES `training_program`(`id`)
) ENGINE=InnoDB;

CREATE TABLE `blog_post` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `workout_log_id` INT NULL,
  `title` VARCHAR(120) NOT NULL,
  `content` TEXT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `is_public` BOOLEAN DEFAULT FALSE,
  FOREIGN KEY (`user_id`) REFERENCES `user`(`id`),
  FOREIGN KEY (`workout_log_id`) REFERENCES `workout_log`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- SAMPLE DATA
INSERT INTO `exercise` (`name`, `muscle_group`, `difficulty_level`, `description`) VALUES
('Bench Press', 'chest', 'intermediate', 'Flat bench barbell press'),
('Squat', 'legs', 'intermediate', 'Barbell back squat'),
('Pull-up', 'back', 'advanced', 'Bodyweight pull exercise');