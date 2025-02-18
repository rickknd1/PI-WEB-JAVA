<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250218170106 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gamifications ADD CONSTRAINT FK_DB1F936B2811BE9E FOREIGN KEY (type_abonnement) REFERENCES abonnements (id)');
        $this->addSql('CREATE INDEX IDX_DB1F936B2811BE9E ON gamifications (type_abonnement)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gamifications DROP FOREIGN KEY FK_DB1F936B2811BE9E');
        $this->addSql('DROP INDEX IDX_DB1F936B2811BE9E ON gamifications');
    }
}
