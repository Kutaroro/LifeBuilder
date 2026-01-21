<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260121080234 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE signalement ADD personnage_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE signalement ADD CONSTRAINT FK_F4B551145E315342 FOREIGN KEY (personnage_id) REFERENCES personnage (id)');
        $this->addSql('CREATE INDEX IDX_F4B551145E315342 ON signalement (personnage_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE signalement DROP CONSTRAINT FK_F4B551145E315342');
        $this->addSql('DROP INDEX IDX_F4B551145E315342');
        $this->addSql('ALTER TABLE signalement DROP personnage_id');
    }
}
