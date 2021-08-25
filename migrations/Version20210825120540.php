<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210825120540 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE position (id INT AUTO_INCREMENT NOT NULL, user_id INT UNSIGNED DEFAULT NULL, creator_id INT UNSIGNED DEFAULT NULL, occupation_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, location VARCHAR(255) DEFAULT NULL, longitude VARCHAR(255) DEFAULT NULL, latitude VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, salary DOUBLE PRECISION DEFAULT NULL, currency ENUM(\'euro\', \'złoty\'), created_at DATETIME DEFAULT NULL, start_at DATETIME DEFAULT NULL, end_at DATETIME DEFAULT NULL, score INT UNSIGNED DEFAULT 0 NOT NULL COMMENT \'Champs dynamique pour calcul de pondération lors d\'\'une recherche de formation\', max_score INT UNSIGNED DEFAULT 0 NOT NULL COMMENT \'Champs dynamique pour calcul de pondération lors d\'\'une recherche de formation\', completion INT UNSIGNED DEFAULT 0 NOT NULL, is_validated TINYINT(1) DEFAULT \'0\' NOT NULL, validated_at DATETIME DEFAULT NULL, is_rejected TINYINT(1) DEFAULT \'0\' NOT NULL, rejected_at DATETIME DEFAULT NULL, distance INT UNSIGNED DEFAULT 0 NOT NULL COMMENT \'Champs dynamique pour calcul de pondération lors d\'\'une recherche de formation\', INDEX IDX_462CE4F5A76ED395 (user_id), INDEX IDX_462CE4F561220EA6 (creator_id), INDEX IDX_462CE4F522C8FC20 (occupation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE position_skill (position_id INT NOT NULL, skill_id INT NOT NULL, INDEX IDX_D2FD00AADD842E46 (position_id), INDEX IDX_D2FD00AA5585C142 (skill_id), PRIMARY KEY(position_id, skill_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE position ADD CONSTRAINT FK_462CE4F5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE position ADD CONSTRAINT FK_462CE4F561220EA6 FOREIGN KEY (creator_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE position ADD CONSTRAINT FK_462CE4F522C8FC20 FOREIGN KEY (occupation_id) REFERENCES occupation (id)');
        $this->addSql('ALTER TABLE position_skill ADD CONSTRAINT FK_D2FD00AADD842E46 FOREIGN KEY (position_id) REFERENCES position (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE position_skill ADD CONSTRAINT FK_D2FD00AA5585C142 FOREIGN KEY (skill_id) REFERENCES skill (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notification CHANGE is_read is_read TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE training CHANGE has_sessions has_sessions TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_online is_online TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_online_monitored is_online_monitored TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_presential is_presential TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_validated is_validated TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_rejected is_rejected TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE currency currency ENUM(\'euro\', \'złoty\'), CHANGE duration_unity duration_unity ENUM(\'hours\', \'days\', \'weeks\', \'months\')');
        $this->addSql('ALTER TABLE training_session_skill CHANGE is_required is_required TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_to_acquire is_to_acquire TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE training_skill CHANGE is_required is_required TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_to_acquire is_to_acquire TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE updated_at updated_at DATETIME on update CURRENT_TIMESTAMP, CHANGE is_validated is_validated TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_suspended is_suspended TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_suspected is_suspected TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_searches_kept is_searches_kept TINYINT(1) DEFAULT \'1\' NOT NULL');
        $this->addSql('ALTER TABLE user_occupation CHANGE is_current is_current TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_previous is_previous TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_desired is_desired TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE user_search CHANGE is_active is_active TINYINT(1) DEFAULT \'1\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE position_skill DROP FOREIGN KEY FK_D2FD00AADD842E46');
        $this->addSql('DROP TABLE position');
        $this->addSql('DROP TABLE position_skill');
        $this->addSql('ALTER TABLE notification CHANGE is_read is_read TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE training CHANGE duration_unity duration_unity VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE currency currency VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE has_sessions has_sessions TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_online is_online TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_online_monitored is_online_monitored TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_presential is_presential TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_validated is_validated TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_rejected is_rejected TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE training_session_skill CHANGE is_required is_required TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_to_acquire is_to_acquire TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE training_skill CHANGE is_required is_required TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_to_acquire is_to_acquire TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE updated_at updated_at DATETIME DEFAULT NULL, CHANGE is_validated is_validated TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_suspended is_suspended TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_suspected is_suspected TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_searches_kept is_searches_kept TINYINT(1) DEFAULT \'1\' NOT NULL');
        $this->addSql('ALTER TABLE user_occupation CHANGE is_current is_current TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_previous is_previous TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_desired is_desired TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE user_search CHANGE is_active is_active TINYINT(1) DEFAULT \'1\' NOT NULL');
    }
}
