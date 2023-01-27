<?php

declare(strict_types=1);


namespace App\DamMigrations;

use AnzuSystems\CoreDamBundle\Model\Enum\DistributionFailReason;
use AnzuSystems\CoreDamBundle\Model\Enum\DistributionProcessStatus;
use App\DamMigrations\Cache\AuthorCache;
use App\DamMigrations\Cache\KeywordCache;
use App\DamMigrations\Cache\PodcastCache;
use App\Model\MigrateConfig;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Result;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractAssetMigrations extends AbstractMigrations
{
    // todo described
    public const ASSET_TYPE_DISC = 'imagefile';
    protected const SLOT_NAME = 'default';
    private const BULK_SIZE = 1;

    protected AuthorCache $authorCache;
    protected KeywordCache $keywordCache;
    protected PodcastCache $podcastCache;

    #[Required]
    public function setPodcastCache(PodcastCache $podcastCache): void
    {
        $this->podcastCache = $podcastCache;
    }

    #[Required]
    public function setAuthorCache(AuthorCache $authorCache): void
    {
        $this->authorCache = $authorCache;
    }

    #[Required]
    public function setKeywordCache(KeywordCache $keywordCache): void
    {
        $this->keywordCache = $keywordCache;
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
        $skipped = 0;
        while ($row = $res->fetchAssociative()) {
            if (false === $this->shouldMigrate($row)) {
                $skipped++;

                continue;
            }

            $i++;
            $this->insertAssetFileMetadata($row);
            $this->insertAssetFile($row);

            $existingAssetId = $this->getExistingAssetId($row);
            if (null === $existingAssetId) {
                $this->insertAssetMetadata($row);
                $this->insertAsset($row);
            }

            $this->prepareAssetTypeSpecific($row, $existingAssetId);
            $this->insertAssetSlot($row, $existingAssetId);

            if (0 === $i % self::BULK_SIZE) {
                $this->flush();
            }
            $progressBar->advance();
        }

        $this->flush();

        $progressBar->finish();
        $this->writeln('');
    }

    protected function shouldMigrate(array $row): bool
    {
        return true;
    }

    protected function getExistingAssetId(array $row): ?string
    {
        return null;
    }

    abstract protected function prepareAssetTypeSpecific(array $row, ?string $assetId = null): void;

    abstract protected function getSlotName(): string;

    abstract protected function getCustomData(array $row): string;

    protected function insertAssetMetadata(array $row): void
    {
        $this->prepareBulkInsert('asset_metadata',[
            'id' => $row['id'],
            'keyword_suggestions' => '{}',
            'author_suggestions' => '{}',
            'custom_data' => $this->getCustomData($row),
            'created_at' => $row['created_at'],
            'modified_at' => $row['modified_at'],
            'created_by_id' => $row['created_by_id'],
            'modified_by_id' => $row['modified_by_id'],
        ]);
    }

    protected function insertAssetFileMetadata(array $row): void
    {
        $this->prepareBulkInsert('asset_file_metadata',[
            'id' => $row['id'],
            'exif_data' => json_encode(array_filter([
                'Headline' => trim($row['tags_headline']),
                'Title' => trim($row['tags_title']),
                'Description' => trim($row['tags_description']),
                'Creator' => trim($row['tags_creator']),
                'Event' => trim($row['tags_tag_event']),
                'PersonInImage' => trim($row['tags_person_shown']),
                'Keywords' => implode(', ', array_filter(
                    array_map('trim',
                        json_decode($row['tags_keywords'], true)
                    )
                )),
                'Author' => trim($row['tags_author']),
                'Orientation' => trim($row['tags_orientation']),
                'Color Space' => trim($row['tags_color_space']),
            ])),
            'created_at' => $row['created_at'],
            'modified_at' => $row['modified_at'],
            'created_by_id' => $row['created_by_id'],
            'modified_by_id' => $row['modified_by_id']
        ]);
    }

    protected function insertAssetFile(array $row): void
    {
        $this->prepareBulkInsert('asset_file',[
            'id' => $row['id'],
            'metadata_id' => $row['id'],
            'licence_id' => $row['licence_id'],
            'asset_attributes_checksum' => $row['file_attributes_checksum'],
            'asset_attributes_origin_asset_id' => '',
            'asset_attributes_file_path' => $row['file_attributes_file_path'],
            'asset_attributes_origin_file_name' => $row['file_attributes_origin_file_name'],
            'asset_attributes_mime_type' => $row['file_attributes_extension'],
            'asset_attributes_size' => $row['file_attributes_size'],
            'asset_attributes_origin_url' => $row['file_attributes_origin_url'] ?? '',
            'asset_attributes_status' => 'processed',
            'asset_attributes_fail_reason' => 'none',
            'flags_processed_metadata' => 1,
            'asset_attributes_create_strategy' => 'chunk',
            'dtype' => static::ASSET_TYPE_DISC,
            'created_at' => $row['created_at'],
            'modified_at' => $row['modified_at'],
            'created_by_id' => $row['created_by_id'],
            'modified_by_id' => $row['modified_by_id'],
        ]);
    }

    private function insertAsset(array $row): void
    {
        $this->prepareBulkInsert('asset',[
            'id' => $row['id'],
            'metadata_id' => $row['id'],
            'licence_id' => $row['licence_id'],
            'distribution_category_id' => null, // TODO
            'texts_display_title' => $this->getDisplayTitle($row), // TODO
            'dates_uploaded_at' => $row['dates_uploaded_at'],
            'dates_expire_at' => null, // TODO
            'dates_publish_at' => $row['publish_at'] ?? null,
            'asset_flags_described' => $row['asset_flags_is_described'],
            'asset_flags_visible' => 1, // TODO
            'asset_flags_generated_by_system' => 0, // TODO
            'asset_flags_autocompleted_metadata' => 1, // TODO
            'asset_flags_auto_delete_unprocessed' => 0, // TODO
            'attributes_asset_type' => $this->getLegacyDamAssetType(),
            'attributes_status' => 'with_file',
            'main_file_id' => $row['id'],
            'created_at' => $row['created_at'],
            'modified_at' => $row['modified_at'],
            'created_by_id' => $row['created_by_id'],
            'modified_by_id' => $row['modified_by_id'],
        ]);
    }

    private function getDisplayTitle(array $row): string
    {
        return $row['texts_title'] ?? $row['id'];
    }

    protected function insertAssetSlot(array $row, ?string $assetId = null): void
    {
        $this->prepareBulkInsert('asset_slot',[
            'id' => $row['id'],
            'asset_id' => $assetId ?? $row['id'],
            'image_id' => 'imagefile' === static::ASSET_TYPE_DISC ? $row['id'] : null,
            'audio_id' => 'audiofile' === static::ASSET_TYPE_DISC ? $row['id'] : null,
            'video_id' => 'videofile' === static::ASSET_TYPE_DISC ? $row['id'] : null,
            'document_id' => null,
            'name' => $this->getSlotName(),
            'flags_is_default' => 1,
            'flags_is_main' => 1,
            'created_at' => $row['created_at'],
            'modified_at' => $row['modified_at'],
            'created_by_id' => $row['created_by_id'],
            'modified_by_id' => $row['modified_by_id'],
        ]);
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
                i.image_attributes_ratio_width,
                i.image_attributes_ratio_height,
                i.image_attributes_width,
                i.image_attributes_height,
                i.image_attributes_rotation,
                i.small_optimal_resize_width,
                i.small_optimal_resize_height,
                i.small_optimal_resize_path,
                i.medium_optimal_resize_width,
                i.medium_optimal_resize_height,
                i.medium_optimal_resize_path,
                i_roi.id as roi_id,
                i_roi.point_x as roi_point_x,
                i_roi.point_y as roi_point_y,
                i_roi.percentage_width as roi_percentage_width,
                i_roi.percentage_height as roi_percentage_height,
                i_roi.title as roi_title,
                COALESCE(au.audio_dates_publish_at, v.video_dates_publish_at) as publish_at,
                aha.custom_author,
                COALESCE(lg.id, :defaultLicenceId) as licence_id,
                au.audio_category_id,
                au.audio_attributes_length,
                au.audio_dates_publish_at,
                au.audio_public_stream_path,
                au.audio_public_stream_slug,
                au.audio_public_stream_is_public
            FROM asset a
            LEFT JOIN asset_has_author aha ON aha.asset_id = a.id
            LEFT JOIN video v ON v.id = a.id
            LEFT JOIN video_category v_cat ON v_cat.id = v.video_category_id
            LEFT JOIN jw_video jw_v ON jw_v.video_id = v.id
            LEFT JOIN youtube_video yt_v ON yt_v.video_id = v.id
            LEFT JOIN audio au ON au.id = a.id
            LEFT JOIN audio_category au_cat ON au_cat.id = au.audio_category_id
            LEFT JOIN image i ON a.id = i.id
            LEFT JOIN region_of_interest i_roi ON i_roi.image_id = i.id
            LEFT JOIN image_licence il ON i.id = il.image_id
            LEFT JOIN licence_group lg ON il.licence_group_id = lg.id
        ';
    }

    protected function getAssets(MigrateConfig $migrateConfig): Result
    {
        $sql = $this->getBaseSelectSql();

        $conditions = [
            'a.process_process_state = :processState',
            'a.dtype = :type'
        ];
        $conditions = array_merge($this->getSelectConditions($migrateConfig), $conditions);
        $sql .= ' WHERE ' . implode(' AND ', $conditions);

        return $this->damLegacyConnection->executeQuery($sql, [
            'defaultLicenceId' => self::CMS_LICENCE_ID,
            'processState' => 'processed',
            'type' => $this->getLegacyDamAssetType(),
        ]);
    }

    private function totalCount(MigrateConfig $migrateConfig): int
    {
        $sql = '
            SELECT count(a.id)
            FROM asset a
            LEFT JOIN image i ON i.id = a.id
            LEFT JOIN video v ON v.id = a.id
            LEFT JOIN audio au ON au.id = a.id
         ';
        $conditions = [
            'a.process_process_state = :processState',
            'a.dtype = :type'
        ];
        $conditions = array_merge($this->getSelectConditions($migrateConfig), $conditions);
        $sql .= ' WHERE ' . implode(' AND ', $conditions);

        return (int) $this->damLegacyConnection->fetchOne(
            $sql,
            [
                'processState' => 'processed',
                'type' => $this->getLegacyDamAssetType(),
            ]
        );
    }

    protected function insertKeywords(array $row, ?string $assetId = null): array
    {
        $sql = '
            SELECT
                k.id, k.title
            FROM asset_has_keyword ahk
            INNER JOIN keyword k ON k.id = ahk.keyword_id
            WHERE ahk.asset_id = :assetId
        ';

        $keywords = $this->damLegacyConnection->fetchAllAssociative($sql, ['assetId' => $row['id']]);
        $keywordNames = [];
        foreach ($keywords as $keyword) {
            $keywordNames[] = $keyword['title'];
            $this->prepareBulkInsert(
                'asset_keyword',
                [
                    'asset_id' => $assetId ?? $row['id'],
                    'keyword_id' => $this->keywordCache->getKeyword($keyword['title'])
                ],
                [
                    'asset_keyword.asset_id = new_row.asset_id',
                    'asset_keyword.keyword_id = new_row.keyword_id',
                ]
            );
        }

        return $keywordNames;
    }

    protected function insertAuthors(array $row, ?string $assetId = null): array
    {
        $sql = '
            SELECT
                auth.custom_author, auth.position, a.title
            FROM asset_has_author auth
            LEFT JOIN author a ON a.id = auth.author_id
            WHERE auth.asset_id = :assetId
        ';

        // todo custom author (jolo);
        $authors = $this->damLegacyConnection->fetchAllAssociative($sql, ['assetId' => $row['id']]);
        $authorNames = [];
        foreach ($authors as $author) {
            $authorNames[] = $author['title'];
            $this->prepareBulkInsert(
                'asset_author',
                [
                    'asset_id' => $assetId ??$row['id'],
                    'author_id' => $this->authorCache->getAuthor($author['title'])
                ],
                [
                    'asset_author.asset_id = new_row.asset_id',
                    'asset_author.author_id = new_row.author_id',
                ]
            );
        }

        return $authorNames;
    }

    protected function getBaseDistribution(array $videoRow, array $distributionRow): array
    {
        return [
            'id' => (string) Uuid::v6(),
            'created_by_id' => $distributionRow['distributed_by_id'],
            'modified_by_id' => $distributionRow['distributed_by_id'],
            'notify_to_id' => $distributionRow['distributed_by_id'],
            'created_at' => $videoRow['created_at'],
            'modified_at' => $videoRow['modified_at'],
            'publish_at' => null,
            'distribution_service' => '',
            'asset_file_id' => $videoRow['id'],
            'asset_id' => $videoRow['id'],
            'ext_id' => $distributionRow['distribution_id'],
            'status' => DistributionProcessStatus::Distributed->toString(),
            'fail_reason' => DistributionFailReason::None->toString(),
            'distribution_data' => '[]',
            'custom_data' => '[]',
            'texts_title' => null,
            'texts_description' =>null,
            'texts_author' => null,
            'texts_authors' => null,
            'texts_keywords' => null,
            'privacy' => null,
            'channel_id' => null,
            'playlist' => null,
            'language' => null,
            'flags_embeddable' => null,
            'flags_for_kids' => null,
            'flags_notify_subscribers' => null,
            'rss_url' => null,
        ];
    }

    private function getLegacyDamAssetType(): string
    {
        return match (static::ASSET_TYPE_DISC) {
            AssetImageMigrations::ASSET_TYPE_DISC => 'image',
            AbstractAssetAudioMigrations::ASSET_TYPE_DISC => 'audio',
            AssetVideoMigrations::ASSET_TYPE_DISC => 'video',
        };
    }

    protected function getSelectConditions(MigrateConfig $migrateConfig): array
    {
        return [];
    }
}
