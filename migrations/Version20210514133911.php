<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210514133911 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        if (!$schema->hasTable('user_training')) {
            $this->addSql('CREATE TABLE user_training (user_id INT UNSIGNED NOT NULL, training_id INT NOT NULL, INDEX IDX_359F6E8FA76ED395 (user_id), INDEX IDX_359F6E8FBEFD98D1 (training_id), PRIMARY KEY(user_id, training_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }
        if (!$schema->hasTable('user_occupation')) {
            $this->addSql('CREATE TABLE user_occupation (id INT AUTO_INCREMENT NOT NULL, user_id INT UNSIGNED NOT NULL, occupation_id INT NOT NULL, is_current TINYINT(1) DEFAULT \'0\' NOT NULL, is_previous TINYINT(1) DEFAULT \'0\' NOT NULL, is_desired TINYINT(1) DEFAULT \'0\' NOT NULL, INDEX IDX_A59FBBBDA76ED395 (user_id), INDEX IDX_A59FBBBD22C8FC20 (occupation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }
        if (!$schema->hasTable('user_skill')) {
            $this->addSql('CREATE TABLE user_skill (id INT AUTO_INCREMENT NOT NULL, user_id INT UNSIGNED NOT NULL, skill_id INT NOT NULL, is_selected TINYINT(1) NOT NULL, INDEX IDX_BCFF1F2FA76ED395 (user_id), INDEX IDX_BCFF1F2F5585C142 (skill_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }
        $this->addSql('ALTER TABLE training_session_skill CHANGE is_required is_required TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_to_acquire is_to_acquire TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE training_skill CHANGE is_required is_required TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_to_acquire is_to_acquire TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE updated_at updated_at DATETIME on update CURRENT_TIMESTAMP');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE user_training');
        $this->addSql('DROP TABLE user_occupation');
        $this->addSql('DROP TABLE user_skill');
        $this->addSql('ALTER TABLE training CHANGE has_sessions has_sessions TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_online is_online TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_online_monitored is_online_monitored TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_presential is_presential TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE training_session_skill CHANGE is_required is_required TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_to_acquire is_to_acquire TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE training_skill CHANGE is_required is_required TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_to_acquire is_to_acquire TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE updated_at updated_at DATETIME DEFAULT NULL');
    }
}
