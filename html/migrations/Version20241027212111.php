<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241027212111 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("INSERT INTO volunteer (id, first_name, last_name) VALUES
            (1, 'Barry', 'Towne'),
            (2, 'Ethyl', 'Donnelly'),
            (3, 'Felipe', 'Breitenberg'),
            (4, 'Fred', 'Hettinger'),
            (5, 'Freeda', 'Koelpin'),
            (6, 'Guy', 'Considine'),
            (7, 'Gillian', 'Gerhold'),
            (8, 'Gloria', 'Williamson'),
            (9, 'Gordon', 'Kirlin'),
            (10, 'Isabell', 'Prosacco'),
            (11, 'Jacky', 'Renner'),
            (12, 'Kaitlyn', 'Tillman'),
            (13, 'Laverna', 'D\'Amore'),
            (14, 'Lonnie', 'Kreiger'),
            (15, 'Maeve', 'Hahn'),
            (16, 'Marcia', 'Corkery'),
            (17, 'Melyssa', 'Boehm'),
            (18, 'Muriel', 'Metz'),
            (19, 'Nat', 'Conn'),
            (20, 'Orin', 'Bahringer'),
            (21, 'Pascale', 'Hayes'),
            (22, 'Phoebe', 'Collier'),
            (23, 'Rowena', 'Walsh'),
            (24, 'Sabryna', 'Schowalter'),
            (25, 'Sahil', 'Pacocha'),
            (26, 'Sanford', 'Toy'),
            (27, 'Viola', 'Wisozk')
        ");
    
        $this->addSql("INSERT INTO job (id, name, description) VALUES
            (1, 'Front Desk', 'Manage the front desk, keep track of signed in guest and print entry badges. At http://contributetocommunity.org we organize events which would benefit the community. These range from organizing a yoga workshop, career fairs to food festivals always rely upon the willingness and generosity of our volunteers who want to give something back to the community. Please check the events calendar to view the job postings and do apply for one or more openings as per your availability!'),
            (2, 'Stage Organizer', 'Organize and arrange the props on the performance stage during the event. At http://contributetocommunity.org we organize events which would benefit the community. These range from organizing a yoga workshop, career fairs to food festivals always rely upon the willingness and generosity of our volunteers who want to give something back to the community. Please check the events calendar to view the job postings and do apply for one or more openings as per your availability!'),
            (3, 'Guest Coordinator', 'Provide information to the guests about events, locations & details. At http://contributetocommunity.org we organize events which would benefit the community. These range from organizing a yoga workshop, career fairs to food festivals always rely upon the willingness and generosity of our volunteers who want to give something back to the community. Please check the events calendar to view the job postings and do apply for one or more openings as per your availability!'),
            (4, 'Food Services', 'Handle food logistics, preparation & serving. At http://contributetocommunity.org we organize events which would benefit the community. These range from organizing a yoga workshop, career fairs to food festivals always rely upon the willingness and generosity of our volunteers who want to give something back to the community. Please check the events calendar to view the job postings and do apply for one or more openings as per your availability!'),
            (5, 'Stationary Services', 'Manage stationary supplies. At http://contributetocommunity.org we organize events which would benefit the community. These range from organizing a yoga workshop, career fairs to food festivals always rely upon the willingness and generosity of our volunteers who want to give something back to the community. Please check the events calendar to view the job postings and do apply for one or more openings as per your availability!'),
            (6, 'Security Personnel', 'Manage security outside of each event space by controlling entry exits. At http://contributetocommunity.org we organize events which would benefit the community. These range from organizing a yoga workshop, career fairs to food festivals always rely upon the willingness and generosity of our volunteers who want to give something back to the community. Please check the events calendar to view the job postings and do apply for one or more openings as per your availability!'),
            (7, 'General Supervisor', 'Act as a supervisor to the team of volunteers. We prefer to have past history working for us in order to qualify as a supervisor. At http://contributetocommunity.org we organize events which would benefit the community. These range from organizing a yoga workshop, career fairs to food festivals always rely upon the willingness and generosity of our volunteers who want to give something back to the community. Please check the events calendar to view the job postings and do apply for one or more openings as per your availability!')
        ");

        $this->addSql("INSERT INTO volunteers_jobs (job_id, volunteer_id) VALUES
            (1, 6),(1, 10),(1, 12),(1, 13),(1, 16),(1, 19),(1, 20),(1, 23),(1, 24),(2, 1),(2, 7),(2, 15),(2, 17),(2, 18),(2, 22),(2, 25),(3, 2),(3, 5),(3, 8),(3, 9),(3, 10),(3, 11),(3, 13),(3, 14),(3, 18),(3, 19),(3, 20),(3, 21),(3, 22),(3, 24),(3, 27),(4, 2),(4, 3),(4, 4),(4, 6),(4, 11),(4, 16),(4, 20),(4, 21),(4, 26),(5, 3),(5, 5),(5, 14),(5, 24),(6, 5),(6, 9),(6, 11),(6, 17),(6, 21),(6, 22),(6, 25),(7, 1),(7, 3),(7, 4),(7, 7),(7, 9),(7, 10),(7, 12),(7, 16),(7, 17),(7, 23),(7, 25),(7, 26),(7, 27)
        ");
    }
    
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
