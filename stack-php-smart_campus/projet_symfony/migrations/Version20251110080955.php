<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251110080955 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE demande (id INT AUTO_INCREMENT NOT NULL, systeme_acquisition_id INT NOT NULL, id_demande INT NOT NULL, type_demande VARCHAR(20) NOT NULL, date_demande DATETIME NOT NULL, status VARCHAR(20) NOT NULL, INDEX IDX_2694D7A5E6ADA47F (systeme_acquisition_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE demande ADD CONSTRAINT FK_2694D7A5E6ADA47F FOREIGN KEY (systeme_acquisition_id) REFERENCES systeme_acquisition (id)');
        $this->addSql('ALTER TABLE salle ADD demande_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE salle ADD CONSTRAINT FK_4E977E5C80E95E18 FOREIGN KEY (demande_id) REFERENCES demande (id)');
        $this->addSql('CREATE INDEX IDX_4E977E5C80E95E18 ON salle (demande_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE salle DROP FOREIGN KEY FK_4E977E5C80E95E18');
        $this->addSql('ALTER TABLE demande DROP FOREIGN KEY FK_2694D7A5E6ADA47F');
        $this->addSql('DROP TABLE demande');
        $this->addSql('DROP INDEX IDX_4E977E5C80E95E18 ON salle');
        $this->addSql('ALTER TABLE salle DROP demande_id');
    }
}
