<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250527074341 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE fitness_goal (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, title VARCHAR(100) NOT NULL, description VARCHAR(500) DEFAULT NULL, goal_type VARCHAR(50) NOT NULL, target_value DOUBLE PRECISION DEFAULT NULL, current_value DOUBLE PRECISION DEFAULT NULL, unit VARCHAR(20) DEFAULT NULL, start_date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', target_date DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', is_active TINYINT(1) NOT NULL, is_completed TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', completed_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_53C6AFC0A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fitness_goal ADD CONSTRAINT FK_53C6AFC0A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users ADD reset_token VARCHAR(255) DEFAULT NULL, ADD reset_token_expires_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE fitness_goal DROP FOREIGN KEY FK_53C6AFC0A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE fitness_goal
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users DROP reset_token, DROP reset_token_expires_at
        SQL);
    }
}
