<?php

declare(strict_types=1);

namespace App\DamMigrations;

use AnzuSystems\CoreDamBundle\Distribution\Modules\JwPlayerCustomDataFactory;
use AnzuSystems\CoreDamBundle\Distribution\Modules\JwVideo\JwVideoDtoFactory;
use AnzuSystems\CoreDamBundle\Distribution\Modules\YoutubeCustomDataFactory;
use AnzuSystems\CoreDamBundle\Entity\JwDistribution;
use AnzuSystems\CoreDamBundle\Entity\YoutubeDistribution;
use AnzuSystems\CoreDamBundle\Helper\Math;
use AnzuSystems\CoreDamBundle\Model\Dto\Youtube\YoutubeVideoDto;
use App\App;
use App\DamMigrations\Cache\VideoCategoryCache;
use App\Distribution\Modules\ArtemisMediaDistributionCustomDataFactory;
use App\Domain\ArtemisVideoDistribution\ArtemisVideoDistributionModule;
use App\Model\Dto\Artemis\ArtemisMediaMetaDto;
use App\Model\Dto\Artemis\ArtemisMediaResponseDto;
use App\Model\MigrateConfig;
use DateTimeImmutable;
use Doctrine\DBAL\Exception;
use Symfony\Component\Uid\Uuid;

final class AssetVideoMigrations extends AbstractAssetMigrations
{
    public const ASSET_TYPE_DISC = 'videofile';
    protected const SLOT_NAME = 'default';
    private const JW_DISTRIBUTION_SERVICE = 'jw_cms';
    private const YT_DISTRIBUTION_MAIN_SERVICE = 'youtube_cms_main';
    private const YT_DISTRIBUTION_FICI_SERVICE = 'youtube_cms_fici';
    private const YT_DISTRIBUTION_ARTEMIS_SERVICE = 'artemis_video_cms';

    public function __construct(
        private readonly JwVideoDtoFactory $jwVideoDtoFactory,
        private readonly VideoCategoryCache $videoCategoryCache,
        private readonly JwPlayerCustomDataFactory $jwPlayerCustomDataFactory,
        private readonly YoutubeCustomDataFactory $youtubeCustomDataFactory,
        private readonly ArtemisMediaDistributionCustomDataFactory $artemisCustomDataFactory,
    ) {
    }

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
            $i++;
            $this->insertAssetFileMetadata($row);
            $this->insertAssetFile($row);
            $this->insertAssetMetadata($row);
            $this->insertAsset($row, $this->videoCategoryCache->getCategory((int) $row['video_category_id']));

            $this->insertVideoFile($row, $this->insertImagePreview($row));

            $keywords = $this->insertKeywords($row);
            $authors = $this->insertAuthors($row);

            $this->insertJwVideoDistribution($row, $keywords, $authors);
            $this->insertYoutubeDistribution($row, $keywords, $authors);
            $this->insertArtemisDistribution($row, $keywords, $authors);

            $this->insertAssetSlot($row, $row['id']);

