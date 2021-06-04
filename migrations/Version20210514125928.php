<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210514125928 extends AbstractMigration
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
        //$this->addSql('ALTER TABLE user_training ADD CONSTRAINT FK_359F6E8FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        //$this->addSql('ALTER TABLE user_training ADD CONSTRAINT FK_359F6E8FBEFD98D1 FOREIGN KEY (training_id) REFERENCES training (id) ON DELETE CASCADE');
        $table = $schema->getTable('training');
        if ($table->hasColumn('has_sessions')) {
            $this->addSql('ALTER TABLE training CHANGE has_sessions has_sessions TINYINT(1) DEFAULT \'0\' NOT NULL');
        }
        if ($table->hasColumn('is_online')) {
            $this->addSql('ALTER TABLE training CHANGE is_online is_online TINYINT(1) DEFAULT \'0\' NOT NULL');
        }
        if ($table->hasColumn('is_online_monitored')) {
            $this->addSql('ALTER TABLE training CHANGE is_online_monitored is_online_monitored TINYINT(1) DEFAULT \'0\' NOT NULL');
        }
        if ($table->hasColumn('is_presential')) {
            $this->addSql('ALTER TABLE training CHANGE is_presential is_presential TINYINT(1) DEFAULT \'0\' NOT NULL');
        }
        $this->addSql('ALTER TABLE training_session_skill CHANGE is_required is_required TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_to_acquire is_to_acquire TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE training_skill CHANGE is_required is_required TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_to_acquire is_to_acquire TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE updated_at updated_at DATETIME on update CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE user_occupation CHANGE is_current is_current TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_previous is_previous TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_desired is_desired TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE user_training');
        $this->addSql('ALTER TABLE training CHANGE has_sessions has_sessions TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_online is_online TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_online_monitored is_online_monitored TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_presential is_presential TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE training_session_skill CHANGE is_required is_required TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_to_acquire is_to_acquire TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE training_skill CHANGE is_required is_required TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE is_to_acquire is_to_acquire TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE user_occupation CHANGE is_current is_current TINYINT(1) NOT NULL, CHANGE is_previous is_previous TINYINT(1) NOT NULL, CHANGE is_desired is_desired TINYINT(1) NOT NULL');
    }
}
