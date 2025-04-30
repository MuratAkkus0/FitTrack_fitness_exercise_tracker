<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250429165556 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE training_program ADD users_id INT DEFAULT NULL, ADD name VARCHAR(45) NOT NULL, ADD description VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE training_program ADD CONSTRAINT FK_4FD3E78A67B3B43D FOREIGN KEY (users_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4FD3E78A67B3B43D ON training_program (users_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE training_program DROP FOREIGN KEY FK_4FD3E78A67B3B43D
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_4FD3E78A67B3B43D ON training_program
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE training_program DROP users_id, DROP name, DROP description
        SQL);
    }
}
