<?php

declare(strict_types=1);


namespace App\DamMigrations;

use AnzuSystems\CoreDamBundle\Command\Traits\OutputUtilTrait;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetType;
use App\Model\MigrateConfig;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Result;

abstract class AbstractAssetMigrations extends AbstractMigrations
{
    use OutputUtilTrait;

    public const ASSET_TYPE_DISC = 'imagefile';

    /**
     * @throws Exception
     */
    public function migrate(MigrateConfig $migrateConfig): void
    {
        $res = $this->getAssets($migrateConfig);

        $progressBar = $this->outputUtil->createProgressBar($this->totalCount($migrateConfig));
        $progressBar->setFormat('debug');
        $progressBar->start();

        while ($row = $res->fetchAssociative()) {
            if ($this->hasAsset($row['id'])) {
                continue;
            }
            $this->defaultConnection->beginTransaction();
            $this->insertAssetMetadata($row);
            $this->insertAssetFileMetadata($row);
            $this->insertAssetFile($row);
            $this->insertAsset($row);
            $this->migrateAssetTypeSpecific($row);
            $this->insertAssetSlot($row);
            $this->defaultConnection->commit();

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->writeln('');
    }

    abstract protected function migrateAssetTypeSpecific(array $row): void;

    protected function insertAssetMetadata(array $row): void
    {
        $this->defaultConnection->insert(
            'asset_metadata',
            [
                'id' => $row['id'],
                'keyword_suggestions' => '{}',
                'author_suggestions' => '{}',
                'custom_data' => json_encode(array_filter([
                    'description' => trim($row['texts_description']),
                    'author' => trim($row['custom_author'] ?? ''),
                ])),
                'created_at' => $row['created_at'],
                'modified_at' => $row['modified_at'],
                'created_by_id' => $this->getUserIdBySsoId($row['created_by_id']),
                'modified_by_id' => $this->getUserIdBySsoId($row['modified_by_id'])
            ]
        );
    }

    protected function insertAssetFileMetadata(array $row): void
    {
        $this->defaultConnection->insert(
            'asset_file_metadata',
            [
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
                'created_by_id' => $this->getUserIdBySsoId($row['created_by_id']),
                'modified_by_id' => $this->getUserIdBySsoId($row['modified_by_id'])
            ]
        );
    }

    protected function insertAssetFile(array $row): void
    {
        $this->defaultConnection->insert(
            'asset_file',
            [
                'id' => $row['id'],
                'metadata_id' => $row['id'],
                'licence_id' => $row['licence_id'],
                'asset_attributes_checksum' => $row['file_attributes_checksum'],
                'asset_attributes_origin_asset_id' => '',
                'asset_attributes_file_path' => $row['file_attributes_file_path'],
                'asset_attributes_origin_file_name' => $row['file_attributes_origin_file_name'],
                'asset_attributes_mime_type' => $row['file_attributes_extension'],
                'asset_attributes_size' => $row['file_attributes_size'],
                'asset_attributes_uploaded_size' => $row['file_attributes_size'],
                'asset_attributes_origin_url' => $row['file_attributes_origin_url'],
                'asset_attributes_status' => 'processed',
                'asset_attributes_fail_reason' => 'none',
                'flags_processed_metadata' => 1,
                'asset_attributes_create_strategy' => 'chunk',
                'dtype' => static::ASSET_TYPE_DISC,
                'created_at' => $row['created_at'],
                'modified_at' => $row['modified_at'],
                'created_by_id' => $this->getUserIdBySsoId($row['created_by_id']),
                'modified_by_id' => $this->getUserIdBySsoId($row['modified_by_id'])
            ]
        );
    }

    private function insertAsset(array $row): void
    {
        $this->defaultConnection->insert(
            'asset',
            [
                'id' => $row['id'],
                'metadata_id' => $row['id'],
                'licence_id' => $row['licence_id'],
                'distribution_category_id' => null, // TODO
                'texts_display_title' => $row['id'], // TODO
                'dates_uploaded_at' => $row['dates_uploaded_at'],
                'dates_expire_at' => null, // TODO
                'dates_publish_at' => $row['publish_at'] ?? null,
                'asset_flags_described' => $row['asset_flags_is_described'],
                'asset_flags_visible' => 1, // TODO
                'asset_flags_generated_by_system' => 1, // TODO
                'asset_flags_autocompleted_metadata' => 1, // TODO
                'asset_flags_auto_delete_unprocessed' => 0, // TODO
                'attributes_asset_type' => match (static::ASSET_TYPE_DISC) {
                    AssetImageMigrations::ASSET_TYPE_DISC => AssetType::Image->toString(),
                },
                'attributes_status' => 'with_file',
                'main_file_id' => $row['id'],
                'created_at' => $row['created_at'],
                'modified_at' => $row['modified_at'],
                'created_by_id' => $this->getUserIdBySsoId($row['created_by_id']),
                'modified_by_id' => $this->getUserIdBySsoId($row['modified_by_id'])
            ]
        );
    }

    protected function insertAssetSlot(array $row): void
    {
        $this->defaultConnection->insert(
            'asset_slot',
            [
                'id' => $row['id'],
                'asset_id' => $row['id'],
                'image_id' => 'imagefile' === static::ASSET_TYPE_DISC ? $row['id'] : null,
                'audio_id' => 'audiofile' === static::ASSET_TYPE_DISC ? $row['id'] : null,
                'video_id' => 'videofile' === static::ASSET_TYPE_DISC ? $row['id'] : null,
                'document_id' => null,
                'name' => 'default',
                'flags_is_default' => 1,
                'flags_is_main' => 1,
                'created_at' => $row['created_at'],
                'modified_at' => $row['modified_at'],
                'created_by_id' => $this->getUserIdBySsoId($row['created_by_id']),
                'modified_by_id' => $this->getUserIdBySsoId($row['modified_by_id'])
            ]
        );
    }

    protected function getAssets(MigrateConfig $migrateConfig): Result
    {
        $sql = 'SELECT
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
                COALESCE(lg.id, :defaultLicenceId) as licence_id
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
            WHERE a.process_process_state = :processState AND a.dtype = :type AND lg.ext_id = 718
        ';
        $sql .= $migrateConfig->isUgc()
            ? ' AND i.image_type = "ugc"'
            : ' AND i.image_type != "ugc"';

        return $this->damLegacyConnection->executeQuery($sql, [
            'defaultLicenceId' => self::CMS_LICENCE_ID,
            'processState' => 'processed',
            'type' => $this->getLegacyDamAssetType(),
        ]);
    }

    private function hasAsset(string $assetMetadataId): bool
    {
        $res = $this->defaultConnection->fetchOne(
            'SELECT id FROM asset WHERE id = ?',
            [
                $assetMetadataId
            ]
        );

        return is_string($res);
    }

    private function totalCount(MigrateConfig $migrateConfig): int
    {
        $sql = 'SELECT count(a.id) FROM asset a LEFT JOIN image i ON i.id = a.id WHERE a.dtype = ?';
        $sql .= $migrateConfig->isUgc() ? ' AND i.image_type = "ugc"' : ' AND i.image_type != "ugc"';

        return (int) $this->damLegacyConnection->fetchOne(
            $sql,
            [$this->getLegacyDamAssetType()]
        );
    }

    private function getLegacyDamAssetType(): string
    {
        return match (static::ASSET_TYPE_DISC) {
            AssetImageMigrations::ASSET_TYPE_DISC => 'image',
        };
    }
}
