<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250416235354 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chat_room_membres CHANGE id id INT AUTO_INCREMENT NOT NULL, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE chat_room_membres ADD CONSTRAINT FK_4B8336FDA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE chat_room_membres ADD CONSTRAINT FK_4B8336FD1819BCFA FOREIGN KEY (chat_room_id) REFERENCES chat_rooms (id)');
        $this->addSql('CREATE INDEX IDX_4B8336FDA76ED395 ON chat_room_membres (user_id)');
        $this->addSql('CREATE INDEX IDX_4B8336FD1819BCFA ON chat_room_membres (chat_room_id)');
        $this->addSql('ALTER TABLE chat_rooms DROP FOREIGN KEY FK_7DDCF70DFDA7B0BF');
        $this->addSql('ALTER TABLE chat_rooms ADD CONSTRAINT FK_7DDCF70DFDA7B0BF FOREIGN KEY (community_id) REFERENCES community (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE comment CHANGE id id INT AUTO_INCREMENT NOT NULL, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C4B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_9474526C4B89032C ON comment (post_id)');
        $this->addSql('CREATE INDEX IDX_9474526CA76ED395 ON comment (user_id)');
        $this->addSql('ALTER TABLE community ADD statut TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE events ADD acces VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE gamifications DROP FOREIGN KEY FK_DB1F936B2811BE9E');
        $this->addSql('ALTER TABLE gamifications ADD CONSTRAINT FK_DB1F936B2811BE9E FOREIGN KEY (type_abonnement) REFERENCES abonnements (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE inscription_abonnement CHANGE id id INT AUTO_INCREMENT NOT NULL, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE inscription_abonnement ADD CONSTRAINT FK_26D8E52FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE inscription_abonnement ADD CONSTRAINT FK_26D8E52FF1D74413 FOREIGN KEY (abonnement_id) REFERENCES abonnements (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_26D8E52FA76ED395 ON inscription_abonnement (user_id)');
        $this->addSql('CREATE INDEX IDX_26D8E52FF1D74413 ON inscription_abonnement (abonnement_id)');
        $this->addSql('ALTER TABLE lieu_culturels CHANGE id id INT AUTO_INCREMENT NOT NULL, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE lieu_culturels ADD CONSTRAINT FK_6AC9CE84A73F0036 FOREIGN KEY (ville_id) REFERENCES ville (id)');
        $this->addSql('CREATE INDEX IDX_6AC9CE84A73F0036 ON lieu_culturels (ville_id)');
        $this->addSql('ALTER TABLE media CHANGE id id INT AUTO_INCREMENT NOT NULL, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE media ADD CONSTRAINT FK_6A2CA10CA2C806AC FOREIGN KEY (lieux_id) REFERENCES lieu_culturels (id)');
        $this->addSql('CREATE INDEX IDX_6A2CA10CA2C806AC ON media (lieux_id)');
        $this->addSql('ALTER TABLE membre_comunity DROP FOREIGN KEY FK_88F7DAC0A4C4F6C9');
        $this->addSql('DROP INDEX IDX_88F7DAC0A4C4F6C9 ON membre_comunity');
        $this->addSql('ALTER TABLE membre_comunity CHANGE id_comunity_id community_id INT NOT NULL');
        $this->addSql('ALTER TABLE membre_comunity ADD CONSTRAINT FK_88F7DAC0FDA7B0BF FOREIGN KEY (community_id) REFERENCES community (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_88F7DAC0FDA7B0BF ON membre_comunity (community_id)');
        $this->addSql('ALTER TABLE messages CHANGE id id INT AUTO_INCREMENT NOT NULL, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE messages ADD CONSTRAINT FK_DB021E96A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE messages ADD CONSTRAINT FK_DB021E961819BCFA FOREIGN KEY (chat_room_id) REFERENCES chat_rooms (id)');
        $this->addSql('CREATE INDEX IDX_DB021E96A76ED395 ON messages (user_id)');
        $this->addSql('CREATE INDEX IDX_DB021E961819BCFA ON messages (chat_room_id)');
        $this->addSql('ALTER TABLE post CHANGE id id INT AUTO_INCREMENT NOT NULL, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_5A8A6C8DA76ED395 ON post (user_id)');
        $this->addSql('ALTER TABLE reaction CHANGE id id INT AUTO_INCREMENT NOT NULL, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE reaction ADD CONSTRAINT FK_A4D707F74B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
        $this->addSql('ALTER TABLE reaction ADD CONSTRAINT FK_A4D707F7F8697D13 FOREIGN KEY (comment_id) REFERENCES comment (id)');
        $this->addSql('ALTER TABLE reaction ADD CONSTRAINT FK_A4D707F7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_A4D707F74B89032C ON reaction (post_id)');
        $this->addSql('CREATE INDEX IDX_A4D707F7F8697D13 ON reaction (comment_id)');
        $this->addSql('CREATE INDEX IDX_A4D707F7A76ED395 ON reaction (user_id)');
        $this->addSql('ALTER TABLE share CHANGE id id INT AUTO_INCREMENT NOT NULL, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE share ADD CONSTRAINT FK_EF069D5A4B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE share ADD CONSTRAINT FK_EF069D5A5919D5BC FOREIGN KEY (shared_from_id) REFERENCES share (id)');
        $this->addSql('ALTER TABLE share ADD CONSTRAINT FK_EF069D5AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_EF069D5A4B89032C ON share (post_id)');
        $this->addSql('CREATE INDEX IDX_EF069D5A5919D5BC ON share (shared_from_id)');
        $this->addSql('CREATE INDEX IDX_EF069D5AA76ED395 ON share (user_id)');
        $this->addSql('ALTER TABLE user ADD points INT NOT NULL, CHANGE is_active is_active TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE ville CHANGE id id INT AUTO_INCREMENT NOT NULL, ADD PRIMARY KEY (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chat_rooms DROP FOREIGN KEY FK_7DDCF70DFDA7B0BF');
        $this->addSql('ALTER TABLE chat_rooms ADD CONSTRAINT FK_7DDCF70DFDA7B0BF FOREIGN KEY (community_id) REFERENCES community (id)');
        $this->addSql('ALTER TABLE chat_room_membres MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE chat_room_membres DROP FOREIGN KEY FK_4B8336FDA76ED395');
        $this->addSql('ALTER TABLE chat_room_membres DROP FOREIGN KEY FK_4B8336FD1819BCFA');
        $this->addSql('DROP INDEX IDX_4B8336FDA76ED395 ON chat_room_membres');
        $this->addSql('DROP INDEX IDX_4B8336FD1819BCFA ON chat_room_membres');
        $this->addSql('DROP INDEX `primary` ON chat_room_membres');
        $this->addSql('ALTER TABLE chat_room_membres CHANGE id id INT NOT NULL');
        $this->addSql('ALTER TABLE comment MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C4B89032C');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CA76ED395');
        $this->addSql('DROP INDEX IDX_9474526C4B89032C ON comment');
        $this->addSql('DROP INDEX IDX_9474526CA76ED395 ON comment');
        $this->addSql('DROP INDEX `primary` ON comment');
        $this->addSql('ALTER TABLE comment CHANGE id id INT NOT NULL');
        $this->addSql('ALTER TABLE community DROP statut');
        $this->addSql('ALTER TABLE events DROP acces');
        $this->addSql('ALTER TABLE gamifications DROP FOREIGN KEY FK_DB1F936B2811BE9E');
        $this->addSql('ALTER TABLE gamifications ADD CONSTRAINT FK_DB1F936B2811BE9E FOREIGN KEY (type_abonnement) REFERENCES abonnements (id)');
        $this->addSql('ALTER TABLE inscription_abonnement MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE inscription_abonnement DROP FOREIGN KEY FK_26D8E52FA76ED395');
        $this->addSql('ALTER TABLE inscription_abonnement DROP FOREIGN KEY FK_26D8E52FF1D74413');
        $this->addSql('DROP INDEX UNIQ_26D8E52FA76ED395 ON inscription_abonnement');
        $this->addSql('DROP INDEX IDX_26D8E52FF1D74413 ON inscription_abonnement');
        $this->addSql('DROP INDEX `primary` ON inscription_abonnement');
        $this->addSql('ALTER TABLE inscription_abonnement CHANGE id id INT NOT NULL');
        $this->addSql('ALTER TABLE lieu_culturels MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE lieu_culturels DROP FOREIGN KEY FK_6AC9CE84A73F0036');
        $this->addSql('DROP INDEX IDX_6AC9CE84A73F0036 ON lieu_culturels');
        $this->addSql('DROP INDEX `primary` ON lieu_culturels');
        $this->addSql('ALTER TABLE lieu_culturels CHANGE id id INT NOT NULL');
        $this->addSql('ALTER TABLE media MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE media DROP FOREIGN KEY FK_6A2CA10CA2C806AC');
        $this->addSql('DROP INDEX IDX_6A2CA10CA2C806AC ON media');
        $this->addSql('DROP INDEX `primary` ON media');
        $this->addSql('ALTER TABLE media CHANGE id id INT NOT NULL');
        $this->addSql('ALTER TABLE membre_comunity DROP FOREIGN KEY FK_88F7DAC0FDA7B0BF');
        $this->addSql('DROP INDEX IDX_88F7DAC0FDA7B0BF ON membre_comunity');
        $this->addSql('ALTER TABLE membre_comunity CHANGE community_id id_comunity_id INT NOT NULL');
        $this->addSql('ALTER TABLE membre_comunity ADD CONSTRAINT FK_88F7DAC0A4C4F6C9 FOREIGN KEY (id_comunity_id) REFERENCES community (id)');
        $this->addSql('CREATE INDEX IDX_88F7DAC0A4C4F6C9 ON membre_comunity (id_comunity_id)');
        $this->addSql('ALTER TABLE messages MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE messages DROP FOREIGN KEY FK_DB021E96A76ED395');
        $this->addSql('ALTER TABLE messages DROP FOREIGN KEY FK_DB021E961819BCFA');
        $this->addSql('DROP INDEX IDX_DB021E96A76ED395 ON messages');
        $this->addSql('DROP INDEX IDX_DB021E961819BCFA ON messages');
        $this->addSql('DROP INDEX `primary` ON messages');
        $this->addSql('ALTER TABLE messages CHANGE id id INT NOT NULL');
        $this->addSql('ALTER TABLE post MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DA76ED395');
        $this->addSql('DROP INDEX IDX_5A8A6C8DA76ED395 ON post');
        $this->addSql('DROP INDEX `primary` ON post');
        $this->addSql('ALTER TABLE post CHANGE id id INT NOT NULL');
        $this->addSql('ALTER TABLE reaction MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE reaction DROP FOREIGN KEY FK_A4D707F74B89032C');
        $this->addSql('ALTER TABLE reaction DROP FOREIGN KEY FK_A4D707F7F8697D13');
        $this->addSql('ALTER TABLE reaction DROP FOREIGN KEY FK_A4D707F7A76ED395');
        $this->addSql('DROP INDEX IDX_A4D707F74B89032C ON reaction');
        $this->addSql('DROP INDEX IDX_A4D707F7F8697D13 ON reaction');
        $this->addSql('DROP INDEX IDX_A4D707F7A76ED395 ON reaction');
        $this->addSql('DROP INDEX `primary` ON reaction');
        $this->addSql('ALTER TABLE reaction CHANGE id id INT NOT NULL');
        $this->addSql('ALTER TABLE share MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE share DROP FOREIGN KEY FK_EF069D5A4B89032C');
        $this->addSql('ALTER TABLE share DROP FOREIGN KEY FK_EF069D5A5919D5BC');
        $this->addSql('ALTER TABLE share DROP FOREIGN KEY FK_EF069D5AA76ED395');
        $this->addSql('DROP INDEX IDX_EF069D5A4B89032C ON share');
        $this->addSql('DROP INDEX IDX_EF069D5A5919D5BC ON share');
        $this->addSql('DROP INDEX IDX_EF069D5AA76ED395 ON share');
        $this->addSql('DROP INDEX `primary` ON share');
        $this->addSql('ALTER TABLE share CHANGE id id INT NOT NULL');
        $this->addSql('ALTER TABLE user DROP points, CHANGE is_active is_active TINYINT(1) DEFAULT 1');
        $this->addSql('ALTER TABLE ville MODIFY id INT NOT NULL');
        $this->addSql('DROP INDEX `primary` ON ville');
        $this->addSql('ALTER TABLE ville CHANGE id id INT NOT NULL');
    }
}
