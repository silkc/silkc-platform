<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220407080701 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE message ADD created_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE notification CHANGE is_read is_read TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE position CHANGE currency currency ENUM(\'euro\', \'złoty\'), CHANGE is_validated is_validated TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_rejected is_rejected TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_visible is_visible TINYINT(1) DEFAULT \'1\' NOT NULL, CHANGE is_sent_to_affected_users is_sent_to_affected_users TINYINT(1) DEFAULT \'0\' NOT NULL COMMENT \'Est-ce que le recruteur a déjà envoyé un email à tous les utilisateurs concernés par ce poste\', CHANGE is_fulfilled is_fulfilled TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE training CHANGE has_sessions has_sessions TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_online is_online TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_online_monitored is_online_monitored TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_presential is_presential TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_validated is_validated TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_rejected is_rejected TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE currency currency ENUM(\'euro\', \'złoty\'), CHANGE duration_unity duration_unity ENUM(\'hours\', \'days\', \'weeks\', \'months\'), CHANGE language language ENUM(\'en\', \'fr\', \'it\', \'pl\'), CHANGE is_free is_free TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_certified is_certified TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE training_session_skill CHANGE is_required is_required TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_to_acquire is_to_acquire TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE training_skill CHANGE is_required is_required TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_to_acquire is_to_acquire TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE updated_at updated_at DATETIME on update CURRENT_TIMESTAMP, CHANGE is_validated is_validated TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_suspended is_suspended TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_suspected is_suspected TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_searches_kept is_searches_kept TINYINT(1) DEFAULT \'1\' NOT NULL, CHANGE is_listening_position is_listening_position TINYINT(1) DEFAULT \'1\' NOT NULL');
        $this->addSql('ALTER TABLE user_occupation CHANGE is_current is_current TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_previous is_previous TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_desired is_desired TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE user_search CHANGE is_active is_active TINYINT(1) DEFAULT \'1\' NOT NULL');
        $this->addSql('ALTER TABLE user_training CHANGE is_followed is_followed TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_interesting_for_me is_interesting_for_me TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_uninteresting_to_me is_uninteresting_to_me TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE message DROP created_at');
        $this->addSql('ALTER TABLE notification CHANGE is_read is_read TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE position CHANGE currency currency VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE is_visible is_visible TINYINT(1) DEFAULT \'1\' NOT NULL, CHANGE is_validated is_validated TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_rejected is_rejected TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_sent_to_affected_users is_sent_to_affected_users TINYINT(1) DEFAULT \'0\' NOT NULL COMMENT \'Est-ce que le recruteur a déjà envoyé un email à tous les utilisateurs concernés par ce poste\', CHANGE is_fulfilled is_fulfilled TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE training CHANGE duration_unity duration_unity VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE currency currency VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE language language VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE has_sessions has_sessions TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_online is_online TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_online_monitored is_online_monitored TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_presential is_presential TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_validated is_validated TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_rejected is_rejected TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_free is_free TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_certified is_certified TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE training_session_skill CHANGE is_required is_required TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_to_acquire is_to_acquire TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE training_skill CHANGE is_required is_required TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_to_acquire is_to_acquire TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE updated_at updated_at DATETIME DEFAULT NULL, CHANGE is_validated is_validated TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_suspended is_suspended TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_suspected is_suspected TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_searches_kept is_searches_kept TINYINT(1) DEFAULT \'1\' NOT NULL, CHANGE is_listening_position is_listening_position TINYINT(1) DEFAULT \'1\' NOT NULL');
        $this->addSql('ALTER TABLE user_occupation CHANGE is_current is_current TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_previous is_previous TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_desired is_desired TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE user_search CHANGE is_active is_active TINYINT(1) DEFAULT \'1\' NOT NULL');
        $this->addSql('ALTER TABLE user_training CHANGE is_followed is_followed TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_interesting_for_me is_interesting_for_me TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_uninteresting_to_me is_uninteresting_to_me TINYINT(1) DEFAULT \'0\' NOT NULL');
    }
}
