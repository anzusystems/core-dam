<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230117123131 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_attributes_status_checksum ON asset_file');
        $this->addSql('CREATE INDEX IDX_licence_attributes_status_checksum ON asset_file (licence_id, asset_attributes_status, asset_attributes_checksum)');
        $this->addSql('ALTER TABLE audio_file ADD audio_public_link_path VARCHAR(255) NOT NULL, ADD audio_public_link_slug VARCHAR(128) NOT NULL, ADD audio_public_link_is_public TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE distribution ADD texts_authors JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_licence_attributes_status_checksum ON asset_file');
        $this->addSql('CREATE INDEX IDX_attributes_status_checksum ON asset_file (asset_attributes_status, asset_attributes_checksum)');
        $this->addSql('ALTER TABLE audio_file DROP audio_public_link_path, DROP audio_public_link_slug, DROP audio_public_link_is_public');
        $this->addSql('ALTER TABLE distribution DROP texts_authors');
    }
}
