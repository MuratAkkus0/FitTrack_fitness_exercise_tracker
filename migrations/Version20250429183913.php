<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250429183913 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE training_program_training_exercises (training_program_id INT NOT NULL, training_exercises_id INT NOT NULL, INDEX IDX_8DF4771F8406BD6C (training_program_id), INDEX IDX_8DF4771F2E95CC7 (training_exercises_id), PRIMARY KEY(training_program_id, training_exercises_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE workout_log_details (id INT AUTO_INCREMENT NOT NULL, log_id_id INT DEFAULT NULL, exercise_id_id INT DEFAULT NULL, reps INT NOT NULL, weight NUMERIC(5, 2) NOT NULL, sets INT NOT NULL, notes VARCHAR(255) DEFAULT NULL, INDEX IDX_47EA7D6C5F3A750A (log_id_id), INDEX IDX_47EA7D6C5A726995 (exercise_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE workout_logs (id INT AUTO_INCREMENT NOT NULL, training_program_id_id INT DEFAULT NULL, user_id_id INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', duration NUMERIC(3, 2) NOT NULL, is_complated TINYINT(1) NOT NULL, notes VARCHAR(255) DEFAULT NULL, INDEX IDX_8801C5209F848230 (training_program_id_id), INDEX IDX_8801C5209D86650F (user_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
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
            ALTER TABLE workout_logs ADD CONSTRAINT FK_8801C5209F848230 FOREIGN KEY (training_program_id_id) REFERENCES training_program (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE workout_logs ADD CONSTRAINT FK_8801C5209D86650F FOREIGN KEY (user_id_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE workout_log DROP FOREIGN KEY FK_6F5B68D9D86650F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE blog_post DROP FOREIGN KEY FK_BA5AE01D9D86650F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE blog_post_workout_log DROP FOREIGN KEY FK_9EB61744A77FBEAF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE blog_post_workout_log DROP FOREIGN KEY FK_9EB61744F0E44248
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE workout_log
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE blog_post
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE blog_post_workout_log
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE workout_log (id INT AUTO_INCREMENT NOT NULL, user_id_id INT DEFAULT NULL, notes VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, duration VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_6F5B68D9D86650F (user_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE blog_post (id INT AUTO_INCREMENT NOT NULL, user_id_id INT NOT NULL, title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, content VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, cerated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', is_public TINYINT(1) NOT NULL, INDEX IDX_BA5AE01D9D86650F (user_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE blog_post_workout_log (blog_post_id INT NOT NULL, workout_log_id INT NOT NULL, INDEX IDX_9EB61744A77FBEAF (blog_post_id), INDEX IDX_9EB61744F0E44248 (workout_log_id), PRIMARY KEY(blog_post_id, workout_log_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE workout_log ADD CONSTRAINT FK_6F5B68D9D86650F FOREIGN KEY (user_id_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE blog_post ADD CONSTRAINT FK_BA5AE01D9D86650F FOREIGN KEY (user_id_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE blog_post_workout_log ADD CONSTRAINT FK_9EB61744A77FBEAF FOREIGN KEY (blog_post_id) REFERENCES blog_post (id) ON UPDATE NO ACTION ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE blog_post_workout_log ADD CONSTRAINT FK_9EB61744F0E44248 FOREIGN KEY (workout_log_id) REFERENCES workout_log (id) ON UPDATE NO ACTION ON DELETE CASCADE
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
            ALTER TABLE workout_logs DROP FOREIGN KEY FK_8801C5209F848230
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE workout_logs DROP FOREIGN KEY FK_8801C5209D86650F
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE training_program_training_exercises
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE workout_log_details
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE workout_logs
        SQL);
    }
}
