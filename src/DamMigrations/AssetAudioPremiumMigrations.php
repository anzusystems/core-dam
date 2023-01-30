<?php

declare(strict_types=1);

namespace App\DamMigrations;

use App\Model\MigrateConfig;

final class AssetAudioPremiumMigrations extends AbstractAssetAudioMigrations
{
    protected const SLOT_NAME = 'paid';
    protected const PUBLIC_STREAM = true;

    // todo check title duplicate!
    protected function getSelectConditions(MigrateConfig $migrateConfig): array
    {
        return [
            'au.audio_public_stream_is_public = true',
        ];
    }

    protected function getSlotName(): string
    {
        return self::SLOT_NAME;
    }
}
