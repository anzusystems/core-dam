<?php

declare(strict_types=1);

namespace App\DamMigrations;

use App\Model\MigrateConfig;
use Doctrine\DBAL\Exception;

final class AssetAudioPremiumMigrations extends AbstractAssetAudioMigrations
{
    protected const SLOT_NAME = 'premium';
    protected const PUBLIC_STREAM = true;

    /**
     * @throws Exception
     */
    public function migrate(MigrateConfig $migrateConfig): void
    {
        $res = $this->getAssets($migrateConfig);

        $progressBar = $this->outputUtil->createProgressBar($this->totalCount($migrateConfig));
        $progressBar->setFormat('debug');
        $progressBar->start();

        $i = 0;
        while ($row = $res->fetchAssociative()) {
            // checks if there is multiple PREMIUM podcast with same title
            if (false === $this->shouldMigrate($row, false)) {
                continue;
            }

            $assetId = $row['id'];
            $i++;
            $this->insertAssetFileMetadata($row);
            $this->insertAssetFile($row);

            $this->insertAssetMetadata($row);
            $this->insertAsset($row);

            $this->insertAudioFile($row, $assetId);
            $keywords = $this->insertKeywords($row, $assetId);
            $authors = $this->insertAuthors($row, $assetId);

            $this->insertAssetSlot($row, $assetId);

            if (0 === $i % self::BULK_SIZE) {
                $this->flush();
            }
            $progressBar->advance();
        }

        $this->flush();

        $progressBar->finish();
        $this->writeln('');
    }


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
