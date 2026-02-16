<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260215011848 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE candidature ADD secteur_id INT DEFAULT NULL, ADD cfa_etablissement_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE candidature ADD CONSTRAINT FK_E33BD3B89F7E4405 FOREIGN KEY (secteur_id) REFERENCES secteur (id)');
        $this->addSql('ALTER TABLE candidature ADD CONSTRAINT FK_E33BD3B8EEFC5FE5 FOREIGN KEY (cfa_etablissement_id) REFERENCES cfa_etablissement (id)');
        $this->addSql('CREATE INDEX IDX_E33BD3B89F7E4405 ON candidature (secteur_id)');
        $this->addSql('CREATE INDEX IDX_E33BD3B8EEFC5FE5 ON candidature (cfa_etablissement_id)');
        $this->addSql('ALTER TABLE filiere ADD candidature_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE filiere ADD CONSTRAINT FK_2ED05D9EB6121583 FOREIGN KEY (candidature_id) REFERENCES candidature (id)');
        $this->addSql('CREATE INDEX IDX_2ED05D9EB6121583 ON filiere (candidature_id)');
        $this->addSql('ALTER TABLE recrutement DROP cible');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE candidature DROP FOREIGN KEY FK_E33BD3B89F7E4405');
        $this->addSql('ALTER TABLE candidature DROP FOREIGN KEY FK_E33BD3B8EEFC5FE5');
        $this->addSql('DROP INDEX IDX_E33BD3B89F7E4405 ON candidature');
        $this->addSql('DROP INDEX IDX_E33BD3B8EEFC5FE5 ON candidature');
        $this->addSql('ALTER TABLE candidature DROP secteur_id, DROP cfa_etablissement_id');
        $this->addSql('ALTER TABLE filiere DROP FOREIGN KEY FK_2ED05D9EB6121583');
        $this->addSql('DROP INDEX IDX_2ED05D9EB6121583 ON filiere');
        $this->addSql('ALTER TABLE filiere DROP candidature_id');
        $this->addSql('ALTER TABLE recrutement ADD cible VARCHAR(255) NOT NULL');
    }
}
