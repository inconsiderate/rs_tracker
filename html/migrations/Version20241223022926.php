<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241223022926 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE rsmatch (id INT AUTO_INCREMENT NOT NULL, story_id_id INT NOT NULL, date DATETIME NOT NULL, active TINYINT(1) NOT NULL, highest_position INT DEFAULT NULL, INDEX IDX_D754530BA3043EF4 (story_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE story (id INT AUTO_INCREMENT NOT NULL, story_name VARCHAR(255) NOT NULL, tracked_genres LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', story_address VARCHAR(255) NOT NULL, story_id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE story_user (story_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_1B3EBA67AA5D4036 (story_id), INDEX IDX_1B3EBA67A76ED395 (user_id), PRIMARY KEY(story_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, is_verified TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE rsmatch ADD CONSTRAINT FK_D754530BA3043EF4 FOREIGN KEY (story_id_id) REFERENCES story (id)');
        $this->addSql('ALTER TABLE story_user ADD CONSTRAINT FK_1B3EBA67AA5D4036 FOREIGN KEY (story_id) REFERENCES story (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE story_user ADD CONSTRAINT FK_1B3EBA67A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rsmatch DROP FOREIGN KEY FK_D754530BA3043EF4');
        $this->addSql('ALTER TABLE story_user DROP FOREIGN KEY FK_1B3EBA67AA5D4036');
        $this->addSql('ALTER TABLE story_user DROP FOREIGN KEY FK_1B3EBA67A76ED395');
        $this->addSql('DROP TABLE rsmatch');
        $this->addSql('DROP TABLE story');
        $this->addSql('DROP TABLE story_user');
        $this->addSql('DROP TABLE user');
    }
}
