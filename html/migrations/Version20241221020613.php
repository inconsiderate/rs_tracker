<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241221020613 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE rsmatch (id INT AUTO_INCREMENT NOT NULL, user_id_id INT NOT NULL, tracker_id_id INT NOT NULL, list_position SMALLINT NOT NULL, date DATETIME NOT NULL, INDEX IDX_D754530B9D86650F (user_id_id), INDEX IDX_D754530B6FB61ABC (tracker_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE story_tracker (id INT AUTO_INCREMENT NOT NULL, user_stories_id INT NOT NULL, story_name VARCHAR(255) NOT NULL, tracked_genres LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', story_address VARCHAR(255) NOT NULL, INDEX IDX_FF9FBA2D1843C432 (user_stories_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, is_verified TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE rsmatch ADD CONSTRAINT FK_D754530B9D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE rsmatch ADD CONSTRAINT FK_D754530B6FB61ABC FOREIGN KEY (tracker_id_id) REFERENCES story_tracker (id)');
        $this->addSql('ALTER TABLE story_tracker ADD CONSTRAINT FK_FF9FBA2D1843C432 FOREIGN KEY (user_stories_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rsmatch DROP FOREIGN KEY FK_D754530B9D86650F');
        $this->addSql('ALTER TABLE rsmatch DROP FOREIGN KEY FK_D754530B6FB61ABC');
        $this->addSql('ALTER TABLE story_tracker DROP FOREIGN KEY FK_FF9FBA2D1843C432');
        $this->addSql('DROP TABLE rsmatch');
        $this->addSql('DROP TABLE story_tracker');
        $this->addSql('DROP TABLE user');
    }
}
