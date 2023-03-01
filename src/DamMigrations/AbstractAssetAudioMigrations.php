<?php

declare(strict_types=1);

namespace App\DamMigrations;

use AnzuSystems\CoreDamBundle\Helper\UrlHelper;
use AnzuSystems\CoreDamBundle\Model\Enum\PodcastEpisodeStatus;
use App\Distribution\Modules\ArtemisAudioDistributionModule;

abstract class AbstractAssetAudioMigrations extends AbstractAssetMigrations
{
    public const ASSET_TYPE_DISC = 'audiofile';
    protected const PUBLIC_STREAM = false;

    protected function getCustomData(array $row): string
    {
        return json_encode(array_filter([
            'description' => trim($row['texts_description']),
            'title' => trim($row['texts_title']),
        ]));
    }

    protected function shouldMigrate(array $row, bool $public): bool
    {
        $sql = '
            SELECT COUNT(texts_title)
            FROM asset a
            LEFT JOIN audio au ON au.id = a.id
            WHERE a.texts_title = :title and au.audio_public_stream_is_public = :public
        ';

        $count = $this->damLegacyConnection->fetchOne($sql, [
            'title' => $row['texts_title'],
            'public' => self::PUBLIC_STREAM,
        ]);

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

    protected function insertEpisode(array $row, array $artemisDistributionData, ?string $assetId = null): array
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

        $episodes = $this->damLegacyConnection->fetchAllAssociative($sql, ['audioId' => $row['id']]);

        if (1 < count($episodes)) {
            dump('JOLO'); // todo
        }

        $newEpisode = [];
        foreach ($episodes as $episode) {
            $newEpisode = [
                'id' => $row['id'],
                'podcast_id' => $this->podcastCache->getPodcast($episode['showTitle']),
                'asset_id' => $assetId ?? $row['id'],
                'created_at' => $row['created_at'],
                'modified_at' => $row['modified_at'],
                'created_by_id' => $row['created_by_id'],
                'modified_by_id' => $row['modified_by_id'],
                'position' => 0, // todo reorder position
                'dates_publication_date' => $row['audio_dates_publish_at'],
                'attributes_rss_id' => $artemisDistributionData['texts_ext_rss_id'] ?? '',
                'flags_from_rss' => empty($artemisDistributionData['texts_free_url']) ? 0 : 1,
                'attributes_rss_url' => $artemisDistributionData['texts_free_url'] ?? '',
                'attributes_last_import_status' =>
                    empty($artemisDistributionData['texts_ext_rss_id'])
                        ? PodcastEpisodeStatus::Imported->toString()
                        : PodcastEpisodeStatus::NotImported->toString(),
                'attributes_season_number' => (int) ($episode['seasonTitle'] ?? null),
                'attributes_episode_number' => (int) ($episode['episodeTitle'] ?? null),
                'texts_title' => $row['texts_title'],
                'texts_description' => $row['texts_description'],
                'texts_raw_description' => '', // todo from artemis
            ];
            $this->prepareBulkInsert(
                'podcast_episode',
                $newEpisode
            );
        }

        return $newEpisode;
    }

    protected function prepareDistributionData(
        string $assetId,
        array $freeAsset,
        array $keywords,
        array $authors,
        array $premiumAsset,
    ): array {
        $distributions = $this->damLegacyConnection->fetchAllAssociative(
            'SELECT
                    id, audio_id, distributed_by_id, type, ext_id, url, params, distribution_id, distribute, process_state
                FROM podcast WHERE audio_id = :audioId AND process_state = :processState',
            [
                'audioId' => $freeAsset['id'],
                'processState' => 'distributed',
            ]
        );

        $rssDistribution = [
            'id' => '',
            'url' => '',
        ];
        $artemisDistributionData = [];

        foreach ($distributions as $distribution) {
            if ('anchor' === $distribution['type']) {
                $rssDistribution = [
                    'id' => $distribution['ext_id'],
                    'url' => $distribution['url'],
                ];
            }

            if ('artemis' === $distribution['type']) {
                $artemisDistributionData = $this->getBaseDistribution($freeAsset, $distribution);
                $distribution['ext_id'] = $distribution['distribution_id'];
                $params = json_decode($distribution['params'] ?? '{}', true);
                $artemisDistributionData['id'] = $assetId;
                $artemisDistributionData['asset_file_id'] = $assetId;
                $artemisDistributionData['asset_id'] = $assetId;
                $artemisDistributionData['distribution_service'] = 'artemis_podcast_cms';
                $artemisDistributionData['dtype'] = 'artemisaudiodistribution';
                //                $artemisDistributionData['publish_at'] = 'todo'; // todo
                $artemisDistributionData['distribution_data'] = json_encode(
                    [
                        ArtemisAudioDistributionModule::ARTICLE_WEB_URL => $params['articleUrl'] ?? '',
                        ArtemisAudioDistributionModule::ARTICLE_ADMIN_URL => $params['articleAdminUrl'] ?? '',
                        ArtemisAudioDistributionModule::MEDIA_ADMIN_URL => $params['mediaUrl'] ?? '',
                    ]
                );
                $artemisDistributionData['texts_title'] = mb_substr($freeAsset['texts_title'], 0, 128); // todo truncate
                $artemisDistributionData['texts_description'] = $freeAsset['texts_description'];
                $artemisDistributionData['texts_premium_url'] =
                    empty($premiumAsset['audio_public_link_path']) ? '' : $this->addDomain($premiumAsset['audio_public_link_path']);
                $artemisDistributionData['texts_authors'] = json_encode($authors);
                $artemisDistributionData['texts_keywords'] = json_encode($keywords);
                $artemisDistributionData['texts_rubric_id'] = 6978;
                $artemisDistributionData['texts_episode_id'] = '';
                $artemisDistributionData['texts_podcast_id'] = '';
                $artemisDistributionData['attributes_duration'] = (int) ($freeAsset['audio_attributes_length'] ?? 0);
                $artemisDistributionData['attributes_premium_duration'] = (int) ($premiumAsset['attributes_duration'] ?? 0);
                $artemisDistributionData['flags_create_article'] = 0;
                $artemisDistributionData['flags_bonus_episode'] = 0;
            }
        }

        if (false === empty($artemisDistributionData)) {
            $artemisDistributionData['texts_ext_rss_id'] = $rssDistribution['id'];
            $artemisDistributionData['texts_free_url'] = $rssDistribution['url'];

            return $artemisDistributionData;
        }

        return [];
    }

    protected function insertAudioFile(array $row, ?string $assetId = null): void
    {
        $this->prepareBulkInsert('audio_file', [
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

    protected function prepareAssetTypeSpecific(array $row, ?string $assetId = null): void
    {
    }

    private function addDomain(string $url): string
    {
        return UrlHelper::concatPathWithDomain('https://audio.smedata.sk', $url);
    }
}
