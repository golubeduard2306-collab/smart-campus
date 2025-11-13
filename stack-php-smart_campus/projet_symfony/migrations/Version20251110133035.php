<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251110133035 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Rename id_salle to id in one atomic operation
        $this->addSql('ALTER TABLE salle DROP PRIMARY KEY, CHANGE id_salle id INT AUTO_INCREMENT NOT NULL, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE systeme_acquisition ADD relation_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE systeme_acquisition ADD CONSTRAINT FK_5F4381C93256915B FOREIGN KEY (relation_id) REFERENCES salle (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5F4381C93256915B ON systeme_acquisition (relation_id)');
    }

    public function down(Schema $schema): void
    {
        // Revert id back to id_salle
        $this->addSql('ALTER TABLE salle DROP PRIMARY KEY, CHANGE id id_salle INT AUTO_INCREMENT NOT NULL, ADD PRIMARY KEY (id_salle)');
        $this->addSql('ALTER TABLE systeme_acquisition DROP FOREIGN KEY FK_5F4381C93256915B');
        $this->addSql('DROP INDEX UNIQ_5F4381C93256915B ON systeme_acquisition');
        $this->addSql('ALTER TABLE systeme_acquisition DROP relation_id');
    }
}
