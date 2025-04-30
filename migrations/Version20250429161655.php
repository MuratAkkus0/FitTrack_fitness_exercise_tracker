<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250429161655 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE blog_post (id INT AUTO_INCREMENT NOT NULL, user_id_id INT NOT NULL, title VARCHAR(255) NOT NULL, content VARCHAR(255) NOT NULL, cerated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', is_public TINYINT(1) NOT NULL, INDEX IDX_BA5AE01D9D86650F (user_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE blog_post_workout_log (blog_post_id INT NOT NULL, workout_log_id INT NOT NULL, INDEX IDX_9EB61744A77FBEAF (blog_post_id), INDEX IDX_9EB61744F0E44248 (workout_log_id), PRIMARY KEY(blog_post_id, workout_log_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE training_exercises (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(40) NOT NULL, description VARCHAR(255) NOT NULL, target_muscle_group VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE training_program (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(25) NOT NULL, surname VARCHAR(25) NOT NULL, username VARCHAR(40) NOT NULL, pass VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, profile_image VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE workout_log (id INT AUTO_INCREMENT NOT NULL, user_id_id INT DEFAULT NULL, notes VARCHAR(255) NOT NULL, duration VARCHAR(255) DEFAULT NULL, INDEX IDX_6F5B68D9D86650F (user_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE blog_post ADD CONSTRAINT FK_BA5AE01D9D86650F FOREIGN KEY (user_id_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE blog_post_workout_log ADD CONSTRAINT FK_9EB61744A77FBEAF FOREIGN KEY (blog_post_id) REFERENCES blog_post (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE blog_post_workout_log ADD CONSTRAINT FK_9EB61744F0E44248 FOREIGN KEY (workout_log_id) REFERENCES workout_log (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE workout_log ADD CONSTRAINT FK_6F5B68D9D86650F FOREIGN KEY (user_id_id) REFERENCES users (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
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
            ALTER TABLE workout_log DROP FOREIGN KEY FK_6F5B68D9D86650F
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE blog_post
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE blog_post_workout_log
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE training_exercises
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE training_program
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE users
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE workout_log
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
