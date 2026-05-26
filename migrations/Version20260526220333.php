<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260526220333 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE club (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) NOT NULL, description VARCHAR(120) DEFAULT NULL, category VARCHAR(100) DEFAULT NULL, cover_img VARCHAR(255) DEFAULT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_B8EE3872A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `event` (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(200) NOT NULL, description VARCHAR(500) DEFAULT NULL, event_date DATETIME NOT NULL, place VARCHAR(200) DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL, club_id INT NOT NULL, INDEX IDX_3BAE0AA761190A32 (club_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE follow (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME DEFAULT NULL, student_id INT NOT NULL, club_id INT NOT NULL, INDEX IDX_68344470CB944F1A (student_id), INDEX IDX_6834447061190A32 (club_id), UNIQUE INDEX student_club_unique (student_id, club_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `like` (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME DEFAULT NULL, student_id INT NOT NULL, event_id INT NOT NULL, INDEX IDX_AC6340B3CB944F1A (student_id), INDEX IDX_AC6340B371F7E88B (event_id), UNIQUE INDEX student_event_unique (student_id, event_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE student (id INT AUTO_INCREMENT NOT NULL, fullname VARCHAR(100) NOT NULL, major VARCHAR(100) DEFAULT NULL, birthday DATETIME DEFAULT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_B723AF33A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, username VARCHAR(50) NOT NULL, password VARCHAR(255) NOT NULL, role VARCHAR(30) NOT NULL, profile_img VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE club ADD CONSTRAINT FK_B8EE3872A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE `event` ADD CONSTRAINT FK_3BAE0AA761190A32 FOREIGN KEY (club_id) REFERENCES club (id)');
        $this->addSql('ALTER TABLE follow ADD CONSTRAINT FK_68344470CB944F1A FOREIGN KEY (student_id) REFERENCES student (id)');
        $this->addSql('ALTER TABLE follow ADD CONSTRAINT FK_6834447061190A32 FOREIGN KEY (club_id) REFERENCES club (id)');
        $this->addSql('ALTER TABLE `like` ADD CONSTRAINT FK_AC6340B3CB944F1A FOREIGN KEY (student_id) REFERENCES student (id)');
        $this->addSql('ALTER TABLE `like` ADD CONSTRAINT FK_AC6340B371F7E88B FOREIGN KEY (event_id) REFERENCES `event` (id)');
        $this->addSql('ALTER TABLE student ADD CONSTRAINT FK_B723AF33A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE club DROP FOREIGN KEY FK_B8EE3872A76ED395');
        $this->addSql('ALTER TABLE `event` DROP FOREIGN KEY FK_3BAE0AA761190A32');
        $this->addSql('ALTER TABLE follow DROP FOREIGN KEY FK_68344470CB944F1A');
        $this->addSql('ALTER TABLE follow DROP FOREIGN KEY FK_6834447061190A32');
        $this->addSql('ALTER TABLE `like` DROP FOREIGN KEY FK_AC6340B3CB944F1A');
        $this->addSql('ALTER TABLE `like` DROP FOREIGN KEY FK_AC6340B371F7E88B');
        $this->addSql('ALTER TABLE student DROP FOREIGN KEY FK_B723AF33A76ED395');
        $this->addSql('DROP TABLE club');
        $this->addSql('DROP TABLE `event`');
        $this->addSql('DROP TABLE follow');
        $this->addSql('DROP TABLE `like`');
        $this->addSql('DROP TABLE student');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
