<?php

declare(strict_types=1);

namespace App\DamMigrations;

use AnzuSystems\CoreDamBundle\Distribution\Modules\JwVideo\JwVideoDtoFactory;
use AnzuSystems\CoreDamBundle\Entity\JwDistribution;
use AnzuSystems\CoreDamBundle\Entity\YoutubeDistribution;
use AnzuSystems\CoreDamBundle\Helper\Math;
use App\Domain\ArtemisVideoDistribution\ArtemisVideoDistributionModule;

final class AssetVideoMigrations extends AbstractAssetMigrations
{
    public const ASSET_TYPE_DISC = 'videofile';
    protected const SLOT_NAME = 'default';
    private const JW_DISTRIBUTION_SERVICE = 'jw_cms';
    private const YT_DISTRIBUTION_MAIN_SERVICE = 'youtube_cms_main';
    private const YT_DISTRIBUTION_FICI_SERVICE = 'youtube_cms_fici';
    private const YT_DISTRIBUTION_ARTEMIS_SERVICE = 'artemis_cms';

    public function __construct(
        private readonly JwVideoDtoFactory $jwVideoDtoFactory,
    ) {
    }

    protected function getSlotName(): string
    {
        return self::SLOT_NAME;
    }

    protected function getBaseSelectSql(): string
    {
        return 'SELECT
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
            100000 as licence_id,
            vid.image_preview_id,
            vid.video_attributes_width,
            vid.video_attributes_height,
            vid.video_attributes_length,
            vid.video_dates_publish_at,
            vid.youtube_language_id,
            vid.youtube_general_configuration_for_children,
            vid.youtube_general_configuration_embeddable,
            vid.video_attributes_rotation,
            img.image_id,
            img.roi_id
            FROM asset a
        LEFT JOIN video vid ON vid.id = a.id
        LEFT JOIN image_preview img ON img.id = vid.image_preview_id
        LEFT JOIN video_category vid_cat ON vid_cat.id = vid.video_category_id';
    }

    protected function prepareAssetTypeSpecific(array $row, ?string $assetId = null): void
    {
        $this->insertVideoFile($row);
        $keywords = $this->insertKeywords($row);
        $authors = $this->insertAuthors($row);
        $this->insertJwVideoDistribution($row, $keywords, $authors);
        $this->insertYoutubeDistribution($row, $keywords, $authors);
        $this->insertArtemisDistribution($row, $keywords, $authors);
    }

    protected function getCustomData(array $row): string
    {
        return json_encode(array_filter([
            'description' => trim($row['texts_description']),
            'title' => trim($row['texts_title']),
        ]));
    }

    private function insertJwVideoDistribution(array $row, array $keywords, array $authors): void
    {
        $sql = '
            SELECT 
                id, category_id, video_id, distributed_by_id, distribution_id, distribute, process_state
            FROM jw_video
            WHERE video_id = :videoId and process_state = :processState
        ';

        $res = $this->damLegacyConnection->fetchAssociative($sql, [
            'videoId' => $row['id'],
            'processState' => 'distributed',
        ]);
        if (false === $res) {
            return;
        }

        $data = $this->getBaseDistribution($row, $res);
        $data['dtype'] = 'jwdistribution';
        $data['distribution_service'] = self::JW_DISTRIBUTION_SERVICE;
        $data['texts_title'] = $row['texts_title'];
        $data['texts_description'] = $row['texts_description'];
        $data['texts_author'] = $authors[0] ?? '';
        $data['texts_keywords'] = json_encode($keywords);
        $data['distribution_data'] = json_encode([
            JwDistribution::THUMBNAIL_DATA => $this->jwVideoDtoFactory->createThumbnailUrl($res['distribution_id']),
        ]);

        $this->prepareBulkInsert('distribution', $data);
    }

