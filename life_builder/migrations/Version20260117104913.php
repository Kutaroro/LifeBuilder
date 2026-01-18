<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260117104913 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commentaire DROP CONSTRAINT fk_67f068bcf5e6ea5b');
        $this->addSql('DROP INDEX idx_67f068bcf5e6ea5b');
        $this->addSql('ALTER TABLE commentaire RENAME COLUMN mentioned_utilisateur_id TO mentioned_user_id');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BCE6655814 FOREIGN KEY (mentioned_user_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_67F068BCE6655814 ON commentaire (mentioned_user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commentaire DROP CONSTRAINT FK_67F068BCE6655814');
        $this->addSql('DROP INDEX IDX_67F068BCE6655814');
        $this->addSql('ALTER TABLE commentaire RENAME COLUMN mentioned_user_id TO mentioned_utilisateur_id');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT fk_67f068bcf5e6ea5b FOREIGN KEY (mentioned_utilisateur_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_67f068bcf5e6ea5b ON commentaire (mentioned_utilisateur_id)');
    }
}
