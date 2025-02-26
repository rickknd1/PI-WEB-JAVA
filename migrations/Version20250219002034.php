<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250219002034 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE membre_comunity (id INT AUTO_INCREMENT NOT NULL, id_user_id INT DEFAULT NULL, status VARCHAR(255) NOT NULL, date_adhesion DATETIME NOT NULL, INDEX IDX_88F7DAC079F37AE5 (id_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE membre_comunity ADD CONSTRAINT FK_88F7DAC079F37AE5 FOREIGN KEY (id_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE gamifications ADD CONSTRAINT FK_DB1F936B2811BE9E FOREIGN KEY (type_abonnement) REFERENCES abonnements (id)');
        $this->addSql('CREATE INDEX IDX_DB1F936B2811BE9E ON gamifications (type_abonnement)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE membre_comunity DROP FOREIGN KEY FK_88F7DAC079F37AE5');
        $this->addSql('DROP TABLE membre_comunity');
        $this->addSql('ALTER TABLE gamifications DROP FOREIGN KEY FK_DB1F936B2811BE9E');
        $this->addSql('DROP INDEX IDX_DB1F936B2811BE9E ON gamifications');
    }
}
