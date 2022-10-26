<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221020175624 extends AbstractMigration
{
    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE asset_file DROP FOREIGN KEY FK_68FBF3D8366C73B1');
        $this->addSql('DROP INDEX IDX_68FBF3D8366C73B1 ON asset_file');
        $this->addSql('ALTER TABLE asset_file ADD asset_attributes_origin_asset_id VARCHAR(36) NOT NULL DEFAULT \'\', DROP origin_asset_id');
        $this->addSql('
            ALTER TABLE audio_file
                ADD attributes_codec_name VARCHAR(64) NOT NULL DEFAULT \'\', 
                ADD attributes_bitrate INT NOT NULL DEFAULT 0
        ');
        $this->addSql('
            ALTER TABLE video_file 
                ADD attributes_ratio_width INT NOT NULL DEFAULT 0,
                ADD attributes_ratio_height INT NOT NULL DEFAULT 0, 
                ADD attributes_codec_name VARCHAR(64) NOT NULL DEFAULT \'\', 
                ADD attributes_bitrate INT NOT NULL DEFAULT 0
        ');
        $this->addSql('ALTER TABLE asset_file ALTER asset_attributes_origin_asset_id DROP DEFAULT ');
        $this->addSql('
            ALTER TABLE audio_file 
                ALTER attributes_codec_name DROP DEFAULT,
                ALTER attributes_bitrate DROP DEFAULT
        ');
        $this->addSql('
            ALTER TABLE video_file 
                ALTER attributes_ratio_width DROP DEFAULT,
                ALTER attributes_ratio_height DROP DEFAULT,
                ALTER attributes_codec_name DROP DEFAULT,
                ALTER attributes_bitrate DROP DEFAULT
        ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE asset_file ADD origin_asset_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', DROP asset_attributes_origin_asset_id');
        $this->addSql('ALTER TABLE asset_file ADD CONSTRAINT FK_68FBF3D8366C73B1 FOREIGN KEY (origin_asset_id) REFERENCES asset_file (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_68FBF3D8366C73B1 ON asset_file (origin_asset_id)');
        $this->addSql('ALTER TABLE audio_file DROP attributes_codec_name, DROP attributes_bitrate');
        $this->addSql('ALTER TABLE video_file DROP attributes_ratio_width, DROP attributes_ratio_height, DROP attributes_codec_name, DROP attributes_bitrate');
    }
}