    private function insertArtemisDistribution(array $row, array $keywords, array $authors): void
    {
        $sql = '
            SELECT 
                id, video_id, distributed_by_id, create_article, distribution_id, process_state, distribute, media_meta_article_id,
                media_meta_article_url, media_meta_article_admin_url, media_meta_media_url
            FROM artemis_video
            WHERE video_id = :videoId and process_state = :processState
        ';

        $res = $this->damLegacyConnection->fetchAssociative($sql, ['videoId' => $row['id'], 'processState' => 'distributed']);
        if (false === $res) {
            return;
        }

        $data = $this->getBaseDistribution($row, $res);
        $data['dtype'] = 'customdistribution';
        $data['distribution_service'] = self::YT_DISTRIBUTION_ARTEMIS_SERVICE;
        $data['distribution_data'] = json_encode([
            ArtemisVideoDistributionModule::ARTICLE_WEB_URL => $res['media_meta_article_url'],
            ArtemisVideoDistributionModule::ARTICLE_ADMIN_URL => $res['media_meta_article_admin_url'],
            ArtemisVideoDistributionModule::MEDIA_ADMIN_URL => $res['media_meta_media_url'],
            ArtemisVideoDistributionModule::ARTICLE_ID => $res['media_meta_article_id'],
        ]);

        // todo custom data
        $this->prepareBulkInsert('distribution', $data);
    }

    private function insertYoutubeDistribution(array $row, array $keywords, array $authors): void
    {
        $sql = '
            SELECT 
                id, category_id, video_id, distributed_by_id, channel_id, playlist_id, privacy, distribution_id, distribute,
                process_state, flags_notify_subscribers, texts_title, texts_description, texts_keywords, dates_publish_at, 
                thumbnail_width, thumbnail_height, thumbnail_url, flags_should_update_thumbnail, flags_playlist_updated,
                flags_should_update_playlist, flags_thumbnail_updated
            FROM youtube_video
            WHERE video_id = :videoId and process_state = :processState
        ';

        $res = $this->damLegacyConnection->fetchAssociative($sql, ['videoId' => $row['id'], 'processState' => 'distributed']);
        if (false === $res) {
            return;
        }

        $data = $this->getBaseDistribution($row, $res);
        $data['dtype'] = 'youtubedistribution';
        $data['distribution_service'] = self::YT_DISTRIBUTION_MAIN_SERVICE;
        $data['distribution_data'] = json_encode([
            YoutubeDistribution::THUMBNAIL_WIDTH => $res['thumbnail_width'],
            YoutubeDistribution::THUMBNAIL_HEIGHT => $res['thumbnail_height'],
            YoutubeDistribution::THUMBNAIL_DATA => $res['thumbnail_url'],
        ]);
        $data['texts_title'] = $res['texts_title'];
        $data['texts_description'] = $res['texts_description'];
        $data['texts_authors'] = json_encode($authors);
        $data['texts_keywords'] = json_encode($keywords);
        $data['privacy'] = $res['privacy'];
        $data['publish_at'] = $res['dates_publish_at'];
        $data['channel_id'] = $res['channel_id'] ?? '';
        $data['playlist'] = $res['playlist_id'] ?? '';
        $data['language'] = $row['youtube_language_id'] ?? ''; // todo
        $data['flags_embeddable'] = $row['youtube_general_configuration_embeddable'];
        $data['flags_for_kids'] = $row['youtube_general_configuration_for_children'];
        $data['flags_notify_subscribers'] = $res['flags_notify_subscribers'];

        $this->prepareBulkInsert(
            'distribution',
            $data
        );
    }

    private function insertVideoFile(array $row): void
    {
        // todo 'preview_image_id'
        $gcd = Math::getGreatestCommonDivisor($row['video_attributes_width'], $row['video_attributes_height']);

        $this->prepareBulkInsert('video_file', [
            'id' => $row['id'],
            'asset_id' => $assetId ?? $row['id'],
            'attributes_ratio_width' => (int) ($row['video_attributes_width'] / $gcd),
            'attributes_ratio_height' => (int) ($row['video_attributes_height'] / $gcd),
            'attributes_width' => $row['video_attributes_width'],
            'attributes_height' => $row['video_attributes_height'],
            'attributes_rotation' => $row['video_attributes_rotation'],
            'attributes_duration' => $row['video_attributes_length'],
            'attributes_codec_name' => 0, // todo
            'attributes_bitrate' => 0, // todo
        ]);
    }
}
