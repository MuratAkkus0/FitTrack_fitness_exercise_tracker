<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250527074659 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE blog_post (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, workout_log_id INT DEFAULT NULL, title VARCHAR(120) NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', is_public TINYINT(1) NOT NULL, INDEX IDX_BA5AE01DA76ED395 (user_id), INDEX IDX_BA5AE01DF0E44248 (workout_log_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE blog_post ADD CONSTRAINT FK_BA5AE01DA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE blog_post ADD CONSTRAINT FK_BA5AE01DF0E44248 FOREIGN KEY (workout_log_id) REFERENCES workout_logs (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE blog_post DROP FOREIGN KEY FK_BA5AE01DA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE blog_post DROP FOREIGN KEY FK_BA5AE01DF0E44248
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE blog_post
        SQL);
    }
}
