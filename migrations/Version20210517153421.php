<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210517153421 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE isco_group (id INT AUTO_INCREMENT NOT NULL, concept_type VARCHAR(255) NOT NULL, concept_uri VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, preferred_label LONGTEXT NOT NULL, alt_labels LONGTEXT DEFAULT NULL, in_scheme VARCHAR(255) DEFAULT NULL, description LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE occupation (id INT AUTO_INCREMENT NOT NULL, isco_group_id INT DEFAULT NULL, concept_type VARCHAR(255) NOT NULL, concept_uri VARCHAR(255) NOT NULL, preferred_label LONGTEXT NOT NULL, alt_labels LONGTEXT NOT NULL, hidden_labels LONGTEXT DEFAULT NULL, status VARCHAR(255) NOT NULL, modified_at DATETIME DEFAULT NULL, regulated_profession_note VARCHAR(255) NOT NULL, scope_note VARCHAR(255) DEFAULT NULL, definition LONGTEXT DEFAULT NULL, in_scheme LONGTEXT NOT NULL, description LONGTEXT DEFAULT NULL, code VARCHAR(255) NOT NULL, INDEX IDX_2F87D51A81E27D (isco_group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE occupation_skill (id INT AUTO_INCREMENT NOT NULL, occupation_id INT NOT NULL, skill_id INT NOT NULL, relation_type VARCHAR(255) NOT NULL, skill_type VARCHAR(255) NOT NULL, INDEX IDX_52C31DE122C8FC20 (occupation_id), INDEX IDX_52C31DE15585C142 (skill_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE skill (id INT AUTO_INCREMENT NOT NULL, concept_type VARCHAR(255) NOT NULL, concept_uri VARCHAR(255) NOT NULL, skill_type VARCHAR(255) NOT NULL, reuse_level VARCHAR(255) NOT NULL, preferred_label LONGTEXT NOT NULL, alt_labels LONGTEXT NOT NULL, hidden_labels LONGTEXT DEFAULT NULL, status VARCHAR(255) NOT NULL, modified_at DATETIME DEFAULT NULL, scope_note LONGTEXT DEFAULT NULL, definition LONGTEXT DEFAULT NULL, in_scheme LONGTEXT DEFAULT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE training (id INT AUTO_INCREMENT NOT NULL, occupation_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, location VARCHAR(255) DEFAULT NULL, duration VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, price VARCHAR(255) DEFAULT NULL, start_at DATETIME DEFAULT NULL, end_at DATETIME DEFAULT NULL, has_sessions TINYINT(1) DEFAULT \'0\' NOT NULL, is_online TINYINT(1) DEFAULT \'0\' NOT NULL, is_online_monitored TINYINT(1) DEFAULT \'0\' NOT NULL, is_presential TINYINT(1) DEFAULT \'0\' NOT NULL, url VARCHAR(255) DEFAULT NULL, files VARCHAR(255) DEFAULT NULL, score INT UNSIGNED DEFAULT 0 NOT NULL COMMENT \'Champs dynamique pour calcul de pondération lors d\'\'une recherche de formation\', INDEX IDX_D5128A8F22C8FC20 (occupation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE training_session (id INT AUTO_INCREMENT NOT NULL, training_id INT NOT NULL, name VARCHAR(255) NOT NULL, location VARCHAR(255) DEFAULT NULL, duration VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, price VARCHAR(255) DEFAULT NULL, start_at DATETIME DEFAULT NULL, end_at DATETIME DEFAULT NULL, url VARCHAR(255) DEFAULT NULL, files VARCHAR(255) DEFAULT NULL, INDEX IDX_D7A45DABEFD98D1 (training_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE training_session_skill (id INT AUTO_INCREMENT NOT NULL, training_session_id INT NOT NULL, skill_id INT NOT NULL, is_required TINYINT(1) DEFAULT \'0\' NOT NULL, is_to_acquire TINYINT(1) DEFAULT \'0\' NOT NULL, INDEX IDX_FBE93330DB8156B9 (training_session_id), INDEX IDX_FBE933305585C142 (skill_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE training_skill (id INT AUTO_INCREMENT NOT NULL, training_id INT NOT NULL, skill_id INT NOT NULL, is_required TINYINT(1) DEFAULT \'0\' NOT NULL, is_to_acquire TINYINT(1) DEFAULT \'0\' NOT NULL, INDEX IDX_B1E76E1ABEFD98D1 (training_id), INDEX IDX_B1E76E1A5585C142 (skill_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT UNSIGNED AUTO_INCREMENT NOT NULL, lastname VARCHAR(255) DEFAULT NULL, firstname VARCHAR(255) DEFAULT NULL, username VARCHAR(100) DEFAULT NULL, email VARCHAR(100) NOT NULL, api_token VARCHAR(100) DEFAULT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME on update CURRENT_TIMESTAMP, token_created_at DATETIME NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, homepage VARCHAR(255) DEFAULT NULL, year_of_birth VARCHAR(255) DEFAULT NULL, address LONGTEXT DEFAULT NULL, completion VARCHAR(5) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_8D93D6497BA2F5EB (api_token), FULLTEXT INDEX IDX_8D93D64983A00E683124B5B6F85E0677E7927C74 (firstname, lastname, username, email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_training (user_id INT UNSIGNED NOT NULL, training_id INT NOT NULL, INDEX IDX_359F6E8FA76ED395 (user_id), INDEX IDX_359F6E8FBEFD98D1 (training_id), PRIMARY KEY(user_id, training_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_occupation (id INT AUTO_INCREMENT NOT NULL, user_id INT UNSIGNED NOT NULL, occupation_id INT NOT NULL, is_current TINYINT(1) DEFAULT \'0\' NOT NULL, is_previous TINYINT(1) DEFAULT \'0\' NOT NULL, is_desired TINYINT(1) DEFAULT \'0\' NOT NULL, INDEX IDX_A59FBBBDA76ED395 (user_id), INDEX IDX_A59FBBBD22C8FC20 (occupation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_skill (id INT AUTO_INCREMENT NOT NULL, user_id INT UNSIGNED NOT NULL, skill_id INT NOT NULL, is_selected TINYINT(1) NOT NULL, INDEX IDX_BCFF1F2FA76ED395 (user_id), INDEX IDX_BCFF1F2F5585C142 (skill_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE occupation ADD CONSTRAINT FK_2F87D51A81E27D FOREIGN KEY (isco_group_id) REFERENCES isco_group (id)');
        $this->addSql('ALTER TABLE occupation_skill ADD CONSTRAINT FK_52C31DE122C8FC20 FOREIGN KEY (occupation_id) REFERENCES occupation (id)');
        $this->addSql('ALTER TABLE occupation_skill ADD CONSTRAINT FK_52C31DE15585C142 FOREIGN KEY (skill_id) REFERENCES skill (id)');
        $this->addSql('ALTER TABLE training ADD CONSTRAINT FK_D5128A8F22C8FC20 FOREIGN KEY (occupation_id) REFERENCES occupation (id)');
        $this->addSql('ALTER TABLE training_session ADD CONSTRAINT FK_D7A45DABEFD98D1 FOREIGN KEY (training_id) REFERENCES training (id)');
        $this->addSql('ALTER TABLE training_session_skill ADD CONSTRAINT FK_FBE93330DB8156B9 FOREIGN KEY (training_session_id) REFERENCES training_session (id)');
        $this->addSql('ALTER TABLE training_session_skill ADD CONSTRAINT FK_FBE933305585C142 FOREIGN KEY (skill_id) REFERENCES skill (id)');
        $this->addSql('ALTER TABLE training_skill ADD CONSTRAINT FK_B1E76E1ABEFD98D1 FOREIGN KEY (training_id) REFERENCES training (id)');
        $this->addSql('ALTER TABLE training_skill ADD CONSTRAINT FK_B1E76E1A5585C142 FOREIGN KEY (skill_id) REFERENCES skill (id)');
        $this->addSql('ALTER TABLE user_training ADD CONSTRAINT FK_359F6E8FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_training ADD CONSTRAINT FK_359F6E8FBEFD98D1 FOREIGN KEY (training_id) REFERENCES training (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_occupation ADD CONSTRAINT FK_A59FBBBDA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_occupation ADD CONSTRAINT FK_A59FBBBD22C8FC20 FOREIGN KEY (occupation_id) REFERENCES occupation (id)');
        $this->addSql('ALTER TABLE user_skill ADD CONSTRAINT FK_BCFF1F2FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_skill ADD CONSTRAINT FK_BCFF1F2F5585C142 FOREIGN KEY (skill_id) REFERENCES skill (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE occupation DROP FOREIGN KEY FK_2F87D51A81E27D');
        $this->addSql('ALTER TABLE occupation_skill DROP FOREIGN KEY FK_52C31DE122C8FC20');
        $this->addSql('ALTER TABLE training DROP FOREIGN KEY FK_D5128A8F22C8FC20');
        $this->addSql('ALTER TABLE user_occupation DROP FOREIGN KEY FK_A59FBBBD22C8FC20');
        $this->addSql('ALTER TABLE occupation_skill DROP FOREIGN KEY FK_52C31DE15585C142');
        $this->addSql('ALTER TABLE training_session_skill DROP FOREIGN KEY FK_FBE933305585C142');
        $this->addSql('ALTER TABLE training_skill DROP FOREIGN KEY FK_B1E76E1A5585C142');
        $this->addSql('ALTER TABLE user_skill DROP FOREIGN KEY FK_BCFF1F2F5585C142');
        $this->addSql('ALTER TABLE training_session DROP FOREIGN KEY FK_D7A45DABEFD98D1');
        $this->addSql('ALTER TABLE training_skill DROP FOREIGN KEY FK_B1E76E1ABEFD98D1');
        $this->addSql('ALTER TABLE user_training DROP FOREIGN KEY FK_359F6E8FBEFD98D1');
        $this->addSql('ALTER TABLE training_session_skill DROP FOREIGN KEY FK_FBE93330DB8156B9');
        $this->addSql('ALTER TABLE user_training DROP FOREIGN KEY FK_359F6E8FA76ED395');
        $this->addSql('ALTER TABLE user_occupation DROP FOREIGN KEY FK_A59FBBBDA76ED395');
        $this->addSql('ALTER TABLE user_skill DROP FOREIGN KEY FK_BCFF1F2FA76ED395');
        $this->addSql('DROP TABLE isco_group');
        $this->addSql('DROP TABLE occupation');
        $this->addSql('DROP TABLE occupation_skill');
        $this->addSql('DROP TABLE skill');
        $this->addSql('DROP TABLE training');
        $this->addSql('DROP TABLE training_session');
        $this->addSql('DROP TABLE training_session_skill');
        $this->addSql('DROP TABLE training_skill');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_training');
        $this->addSql('DROP TABLE user_occupation');
        $this->addSql('DROP TABLE user_skill');
    }
}
