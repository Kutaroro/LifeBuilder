<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260121074515 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE signalement ADD status VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE signalement ADD mod_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE signalement ADD CONSTRAINT FK_F4B55114338E21CD FOREIGN KEY (mod_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F4B55114338E21CD ON signalement (mod_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE signalement DROP CONSTRAINT FK_F4B55114338E21CD');
        $this->addSql('DROP INDEX UNIQ_F4B55114338E21CD');
        $this->addSql('ALTER TABLE signalement DROP status');
        $this->addSql('ALTER TABLE signalement DROP mod_id');
    }
}
