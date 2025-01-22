<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250122185918 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE rsdaily (id INT AUTO_INCREMENT NOT NULL, story_id INT NOT NULL, date DATE NOT NULL, genre VARCHAR(255) NOT NULL, highest_position INT NOT NULL, INDEX IDX_23923D64AA5D4036 (story_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE rsdaily ADD CONSTRAINT FK_23923D64AA5D4036 FOREIGN KEY (story_id) REFERENCES story (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rsdaily DROP FOREIGN KEY FK_23923D64AA5D4036');
        $this->addSql('DROP TABLE rsdaily');
    }
}
