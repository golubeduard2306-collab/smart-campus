<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251114133423 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE demande (id INT AUTO_INCREMENT NOT NULL, id_salle_id INT NOT NULL, id_sa_id INT NOT NULL, type_demande VARCHAR(20) NOT NULL, date_demande DATETIME NOT NULL, statut VARCHAR(20) NOT NULL, INDEX IDX_2694D7A58CEBACA0 (id_salle_id), INDEX IDX_2694D7A58C4FA076 (id_sa_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE salle (id INT AUTO_INCREMENT NOT NULL, sa_id INT DEFAULT NULL, nom_salle VARCHAR(255) NOT NULL, etage INT NOT NULL, nb_fenetres INT NOT NULL, date_creation DATETIME NOT NULL, date_modification DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_4E977E5C62CAE146 (sa_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE systeme_acquisition (id INT AUTO_INCREMENT NOT NULL, statut VARCHAR(20) NOT NULL, date_creation DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE demande ADD CONSTRAINT FK_2694D7A58CEBACA0 FOREIGN KEY (id_salle_id) REFERENCES salle (id)');
        $this->addSql('ALTER TABLE demande ADD CONSTRAINT FK_2694D7A58C4FA076 FOREIGN KEY (id_sa_id) REFERENCES systeme_acquisition (id)');
        $this->addSql('ALTER TABLE salle ADD CONSTRAINT FK_4E977E5C62CAE146 FOREIGN KEY (sa_id) REFERENCES systeme_acquisition (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE demande DROP FOREIGN KEY FK_2694D7A58CEBACA0');
        $this->addSql('ALTER TABLE demande DROP FOREIGN KEY FK_2694D7A58C4FA076');
        $this->addSql('ALTER TABLE salle DROP FOREIGN KEY FK_4E977E5C62CAE146');
        $this->addSql('DROP TABLE demande');
        $this->addSql('DROP TABLE salle');
        $this->addSql('DROP TABLE systeme_acquisition');
    }
}
