<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231207114538 extends AbstractMigration
{
    public function isTransactional(): bool
    {
        return true;
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            INSERT INTO asset_file_route (
                id, target_asset_file_id, created_by_id, modified_by_id, status, mode, created_at, modified_at,
                uri_path, uri_slug, uri_main
            )
            SELECT 
                au.id,
                au.id,
                af.created_by_id,
                af.modified_by_id,
                \'active\',
                \'storage_copy\',
                af.created_at,
                af.modified_at,
                au.audio_public_link_path,
                au.audio_public_link_slug,
                1
            FROM audio_file au
            INNER JOIN core_dam.asset_file af ON au.id = af.id
            WHERE au.audio_public_link_is_public = 1
            ON DUPLICATE KEY UPDATE id = au.id;
        ');

        $this->addSql(
            '
            UPDATE asset_file af
            INNER JOIN asset_file_route afr ON af.id = afr.target_asset_file_id
            SET af.main_route_id = afr.id
            WHERE afr.uri_main = 1'
        );
    }

    public function down(Schema $schema): void
    {
    }
}
