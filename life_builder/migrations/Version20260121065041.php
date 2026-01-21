<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260121065041 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE signalement ADD reported_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE signalement ADD CONSTRAINT FK_F4B5511471CE806 FOREIGN KEY (reported_by_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_F4B5511471CE806 ON signalement (reported_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE signalement DROP CONSTRAINT FK_F4B5511471CE806');
        $this->addSql('DROP INDEX IDX_F4B5511471CE806');
        $this->addSql('ALTER TABLE signalement DROP reported_by_id');
    }
}
