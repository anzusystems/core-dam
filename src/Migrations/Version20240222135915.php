<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240222135915 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE asset_licence_in_group (asset_licence_id INT NOT NULL, asset_licence_group_id INT NOT NULL, INDEX IDX_4C135576E00EE493 (asset_licence_id), INDEX IDX_4C1355769572D68A (asset_licence_group_id), PRIMARY KEY(asset_licence_id, asset_licence_group_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE asset_licence_group (id INT AUTO_INCREMENT NOT NULL, ext_system_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, modified_by_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_563A0204E961F7A (ext_system_id), INDEX IDX_563A0204B03A8386 (created_by_id), INDEX IDX_563A020499049ECE (modified_by_id), UNIQUE INDEX UNIQ_name_ext_system (name, ext_system_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE job_podcast_synchronizer (id INT NOT NULL, podcast_id VARCHAR(36) NOT NULL, full_sync TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE job_user_data_delete (id INT NOT NULL, target_user_id INT NOT NULL, anonymize_user TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_in_licence_groups (user_id INT NOT NULL, asset_licence_group_id INT NOT NULL, INDEX IDX_1789A906A76ED395 (user_id), INDEX IDX_1789A9069572D68A (asset_licence_group_id), PRIMARY KEY(user_id, asset_licence_group_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE asset_licence_in_group ADD CONSTRAINT FK_4C135576E00EE493 FOREIGN KEY (asset_licence_id) REFERENCES asset_licence (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE asset_licence_in_group ADD CONSTRAINT FK_4C1355769572D68A FOREIGN KEY (asset_licence_group_id) REFERENCES asset_licence_group (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE asset_licence_group ADD CONSTRAINT FK_563A0204E961F7A FOREIGN KEY (ext_system_id) REFERENCES ext_system (id)');
        $this->addSql('ALTER TABLE asset_licence_group ADD CONSTRAINT FK_563A0204B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE asset_licence_group ADD CONSTRAINT FK_563A020499049ECE FOREIGN KEY (modified_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE job_podcast_synchronizer ADD CONSTRAINT FK_8A540C84BF396750 FOREIGN KEY (id) REFERENCES job (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE job_user_data_delete ADD CONSTRAINT FK_2E11648DBF396750 FOREIGN KEY (id) REFERENCES job (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_in_licence_groups ADD CONSTRAINT FK_1789A906A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_in_licence_groups ADD CONSTRAINT FK_1789A9069572D68A FOREIGN KEY (asset_licence_group_id) REFERENCES asset_licence_group (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE job ADD scheduled_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD priority SMALLINT NOT NULL, DROP podcast_id, DROP full_sync, DROP target_user_id, DROP anonymize_user, CHANGE discr discriminator VARCHAR(255) NOT NULL');
        $this->addSql('CREATE INDEX IDX_job ON job (status, scheduled_at, priority)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE asset_licence_in_group DROP FOREIGN KEY FK_4C135576E00EE493');
        $this->addSql('ALTER TABLE asset_licence_in_group DROP FOREIGN KEY FK_4C1355769572D68A');
        $this->addSql('ALTER TABLE asset_licence_group DROP FOREIGN KEY FK_563A0204E961F7A');
        $this->addSql('ALTER TABLE asset_licence_group DROP FOREIGN KEY FK_563A0204B03A8386');
        $this->addSql('ALTER TABLE asset_licence_group DROP FOREIGN KEY FK_563A020499049ECE');
        $this->addSql('ALTER TABLE job_podcast_synchronizer DROP FOREIGN KEY FK_8A540C84BF396750');
        $this->addSql('ALTER TABLE job_user_data_delete DROP FOREIGN KEY FK_2E11648DBF396750');
        $this->addSql('ALTER TABLE user_in_licence_groups DROP FOREIGN KEY FK_1789A906A76ED395');
        $this->addSql('ALTER TABLE user_in_licence_groups DROP FOREIGN KEY FK_1789A9069572D68A');
        $this->addSql('DROP TABLE asset_licence_in_group');
        $this->addSql('DROP TABLE asset_licence_group');
        $this->addSql('DROP TABLE job_podcast_synchronizer');
        $this->addSql('DROP TABLE job_user_data_delete');
        $this->addSql('DROP TABLE user_in_licence_groups');
        $this->addSql('DROP INDEX IDX_job ON job');
        $this->addSql('ALTER TABLE job ADD podcast_id VARCHAR(36) DEFAULT NULL, ADD full_sync TINYINT(1) DEFAULT NULL, ADD target_user_id INT DEFAULT NULL, ADD anonymize_user TINYINT(1) DEFAULT NULL, DROP scheduled_at, DROP priority, CHANGE discriminator discr VARCHAR(255) NOT NULL');
    }
}
