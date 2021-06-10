<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210610124740 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, is_read TINYINT(1) DEFAULT \'0\' NOT NULL, created_at DATETIME DEFAULT NULL, read_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE training_feedback (id INT AUTO_INCREMENT NOT NULL, training_id INT NOT NULL, user_id INT UNSIGNED NOT NULL, mark INT UNSIGNED DEFAULT 0 NOT NULL, comment LONGTEXT DEFAULT NULL, created_at DATETIME DEFAULT NULL, INDEX IDX_354C57CFBEFD98D1 (training_id), INDEX IDX_354C57CFA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_activity (id INT AUTO_INCREMENT NOT NULL, training_id INT NOT NULL, user_id INT UNSIGNED NOT NULL, score INT UNSIGNED DEFAULT 0 NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_4CF9ED5ABEFD98D1 (training_id), INDEX IDX_4CF9ED5AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE training_feedback ADD CONSTRAINT FK_354C57CFBEFD98D1 FOREIGN KEY (training_id) REFERENCES training (id)');
        $this->addSql('ALTER TABLE training_feedback ADD CONSTRAINT FK_354C57CFA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_activity ADD CONSTRAINT FK_4CF9ED5ABEFD98D1 FOREIGN KEY (training_id) REFERENCES training (id)');
        $this->addSql('ALTER TABLE user_activity ADD CONSTRAINT FK_4CF9ED5AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE training ADD is_validated TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE has_sessions has_sessions TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_online is_online TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_online_monitored is_online_monitored TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_presential is_presential TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE training_session_skill CHANGE is_required is_required TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_to_acquire is_to_acquire TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE training_skill CHANGE is_required is_required TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_to_acquire is_to_acquire TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE user ADD date_of_birth DATETIME DEFAULT NULL, DROP year_of_birth, CHANGE updated_at updated_at DATETIME on update CURRENT_TIMESTAMP, CHANGE is_validated is_validated TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE user_occupation CHANGE is_current is_current TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_previous is_previous TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_desired is_desired TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE training_feedback');
        $this->addSql('DROP TABLE user_activity');
        $this->addSql('ALTER TABLE training DROP is_validated, CHANGE has_sessions has_sessions TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_online is_online TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_online_monitored is_online_monitored TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_presential is_presential TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE training_session_skill CHANGE is_required is_required TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_to_acquire is_to_acquire TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE training_skill CHANGE is_required is_required TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_to_acquire is_to_acquire TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE user ADD year_of_birth VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, DROP date_of_birth, CHANGE updated_at updated_at DATETIME DEFAULT NULL, CHANGE is_validated is_validated TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE user_occupation CHANGE is_current is_current TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_previous is_previous TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_desired is_desired TINYINT(1) DEFAULT \'0\' NOT NULL');
    }
}
