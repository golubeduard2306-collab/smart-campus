<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251112104503 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE salle ADD sa_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE salle ADD CONSTRAINT FK_4E977E5C62CAE146 FOREIGN KEY (sa_id) REFERENCES systeme_acquisition (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4E977E5C62CAE146 ON salle (sa_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE salle DROP FOREIGN KEY FK_4E977E5C62CAE146');
        $this->addSql('DROP INDEX UNIQ_4E977E5C62CAE146 ON salle');
        $this->addSql('ALTER TABLE salle DROP sa_id');
    }
}
