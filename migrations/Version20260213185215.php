<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260213185215 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE secteur DROP FOREIGN KEY FK_8045251FFCC7117B');
        $this->addSql('DROP INDEX IDX_8045251FFCC7117B ON secteur');
        $this->addSql('ALTER TABLE secteur DROP recrutement_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE secteur ADD recrutement_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE secteur ADD CONSTRAINT FK_8045251FFCC7117B FOREIGN KEY (recrutement_id) REFERENCES recrutement (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_8045251FFCC7117B ON secteur (recrutement_id)');
    }
}
