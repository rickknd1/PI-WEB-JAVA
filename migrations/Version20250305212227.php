<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250305212227 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reaction ADD CONSTRAINT FK_A4D707F7F8697D13 FOREIGN KEY (comment_id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE share DROP FOREIGN KEY FK_EF069D5A4B89032C');
        $this->addSql('ALTER TABLE share CHANGE post_id post_id INT NOT NULL');
        $this->addSql('ALTER TABLE share ADD CONSTRAINT FK_EF069D5A4B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reaction DROP FOREIGN KEY FK_A4D707F7F8697D13');
        $this->addSql('ALTER TABLE share DROP FOREIGN KEY FK_EF069D5A4B89032C');
        $this->addSql('ALTER TABLE share CHANGE post_id post_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE share ADD CONSTRAINT FK_EF069D5A4B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
    }
}
