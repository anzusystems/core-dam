<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221213071848 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_texts_title ON asset');
        $this->addSql('ALTER TABLE asset DROP texts_title');
        $this->addSql('CREATE INDEX IDX_licence_attributes_external_provider ON asset_file (licence_id, asset_attributes_origin_external_provider)');
        $this->addSql('CREATE INDEX IDX_attributes_status ON asset_file (asset_attributes_status)');
        $this->addSql('CREATE INDEX IDX_attributes_status_checksum ON asset_file (asset_attributes_status, asset_attributes_checksum)');
        $this->addSql('CREATE INDEX IDX_version_title ON asset_has_file (version_title)');
        $this->addSql('CREATE INDEX IDX_default ON asset_has_file (is_default)');
        $this->addSql('CREATE INDEX IDX_offset ON chunk (offset)');
        $this->addSql('CREATE INDEX IDX_searchable ON custom_form_element (attributes_searchable)');
        $this->addSql('CREATE INDEX IDX_position ON custom_form_element (position)');
        $this->addSql('CREATE INDEX IDX_asset_file_id ON distribution (asset_file_id)');
        $this->addSql('CREATE INDEX IDX_asset_id ON distribution (asset_id)');
        $this->addSql('CREATE INDEX IDX_asset_file_id_distribution_service ON distribution (asset_file_id, distribution_service)');
        $this->addSql('CREATE INDEX IDX_status ON distribution (status)');
        $this->addSql('CREATE INDEX IDX_name ON podcast (attributes_mode)');
        $this->addSql('CREATE INDEX IDX_podcast_position ON podcast_episode (podcast_id, position)');
        $this->addSql('CREATE INDEX IDX_position ON podcast_episode (position)');
        $this->addSql('CREATE INDEX IDX_title_licence_id ON podcast_episode (texts_title)');
        $this->addSql('ALTER TABLE user ADD sso_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_ssoId ON user (sso_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE asset ADD texts_title VARCHAR(255) NOT NULL');
        $this->addSql('CREATE INDEX IDX_texts_title ON asset (texts_title)');
        $this->addSql('DROP INDEX IDX_licence_attributes_external_provider ON asset_file');
        $this->addSql('DROP INDEX IDX_attributes_status ON asset_file');
        $this->addSql('DROP INDEX IDX_attributes_status_checksum ON asset_file');
        $this->addSql('DROP INDEX IDX_version_title ON asset_has_file');
        $this->addSql('DROP INDEX IDX_default ON asset_has_file');
        $this->addSql('DROP INDEX IDX_offset ON chunk');
        $this->addSql('DROP INDEX IDX_searchable ON custom_form_element');
        $this->addSql('DROP INDEX IDX_position ON custom_form_element');
        $this->addSql('DROP INDEX IDX_asset_file_id ON distribution');
        $this->addSql('DROP INDEX IDX_asset_id ON distribution');
        $this->addSql('DROP INDEX IDX_asset_file_id_distribution_service ON distribution');
        $this->addSql('DROP INDEX IDX_status ON distribution');
        $this->addSql('DROP INDEX IDX_name ON podcast');
        $this->addSql('DROP INDEX IDX_podcast_position ON podcast_episode');
        $this->addSql('DROP INDEX IDX_position ON podcast_episode');
        $this->addSql('DROP INDEX IDX_title_licence_id ON podcast_episode');
        $this->addSql('DROP INDEX UNIQ_ssoId ON user');
        $this->addSql('ALTER TABLE user DROP sso_id');
    }
}
