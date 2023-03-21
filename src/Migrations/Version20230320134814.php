<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230320134814 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_asset_file_asset_name ON asset_slot');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_asset_file_asset_name ON asset_slot (asset_id, name)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_asset_file_asset_name ON asset_slot');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_asset_file_asset_name ON asset_slot (asset_id, name, image_id, audio_id, video_id)');
    }
}
