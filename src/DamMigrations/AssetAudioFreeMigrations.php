<?php

declare(strict_types=1);

namespace App\DamMigrations;

use App\Model\MigrateConfig;
use Doctrine\DBAL\Exception;

final class AssetAudioFreeMigrations extends AbstractAssetAudioMigrations
{
    protected const SLOT_NAME = 'free';

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
            // checks if there is multiple FREE podcast with same title
            if (false === $this->shouldMigrate($row, false)) {
                continue;
            }

            $i++;
            $this->insertAssetFileMetadata($row);
            $this->insertAssetFile($row);

            // Existing asset has same title (assign
            $existingPremium = $this->getExistingPremium($row);
            if (empty($existingPremium)) {
                $this->insertAssetMetadata($row);
                $this->insertAsset($row);
            }

            $assetId = $existingPremium['id'] ?? $row['id'];

            $this->insertAudioFile($row, $assetId);
            $keywords = $this->insertKeywords($row, $assetId);
            $authors = $this->insertAuthors($row, $assetId);

            $artemisDistributionData = $this->prepareDistributionData($assetId, $row, $keywords, $authors, $existingPremium);

            $episode = $this->insertEpisode(
                $row,
                $artemisDistributionData,
                $assetId
            );

            if (false === empty($artemisDistributionData)) {
                $artemisDistributionData['texts_episode_id'] = $episode['id'] ?? '';
                $artemisDistributionData['texts_podcast_id'] = $episode['podcast_id'] ?? '';

                $this->prepareBulkInsert(
                    'distribution',
                    $artemisDistributionData
                );
            }

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

    /**
     * Extra conditions for initial data fetch from old system.
     */
    protected function getSelectConditions(MigrateConfig $migrateConfig): array
    {
        return [
            'au.audio_public_stream_is_public = false',
        ];
    }

    protected function getExistingPremium(array $row): array
    {
        $sql = '
            SELECT ass.id,  audio.audio_public_link_path,
            audio.audio_public_link_slug, audio.audio_public_link_is_public,
            audio.attributes_duration
            from asset_metadata am 
            INNER JOIN asset ass ON am.id = ass.metadata_id
            INNER JOIN audio_file audio ON audio.asset_id = ass.id
            WHERE am.custom_data->\'$.title\' = :title
        ';
        $existing = $this->defaultConnection->fetchAssociative($sql, [
            'title' => $row['texts_title'],
        ]);

        if (empty($existing)) {
            return [];
        }

        return $existing;
    }

    protected function getSlotName(): string
    {
        return self::SLOT_NAME;
    }
}
