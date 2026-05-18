<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Adds a single-column index IDX_target_asset_file on asset_file_route.target_asset_file_id.
 *
 * The existing composite index IDX_main_asset_file_id (uri_main, target_asset_file_id) cannot
 * cover `WHERE target_asset_file_id = ?` because target_asset_file_id is not the leftmost column.
 * Used by AssetFileRouteRepository::findByTarget() (TTS regen route-purge dispatch).
 */
final class Version20260518100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add IDX_target_asset_file on asset_file_route.target_asset_file_id';
    }

    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'ALTER TABLE asset_file_route ADD INDEX IDX_target_asset_file (target_asset_file_id), ALGORITHM=INPLACE, LOCK=NONE'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE asset_file_route DROP INDEX IDX_target_asset_file');
    }
}
