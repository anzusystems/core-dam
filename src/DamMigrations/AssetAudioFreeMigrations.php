<?php

declare(strict_types=1);


namespace App\DamMigrations;

use App\Model\MigrateConfig;

final class AssetAudioFreeMigrations extends AbstractAssetAudioMigrations
{
    protected const SLOT_NAME = 'free';
    protected const PUBLIC_STREAM = false;

    protected function getSelectConditions(MigrateConfig $migrateConfig): array
    {
        return [
            'au.audio_public_stream_is_public = false'
        ];
    }

    protected function getExistingAssetId(array $row): ?string
    {
        $sql = 'SELECT ass.id from asset_metadata am INNER JOIN asset ass ON am.id = ass.metadata_id WHERE am.custom_data->\'$.title\' = :title';
        $existing = $this->defaultConnection->fetchOne($sql, ['title' => $row['texts_title']]);

        if (is_string($existing)) {
            return $existing;
        }

        return null;
    }

    protected function getSlotName(): string
    {
        return self::SLOT_NAME;
    }
}
