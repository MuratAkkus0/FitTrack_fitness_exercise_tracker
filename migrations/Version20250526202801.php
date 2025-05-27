<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250526202801 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE training_exercises (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(40) NOT NULL, description VARCHAR(255) NOT NULL, target_muscle_group VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE training_program (id INT AUTO_INCREMENT NOT NULL, users_id INT DEFAULT NULL, name VARCHAR(45) NOT NULL, description VARCHAR(255) DEFAULT NULL, workouts_per_week INT DEFAULT NULL, duration_minutes INT DEFAULT NULL, difficulty_level VARCHAR(20) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', is_active TINYINT(1) NOT NULL, INDEX IDX_4FD3E78A67B3B43D (users_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE training_program_training_exercises (training_program_id INT NOT NULL, training_exercises_id INT NOT NULL, INDEX IDX_8DF4771F8406BD6C (training_program_id), INDEX IDX_8DF4771F2E95CC7 (training_exercises_id), PRIMARY KEY(training_program_id, training_exercises_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, name VARCHAR(45) NOT NULL, surname VARCHAR(45) NOT NULL, profile_image VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE workout_log_details (id INT AUTO_INCREMENT NOT NULL, log_id_id INT DEFAULT NULL, exercise_id_id INT DEFAULT NULL, reps INT NOT NULL, weight NUMERIC(5, 2) NOT NULL, sets INT NOT NULL, notes VARCHAR(255) DEFAULT NULL, INDEX IDX_47EA7D6C5F3A750A (log_id_id), INDEX IDX_47EA7D6C5A726995 (exercise_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE workout_logs (id INT AUTO_INCREMENT NOT NULL, user_id_id INT DEFAULT NULL, training_program_id_id INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', duration NUMERIC(3, 2) NOT NULL, is_complated TINYINT(1) NOT NULL, notes VARCHAR(255) DEFAULT NULL, INDEX IDX_8801C5209D86650F (user_id_id), INDEX IDX_8801C5209F848230 (training_program_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE training_program ADD CONSTRAINT FK_4FD3E78A67B3B43D FOREIGN KEY (users_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE training_program_training_exercises ADD CONSTRAINT FK_8DF4771F8406BD6C FOREIGN KEY (training_program_id) REFERENCES training_program (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE training_program_training_exercises ADD CONSTRAINT FK_8DF4771F2E95CC7 FOREIGN KEY (training_exercises_id) REFERENCES training_exercises (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE workout_log_details ADD CONSTRAINT FK_47EA7D6C5F3A750A FOREIGN KEY (log_id_id) REFERENCES workout_logs (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE workout_log_details ADD CONSTRAINT FK_47EA7D6C5A726995 FOREIGN KEY (exercise_id_id) REFERENCES training_exercises (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE workout_logs ADD CONSTRAINT FK_8801C5209D86650F FOREIGN KEY (user_id_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE workout_logs ADD CONSTRAINT FK_8801C5209F848230 FOREIGN KEY (training_program_id_id) REFERENCES training_program (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE training_program DROP FOREIGN KEY FK_4FD3E78A67B3B43D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE training_program_training_exercises DROP FOREIGN KEY FK_8DF4771F8406BD6C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE training_program_training_exercises DROP FOREIGN KEY FK_8DF4771F2E95CC7
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE workout_log_details DROP FOREIGN KEY FK_47EA7D6C5F3A750A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE workout_log_details DROP FOREIGN KEY FK_47EA7D6C5A726995
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE workout_logs DROP FOREIGN KEY FK_8801C5209D86650F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE workout_logs DROP FOREIGN KEY FK_8801C5209F848230
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE training_exercises
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE training_program
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE training_program_training_exercises
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE users
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE workout_log_details
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE workout_logs
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
