<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250225230338 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rsmatch ADD start_follower_count INT DEFAULT NULL, ADD end_follower_count INT DEFAULT NULL, ADD start_page_count INT DEFAULT NULL, ADD end_page_count INT DEFAULT NULL, ADD start_view_count INT DEFAULT NULL, ADD end_view_count INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rsmatch DROP start_follower_count, DROP end_follower_count, DROP start_page_count, DROP end_page_count, DROP start_view_count, DROP end_view_count');
    }
}
