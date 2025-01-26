<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250123231241 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE societe (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, siret VARCHAR(14) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, telephone VARCHAR(15) DEFAULT NULL, UNIQUE INDEX UNIQ_19653DBD6C6E55B5 (nom), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE societe_user (societe_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_EFBCEA58FCF77503 (societe_id), INDEX IDX_EFBCEA58A76ED395 (user_id), PRIMARY KEY(societe_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE societe_user ADD CONSTRAINT FK_EFBCEA58FCF77503 FOREIGN KEY (societe_id) REFERENCES societe (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE societe_user ADD CONSTRAINT FK_EFBCEA58A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE frais ADD societe_id INT NOT NULL, ADD description VARCHAR(255) DEFAULT NULL, ADD justificatif VARCHAR(255) DEFAULT NULL, ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD statut VARCHAR(20) NOT NULL, DROP entreprise');
        $this->addSql('ALTER TABLE frais ADD CONSTRAINT FK_25404C98FCF77503 FOREIGN KEY (societe_id) REFERENCES societe (id)');
        $this->addSql('CREATE INDEX IDX_25404C98FCF77503 ON frais (societe_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE frais DROP FOREIGN KEY FK_25404C98FCF77503');
        $this->addSql('ALTER TABLE societe_user DROP FOREIGN KEY FK_EFBCEA58FCF77503');
        $this->addSql('ALTER TABLE societe_user DROP FOREIGN KEY FK_EFBCEA58A76ED395');
        $this->addSql('DROP TABLE societe');
        $this->addSql('DROP TABLE societe_user');
        $this->addSql('DROP INDEX IDX_25404C98FCF77503 ON frais');
        $this->addSql('ALTER TABLE frais ADD entreprise VARCHAR(100) NOT NULL, DROP societe_id, DROP description, DROP justificatif, DROP created_at, DROP updated_at, DROP statut');
    }
}
