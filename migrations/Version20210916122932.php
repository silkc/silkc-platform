<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210916122932 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE occupation_translation (id INT AUTO_INCREMENT NOT NULL, occupation_id INT NOT NULL, locale VARCHAR(10) NOT NULL, preferred_label LONGTEXT NOT NULL, alt_labels LONGTEXT DEFAULT NULL, hidden_labels LONGTEXT DEFAULT NULL, definition LONGTEXT NOT NULL, description LONGTEXT DEFAULT NULL, INDEX IDX_55D11AED22C8FC20 (occupation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE skill_translation (id INT AUTO_INCREMENT NOT NULL, skill_id INT NOT NULL, locale VARCHAR(10) NOT NULL, preferred_label LONGTEXT DEFAULT NULL, alt_labels LONGTEXT DEFAULT NULL, hidden_labels LONGTEXT DEFAULT NULL, definition LONGTEXT DEFAULT NULL, description LONGTEXT DEFAULT NULL, INDEX IDX_A77DCA35585C142 (skill_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE occupation_translation ADD CONSTRAINT FK_55D11AED22C8FC20 FOREIGN KEY (occupation_id) REFERENCES occupation (id)');
        $this->addSql('ALTER TABLE skill_translation ADD CONSTRAINT FK_A77DCA35585C142 FOREIGN KEY (skill_id) REFERENCES skill (id)');
        $this->addSql('ALTER TABLE notification CHANGE is_read is_read TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE position CHANGE currency currency ENUM(\'euro\', \'złoty\'), CHANGE is_validated is_validated TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_rejected is_rejected TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_visible is_visible TINYINT(1) DEFAULT \'1\' NOT NULL');
        $this->addSql('ALTER TABLE training CHANGE has_sessions has_sessions TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_online is_online TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_online_monitored is_online_monitored TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_presential is_presential TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_validated is_validated TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_rejected is_rejected TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE currency currency ENUM(\'euro\', \'złoty\'), CHANGE duration_unity duration_unity ENUM(\'hours\', \'days\', \'weeks\', \'months\')');
        $this->addSql('ALTER TABLE training_session_skill CHANGE is_required is_required TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_to_acquire is_to_acquire TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE training_skill CHANGE is_required is_required TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_to_acquire is_to_acquire TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE updated_at updated_at DATETIME on update CURRENT_TIMESTAMP, CHANGE is_validated is_validated TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_suspended is_suspended TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_suspected is_suspected TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_searches_kept is_searches_kept TINYINT(1) DEFAULT \'1\' NOT NULL, CHANGE is_listening_position is_listening_position TINYINT(1) DEFAULT \'1\' NOT NULL');
        $this->addSql('ALTER TABLE user_occupation CHANGE is_current is_current TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_previous is_previous TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_desired is_desired TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE user_search CHANGE is_active is_active TINYINT(1) DEFAULT \'1\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE occupation_translation');
        $this->addSql('DROP TABLE skill_translation');
        $this->addSql('ALTER TABLE notification CHANGE is_read is_read TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE position CHANGE currency currency VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE is_visible is_visible TINYINT(1) DEFAULT \'1\' NOT NULL, CHANGE is_validated is_validated TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_rejected is_rejected TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE training CHANGE duration_unity duration_unity VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE currency currency VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE has_sessions has_sessions TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_online is_online TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_online_monitored is_online_monitored TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_presential is_presential TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_validated is_validated TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_rejected is_rejected TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE training_session_skill CHANGE is_required is_required TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_to_acquire is_to_acquire TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE training_skill CHANGE is_required is_required TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_to_acquire is_to_acquire TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE updated_at updated_at DATETIME DEFAULT NULL, CHANGE is_validated is_validated TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_suspended is_suspended TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_suspected is_suspected TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_searches_kept is_searches_kept TINYINT(1) DEFAULT \'1\' NOT NULL, CHANGE is_listening_position is_listening_position TINYINT(1) DEFAULT \'1\' NOT NULL');
        $this->addSql('ALTER TABLE user_occupation CHANGE is_current is_current TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_previous is_previous TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_desired is_desired TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE user_search CHANGE is_active is_active TINYINT(1) DEFAULT \'1\' NOT NULL');
    }
}
