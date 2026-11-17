<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260514060802 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE absence (id INT AUTO_INCREMENT NOT NULL, date_absence DATE NOT NULL, reason LONGTEXT DEFAULT NULL, status VARCHAR(40) NOT NULL, student_id INT NOT NULL, course_id INT NOT NULL, INDEX IDX_765AE0C9CB944F1A (student_id), INDEX IDX_765AE0C9591CC992 (course_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE activity_log (id INT AUTO_INCREMENT NOT NULL, action VARCHAR(120) NOT NULL, context JSON NOT NULL, ip VARCHAR(45) DEFAULT NULL, created_at DATETIME NOT NULL, user_id INT DEFAULT NULL, INDEX IDX_FD06F647A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE conversation (id INT AUTO_INCREMENT NOT NULL, question LONGTEXT NOT NULL, answer LONGTEXT NOT NULL, provider VARCHAR(40) NOT NULL, created_at DATETIME NOT NULL, student_id INT NOT NULL, INDEX IDX_8A8E26E9CB944F1A (student_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE course (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(180) NOT NULL, description LONGTEXT NOT NULL, video VARCHAR(255) DEFAULT NULL, pdf VARCHAR(255) DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, level VARCHAR(60) NOT NULL, category VARCHAR(120) DEFAULT NULL, created_at DATETIME NOT NULL, teacher_id INT NOT NULL, INDEX IDX_169E6FB941807E1D (teacher_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE enrollment (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(40) NOT NULL, progress INT NOT NULL, created_at DATETIME NOT NULL, student_id INT NOT NULL, course_id INT NOT NULL, INDEX IDX_DBDCD7E1CB944F1A (student_id), INDEX IDX_DBDCD7E1591CC992 (course_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE exam (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(180) NOT NULL, duration INT NOT NULL, passing_score DOUBLE PRECISION NOT NULL, course_id INT NOT NULL, INDEX IDX_38BBA6C6591CC992 (course_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, file VARCHAR(255) DEFAULT NULL, room VARCHAR(120) DEFAULT NULL, created_at DATETIME NOT NULL, sender_id INT NOT NULL, receiver_id INT DEFAULT NULL, INDEX IDX_B6BD307FF624B39D (sender_id), INDEX IDX_B6BD307FCD53EDB6 (receiver_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(180) NOT NULL, message LONGTEXT NOT NULL, is_read TINYINT NOT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_BF5476CAA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE result (id INT AUTO_INCREMENT NOT NULL, score DOUBLE PRECISION NOT NULL, status VARCHAR(40) NOT NULL, student_id INT NOT NULL, exam_id INT NOT NULL, INDEX IDX_136AC113CB944F1A (student_id), INDEX IDX_136AC113578D5E91 (exam_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(120) NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, image VARCHAR(255) DEFAULT NULL, dark_mode TINYINT NOT NULL, is_verified TINYINT NOT NULL, is_blocked TINYINT NOT NULL, two_factor_secret VARCHAR(32) DEFAULT NULL, last_login DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE absence ADD CONSTRAINT FK_765AE0C9CB944F1A FOREIGN KEY (student_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE absence ADD CONSTRAINT FK_765AE0C9591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE activity_log ADD CONSTRAINT FK_FD06F647A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E9CB944F1A FOREIGN KEY (student_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB941807E1D FOREIGN KEY (teacher_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE enrollment ADD CONSTRAINT FK_DBDCD7E1CB944F1A FOREIGN KEY (student_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE enrollment ADD CONSTRAINT FK_DBDCD7E1591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE exam ADD CONSTRAINT FK_38BBA6C6591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FF624B39D FOREIGN KEY (sender_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FCD53EDB6 FOREIGN KEY (receiver_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE result ADD CONSTRAINT FK_136AC113CB944F1A FOREIGN KEY (student_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE result ADD CONSTRAINT FK_136AC113578D5E91 FOREIGN KEY (exam_id) REFERENCES exam (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE absence DROP FOREIGN KEY FK_765AE0C9CB944F1A');
        $this->addSql('ALTER TABLE absence DROP FOREIGN KEY FK_765AE0C9591CC992');
        $this->addSql('ALTER TABLE activity_log DROP FOREIGN KEY FK_FD06F647A76ED395');
        $this->addSql('ALTER TABLE conversation DROP FOREIGN KEY FK_8A8E26E9CB944F1A');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB941807E1D');
        $this->addSql('ALTER TABLE enrollment DROP FOREIGN KEY FK_DBDCD7E1CB944F1A');
        $this->addSql('ALTER TABLE enrollment DROP FOREIGN KEY FK_DBDCD7E1591CC992');
        $this->addSql('ALTER TABLE exam DROP FOREIGN KEY FK_38BBA6C6591CC992');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FF624B39D');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FCD53EDB6');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA76ED395');
        $this->addSql('ALTER TABLE result DROP FOREIGN KEY FK_136AC113CB944F1A');
        $this->addSql('ALTER TABLE result DROP FOREIGN KEY FK_136AC113578D5E91');
        $this->addSql('DROP TABLE absence');
        $this->addSql('DROP TABLE activity_log');
        $this->addSql('DROP TABLE conversation');
        $this->addSql('DROP TABLE course');
        $this->addSql('DROP TABLE enrollment');
        $this->addSql('DROP TABLE exam');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE result');
        $this->addSql('DROP TABLE `user`');
    }
}
