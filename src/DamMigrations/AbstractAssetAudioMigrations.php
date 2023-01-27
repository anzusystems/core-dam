<?php

declare(strict_types=1);


namespace App\DamMigrations;

use AnzuSystems\CoreDamBundle\FileSystem\NameGenerator\NameGenerator;
use AnzuSystems\CoreDamBundle\Model\Enum\DistributionFailReason;
use AnzuSystems\CoreDamBundle\Model\Enum\DistributionProcessStatus;
use App\Model\MigrateConfig;
use Symfony\Component\Uid\Uuid;

abstract class AbstractAssetAudioMigrations extends AbstractAssetMigrations
{
    public const ASSET_TYPE_DISC = 'audiofile';
    protected const PUBLIC_STREAM = false;

    protected function prepareAssetTypeSpecific(array $row, ?string $assetId = null): void
    {
        $this->insertAudioFile($row, $assetId);
        $keywords = $this->insertKeywords($row, $assetId);
        $authors = $this->insertAuthors($row, $assetId);
        $distributions = $this->insertDistribution($row);
        $this->insertEpisode($row, $distributions, $assetId);
    }

    private function insertEpisode(array $row, array $distributions, ?string $assetId = null): void
    {
        $sql = '
            SELECT
                se.id, se.season_id, se.created_by_id, se.modified_by_id, se.title as episodeTitle, se.created_at, se.modified_at, se.audio_id,
                ss.title as seasonTitle, vs.title as showTitle
            FROM show_episode se
            INNER JOIN show_session ss ON se.season_id = ss.id
            INNER JOIN video_show vs ON vs.id = ss.show_id
            where se.audio_id = :audioId
       ';

        $rssDistrib = array_values(array_filter(
            $distributions,
            fn (array $array) => false === ('artemis' === $array['type'])
        ))[0] ?? [];

        $episodes = $this->damLegacyConnection->fetchAllAssociative($sql, ['audioId' => $row['id']]);
        foreach ($episodes as $episode)
        {
            $this->prepareBulkInsert(
                'podcast_episode',[
                    'id' => (string) Uuid::v6(),
                    'podcast_id' => $this->podcastCache->getPodcast($episode['showTitle']),
                    'asset_id' => $assetId ?? $row['id'],
                    'created_at' => $row['created_at'],
                    'modified_at' => $row['modified_at'],
                    'created_by_id' => $row['created_by_id'],
                    'modified_by_id' => $row['modified_by_id'],
                    'position' => 0, // todo reorder position
                    'dates_publication_date' => $row['audio_dates_publish_at'],
                    'attributes_ext_id' => $rssDistrib['distribution_id'] ?? '',
                    'attributes_season_number' => (int) ($episode['seasonTitle'] ?? null),
                    'attributes_episode_number' => (int) ($episode['episodeTitle'] ?? null),
                    'texts_title' => $row['texts_title'],
                    'texts_description' => $row['texts_description'],
//                    'texts_description' => '',
                    'texts_raw_description' => '', // todo from artemis
                ]
            );
        }
    }

    private function insertDistribution(array $row): array
    {
        $distributions = $this->damLegacyConnection->fetchAllAssociative(
            'SELECT
                    id, audio_id, distributed_by_id, type, ext_id, url, params, distribution_id, distribute, process_state
                FROM podcast WHERE audio_id = :audioId',
            [
                'audioId' => $row['id']
            ]
        );

        foreach ($distributions as $distribution) {
            $distributionData = $this->getBaseDistribution($row, $distribution);
            $distributionData['distribution_data'] = '[]'; // todo from params "params" => "{"mediaUrl": "https://artemis.sme.sk/admin/media/46954/edit/section/117", "articleId": 23043331, "articleUrl": "https://podcasty.sme.sk/c/23043331/.html", "articleAdminUrl": "https://artemis.sme.sk/admin/article/23043331/edit/section/117"}"
            $distribution['ext_id'] = $distribution['distribution_id'];

            if ('artemis' === $distribution['type']) {
                $distributionData['custom_data'] = '[]'; // todo
                $distributionData['distribution_service'] = 'artemis_podcast_cms';
                $distributionData['dtype'] = 'customdistribution';
            }
            if (false === ('artemis' === $distribution['type'])) {
                $distributionData['rss_url'] = $row['file_attributes_origin_url'];
                $distributionData['distribution_service'] = 'podcast_rss_main';
                $distributionData['dtype'] = 'rssdistribution';
            }

            $this->prepareBulkInsert(
                'distribution',
                $distributionData
            );
        }

        return $distributions;
    }

    private function insertAudioFile(array $row, ?string $assetId = null): void
    {
        $this->prepareBulkInsert('audio_file',[
            'id' => $row['id'],
            'asset_id' => $assetId ?? $row['id'],
            'attributes_duration' => $row['audio_attributes_length'],
            'attributes_codec_name' => '', // todo
            'attributes_bitrate' => 0, // todo
            'audio_public_link_path' => $row['audio_public_stream_path'],
            'audio_public_link_slug' => $row['audio_public_stream_slug'],
            'audio_public_link_is_public' => $row['audio_public_stream_is_public'],
        ]);
    }

    protected function getCustomData(array $row): string
    {
        return json_encode(array_filter([
            'description' => trim($row['texts_description']),
            'title' => trim($row['texts_title']),
        ]));
    }

    protected function shouldMigrate(array $row): bool
    {
        $sql = '
            SELECT COUNT(texts_title)
            FROM asset a
            LEFT JOIN audio au ON au.id = a.id
            WHERE a.texts_title = :title and au.audio_public_stream_is_public = :public
        ';

        $count = $this->damLegacyConnection->fetchOne($sql, ['title' => $row['texts_title'], 'public' => self::PUBLIC_STREAM]);

        if ($count > 1) {
            $this->outputUtil->error(
                sprintf('Skipp migrate audio with id: (%s) title: (%s)', $row['id'], $row['texts_title'])
            );
        }

        return 1 === $count;
    }

    protected function getBaseSelectSql(): string
    {
        return '
            SELECT
                a.id,
                a.created_at,
                a.modified_at,
                a.created_by_id,
                a.modified_by_id,
                a.dates_uploaded_at,
                a.texts_description,
                a.tags_headline,
                a.tags_title,
                a.tags_description,
                a.tags_creator,
                a.tags_tag_event,
                a.tags_person_shown,
                a.tags_keywords,
                a.tags_author,
                a.tags_color_space,
                a.tags_orientation,
                a.texts_title,
                a.texts_description,
                a.texts_tag_event,
                a.texts_persons,
                a.dtype,
                a.file_attributes_checksum,
                a.file_attributes_file_path,
                a.file_attributes_origin_file_name,
                a.file_attributes_origin_url,
                a.file_attributes_extension,
                a.file_attributes_size,
                a.asset_flags_is_described,
                :defaultLicenceId as licence_id,
                au.audio_category_id,
                au.audio_attributes_length,
                au.audio_dates_publish_at,
                au.audio_public_stream_path,
                au.audio_public_stream_slug,
                au.audio_public_stream_is_public
            FROM asset a
            LEFT JOIN audio au ON au.id = a.id
            LEFT JOIN audio_category au_cat ON au_cat.id = au.audio_category_id'
        ;
    }
}
