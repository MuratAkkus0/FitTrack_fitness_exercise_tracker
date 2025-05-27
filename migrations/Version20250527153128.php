<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250527153128 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE favorite_exercise (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, exercise_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_48DCDEDA76ED395 (user_id), INDEX IDX_48DCDEDE934951A (exercise_id), UNIQUE INDEX user_exercise_unique (user_id, exercise_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE favorite_exercise ADD CONSTRAINT FK_48DCDEDA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE favorite_exercise ADD CONSTRAINT FK_48DCDEDE934951A FOREIGN KEY (exercise_id) REFERENCES training_exercises (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE favorite_exercise DROP FOREIGN KEY FK_48DCDEDA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE favorite_exercise DROP FOREIGN KEY FK_48DCDEDE934951A
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE favorite_exercise
        SQL);
    }
}
