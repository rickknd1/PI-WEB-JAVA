<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250225133425 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE inscription_abonnement (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, abonnement_id INT DEFAULT NULL, subscribed_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expired_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', mode_paiement VARCHAR(255) NOT NULL, renouvellement_auto TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_26D8E52FA76ED395 (user_id), UNIQUE INDEX UNIQ_26D8E52FF1D74413 (abonnement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE inscription_abonnement ADD CONSTRAINT FK_26D8E52FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE inscription_abonnement ADD CONSTRAINT FK_26D8E52FF1D74413 FOREIGN KEY (abonnement_id) REFERENCES abonnements (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inscription_abonnement DROP FOREIGN KEY FK_26D8E52FA76ED395');
        $this->addSql('ALTER TABLE inscription_abonnement DROP FOREIGN KEY FK_26D8E52FF1D74413');
        $this->addSql('DROP TABLE inscription_abonnement');
    }
}
