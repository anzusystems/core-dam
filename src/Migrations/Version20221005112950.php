<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221005112950 extends AbstractMigration
{
    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE user_asset_licence (user_id INT NOT NULL, asset_licence_id INT NOT NULL, INDEX IDX_90CA0A4EA76ED395 (user_id), INDEX IDX_90CA0A4EE00EE493 (asset_licence_id), PRIMARY KEY(user_id, asset_licence_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_ext_system (user_id INT NOT NULL, ext_system_id INT NOT NULL, INDEX IDX_BD50B67FA76ED395 (user_id), INDEX IDX_BD50B67FE961F7A (ext_system_id), PRIMARY KEY(user_id, ext_system_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_asset_licence ADD CONSTRAINT FK_90CA0A4EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_asset_licence ADD CONSTRAINT FK_90CA0A4EE00EE493 FOREIGN KEY (asset_licence_id) REFERENCES asset_licence (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_ext_system ADD CONSTRAINT FK_BD50B67FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_ext_system ADD CONSTRAINT FK_BD50B67FE961F7A FOREIGN KEY (ext_system_id) REFERENCES ext_system (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dam_user_asset_licence DROP FOREIGN KEY FK_39FB0A0CAE87B06A');
        $this->addSql('ALTER TABLE dam_user_asset_licence DROP FOREIGN KEY FK_39FB0A0CE00EE493');
        $this->addSql('DROP TABLE dam_user_asset_licence');
        $this->addSql('ALTER TABLE image_file_optimal_resize ADD original TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE dam_user_asset_licence (dam_user_id INT NOT NULL, asset_licence_id INT NOT NULL, INDEX IDX_39FB0A0CE00EE493 (asset_licence_id), INDEX IDX_39FB0A0CAE87B06A (dam_user_id), PRIMARY KEY(dam_user_id, asset_licence_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE dam_user_asset_licence ADD CONSTRAINT FK_39FB0A0CAE87B06A FOREIGN KEY (dam_user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dam_user_asset_licence ADD CONSTRAINT FK_39FB0A0CE00EE493 FOREIGN KEY (asset_licence_id) REFERENCES asset_licence (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_asset_licence DROP FOREIGN KEY FK_90CA0A4EA76ED395');
        $this->addSql('ALTER TABLE user_asset_licence DROP FOREIGN KEY FK_90CA0A4EE00EE493');
        $this->addSql('ALTER TABLE user_ext_system DROP FOREIGN KEY FK_BD50B67FA76ED395');
        $this->addSql('ALTER TABLE user_ext_system DROP FOREIGN KEY FK_BD50B67FE961F7A');
        $this->addSql('DROP TABLE user_asset_licence');
        $this->addSql('DROP TABLE user_ext_system');
        $this->addSql('ALTER TABLE image_file_optimal_resize DROP original');
    }
}
