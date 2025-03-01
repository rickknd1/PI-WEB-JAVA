<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250228203113 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE chat_room_membres (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, chat_room_id INT NOT NULL, INDEX IDX_4B8336FDA76ED395 (user_id), INDEX IDX_4B8336FD1819BCFA (chat_room_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messages (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, chat_room_id INT NOT NULL, content LONGTEXT NOT NULL, sent_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_DB021E96A76ED395 (user_id), INDEX IDX_DB021E961819BCFA (chat_room_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE chat_room_membres ADD CONSTRAINT FK_4B8336FDA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE chat_room_membres ADD CONSTRAINT FK_4B8336FD1819BCFA FOREIGN KEY (chat_room_id) REFERENCES chat_rooms (id)');
        $this->addSql('ALTER TABLE messages ADD CONSTRAINT FK_DB021E96A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE messages ADD CONSTRAINT FK_DB021E961819BCFA FOREIGN KEY (chat_room_id) REFERENCES chat_rooms (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chat_room_membres DROP FOREIGN KEY FK_4B8336FDA76ED395');
        $this->addSql('ALTER TABLE chat_room_membres DROP FOREIGN KEY FK_4B8336FD1819BCFA');
        $this->addSql('ALTER TABLE messages DROP FOREIGN KEY FK_DB021E96A76ED395');
        $this->addSql('ALTER TABLE messages DROP FOREIGN KEY FK_DB021E961819BCFA');
        $this->addSql('DROP TABLE chat_room_membres');
        $this->addSql('DROP TABLE messages');
    }
}