            if (0 === $i % self::BULK_SIZE) {
                $this->flush();
            }
            $progressBar->advance();
        }

        $this->flush();

        $progressBar->finish();
        $this->writeln('');
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
            vid.video_category_id,
            img.image_id,
            img.roi_id
            FROM asset a
        LEFT JOIN video vid ON vid.id = a.id
        LEFT JOIN image_preview img ON img.id = vid.image_preview_id
        LEFT JOIN video_category vid_cat ON vid_cat.id = vid.video_category_id';
    }

    protected function getCustomData(array $row): string
    {
        return json_encode(
            array_filter([
                'description' => trim($row['texts_description']),
                'title' => trim($row['texts_title']),
            ])
        );
    }

    protected function prepareAssetTypeSpecific(array $row, ?string $assetId = null): void
    {
    }

    private function insertImagePreview(array $row): bool
    {
        if (empty($row['image_preview_id'])) {
            return false;
        }

        $res = $this->damLegacyConnection->fetchAssociative(
            'SELECT id, image_id, roi_id
                FROM image_preview
                WHERE id = :imagePreviewId',
            [
                'imagePreviewId' => $row['image_preview_id'],
            ]
        );

        if (false === $res) {
            // todo log
            return false;
        }

        $imageId = $res['image_id'];

        $res = $this->defaultConnection->fetchOne(
            'SELECT id
                FROM image_file
                WHERE id = :imagePreviewId',
            [
                'imagePreviewId' => $imageId,
            ]
        );

        if (is_string($res)) {
            $this->prepareBulkInsert('image_preview', [
                'id' => $row['id'],
                'image_file_id' => $res,
                'position' => 0,
                'created_at' => App::getAppDate()->format(DateTimeImmutable::ATOM),
                'modified_at' => App::getAppDate()->format(DateTimeImmutable::ATOM),
                'created_by_id' => App::getUserIdConsole(),
                'modified_by_id' => App::getUserIdConsole(),
            ]);

            return true;
        }

        return false;
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
        $data['id'] = $this->getId($row['id'], self::JW_DISTRIBUTION_SERVICE);
        $data['dtype'] = 'jwdistribution';
        $data['distribution_service'] = self::JW_DISTRIBUTION_SERVICE;
        $data['texts_title'] = $row['texts_title'];
        $data['texts_description'] = $row['texts_description'];
        $data['texts_author'] = $authors[0] ?? '';
        $data['texts_keywords'] = json_encode($keywords);
        $data['distribution_data'] = json_encode(
            $this->jwPlayerCustomDataFactory->createDistributionData((new JwDistribution())->setExtId($res['distribution_id']))
        );

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

        // todo blocked by distribution

        $data = $this->getBaseDistribution($row, $res);
        $data['id'] = $this->getId($row['id'], self::YT_DISTRIBUTION_ARTEMIS_SERVICE);
        $data['dtype'] = 'artemisvideodistribution';
        $data['distribution_service'] = self::YT_DISTRIBUTION_ARTEMIS_SERVICE;
        $data['distribution_data'] = json_encode(
            $this->artemisCustomDataFactory->createDistributionData(
                (new ArtemisMediaResponseDto())->setMeta(
                    (new ArtemisMediaMetaDto())
                        ->setArticleId((int) $res['media_meta_article_id'])
                        ->setArticleAdminUrl($res['media_meta_article_admin_url'])
                        ->setArticleUrl((string) $res['media_meta_article_url'])
                        ->setMediaAdminUrl((string) $res['media_meta_media_url'])
                )
            )
        );

        $data['texts_title'] = mb_substr($row['texts_title'], 0, 128); // todo truncate
        $data['texts_description'] = $row['texts_description'];
        $data['texts_authors'] = json_encode($authors);
        $data['texts_keywords'] = json_encode($keywords);
        $data['texts_rubric_id'] = 0; // todo
        $data['flags_create_article'] = $res['create_article'];

        // todo custom data
        $this->prepareBulkInsert('distribution', $data);
    }

    private function getId(string $assetId, string $distributionService): string
    {
        $id = $this->defaultConnection->fetchOne(
            'SELECT id FROM distribution where asset_id = :assetId and distribution_service = :service',
            [
                'assetId' => $assetId,
                'service' => $distributionService,
            ]
        );

        if (is_string($id)) {
            return $id;
        }

        return (string) Uuid::v6();
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
        $data['id'] = $this->getId($row['id'], self::YT_DISTRIBUTION_MAIN_SERVICE);
        $data['dtype'] = 'youtubedistribution';
        $data['distribution_service'] = self::YT_DISTRIBUTION_MAIN_SERVICE;
        $data['distribution_data'] = json_encode(
            $this->youtubeCustomDataFactory->createDistributionData((new YoutubeVideoDto())->setThumbnailUrl($res['thumbnail_url']))
        );
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

    private function insertVideoFile(array $row, bool $insertImagePreview): void
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
            'attributes_duration' => (int) $row['video_attributes_length'],
            'attributes_codec_name' => 0, // todo
            'attributes_bitrate' => 0, // todo
            'image_preview_id' => $insertImagePreview ? $row['id'] : null,
        ]);
    }
}
