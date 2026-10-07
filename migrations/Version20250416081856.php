<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250416081856 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE project_child DROP FOREIGN KEY FK_31E65DB79393F8FE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_31E65DB79393F8FE ON project_child
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project_child DROP partner_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user CHANGE token token VARCHAR(255) DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE project_child ADD partner_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project_child ADD CONSTRAINT FK_31E65DB79393F8FE FOREIGN KEY (partner_id) REFERENCES partner (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_31E65DB79393F8FE ON project_child (partner_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user CHANGE token token VARCHAR(255) NOT NULL
        SQL);
    }
}
