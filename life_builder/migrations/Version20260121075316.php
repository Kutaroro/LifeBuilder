<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260121075316 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_f4b55114338e21cd');
        $this->addSql('CREATE INDEX IDX_F4B55114338E21CD ON signalement (mod_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_F4B55114338E21CD');
        $this->addSql('CREATE UNIQUE INDEX uniq_f4b55114338e21cd ON signalement (mod_id)');
    }
}
