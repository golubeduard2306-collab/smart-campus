<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251110135220 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE salle ADD id INT AUTO_INCREMENT NOT NULL, DROP id_salle, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE systeme_acquisition ADD salle_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE systeme_acquisition ADD CONSTRAINT FK_5F4381C9DC304035 FOREIGN KEY (salle_id) REFERENCES salle (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5F4381C9DC304035 ON systeme_acquisition (salle_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE systeme_acquisition DROP FOREIGN KEY FK_5F4381C9DC304035');
        $this->addSql('DROP INDEX UNIQ_5F4381C9DC304035 ON systeme_acquisition');
        $this->addSql('ALTER TABLE systeme_acquisition DROP salle_id');
        $this->addSql('ALTER TABLE salle MODIFY id INT NOT NULL');
        $this->addSql('DROP INDEX `primary` ON salle');
        $this->addSql('ALTER TABLE salle ADD id_salle INT NOT NULL, DROP id');
    }
}
