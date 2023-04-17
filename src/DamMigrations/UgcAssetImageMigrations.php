<?php

declare(strict_types=1);

namespace App\DamMigrations;

use AnzuSystems\CoreDamBundle\FileSystem\NameGenerator\NameGenerator;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Result;
use function Symfony\Component\String\u;

final class UgcAssetImageMigrations extends AbstractMigrations
{
    private const SLOT_NAME = 'default';

    public function __construct(
        private readonly NameGenerator $nameGenerator,
    ) {
    }

    /**
     * @throws Exception
     */
    public function migrate(): void
    {
        $res = $this->getAssets();

        $progressBar = $this->outputUtil->createProgressBar($this->totalCount());
        $progressBar->setFormat('debug');
        $progressBar->start();

        $i = 0;
        while ($row = $res->fetchAssociative()) {
            $i++;
            $this->insertAssetFileMetadata($row);
            $this->insertAssetFile($row);
            $this->insertAssetMetadata($row);
            $this->insertAsset($row);
            $this->prepareAssetTypeSpecific($row);
            $this->insertAssetSlot($row);

            if (0 === $i % self::BULK_SIZE) {
                $this->flush();
            }
            $progressBar->advance();
        }

        $this->flush();

        $progressBar->finish();
        $this->writeln('');
    }

    protected function getAssets(): Result
    {
        return $this->damLegacyConnection->executeQuery(
            '
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
                aha.custom_author,
                lg.id as licence_id
            FROM asset a
            INNER JOIN image i ON a.id = i.id
            LEFT JOIN asset_has_author aha ON aha.asset_id = a.id
            LEFT JOIN region_of_interest i_roi ON i_roi.image_id = i.id
            LEFT JOIN image_licence il ON i.id = il.image_id
            LEFT JOIN licence_group lg ON il.licence_group_id = lg.id
            WHERE a.process_process_state = :processState
            AND a.dtype = :dtype
            AND i.image_type = :imageType
        ',
            [
                'processState' => 'processed',
                'dtype' => 'image',
                'imageType' => 'ugc',
            ]
        );
    }

    protected function insertAsset(array $row): void
    {
        $this->prepareBulkInsert('asset', [
            'id' => $row['id'],
            'metadata_id' => $row['id'],
            'licence_id' => (int) $row['licence_id'],
            'distribution_category_id' => null,
            'texts_display_title' => $this->getDisplayTitle($row),
            'dates_uploaded_at' => $row['dates_uploaded_at'],
            'dates_expire_at' => null,
            'dates_publish_at' => null,
            'asset_flags_described' => (int) $row['asset_flags_is_described'],
            'asset_flags_visible' => 1,
            'asset_flags_generated_by_system' => 0,
            'asset_flags_autocompleted_metadata' => 1,
            'asset_flags_auto_delete_unprocessed' => 0,
            'asset_file_properties_distributes_in_services' => '[]',
            'asset_file_properties_slot_names' => '[]',
            'asset_file_properties_from_rss' => 0,
            'asset_file_properties_width' => 0,
            'asset_file_properties_height' => 0,
            'attributes_asset_type' => 'image',
            'attributes_status' => 'with_file',
            'main_file_id' => $row['id'],
            'created_at' => $row['created_at'],
            'modified_at' => $row['modified_at'],
            'created_by_id' => $this->getUserIdWithFallback($row['created_by_id']),
            'modified_by_id' => $this->getUserIdWithFallback($row['modified_by_id']),
        ]);
    }

    protected function getDisplayTitle(array $row): string
    {
        return $row['texts_title'] ?? $row['id'];
    }

    protected function totalCount(): int
    {
        return (int) $this->damLegacyConnection->fetchOne(
            '
            SELECT count(a.id)
            FROM asset a
            INNER JOIN image i ON i.id = a.id
            WHERE a.process_process_state = :processState
            AND a.dtype = :dtype
            AND i.image_type = :imageType
         ',
            [
                'processState' => 'processed',
                'dtype' => 'image',
                'imageType' => 'ugc',
            ]
        );
    }

    private function prepareAssetTypeSpecific(array $row): void
    {
        $this->insertImageFile($row);
        $this->insertRegionIfInterest($row);
        if ($row['medium_optimal_resize_path']) {
            $this->insertImageFileOptimalResize(
                imageId: $row['id'],
                requestedSize: 2_000,
                width: $row['medium_optimal_resize_width'],
                height: $row['medium_optimal_resize_height'],
                path: $row['medium_optimal_resize_path'],
            );
        }
        if ($row['small_optimal_resize_path']) {
            $this->insertImageFileOptimalResize(
                imageId: $row['id'],
                requestedSize: 1_000,
                width: $row['small_optimal_resize_width'],
                height: $row['small_optimal_resize_height'],
                path: $row['small_optimal_resize_path'],
            );
        }
        $this->insertImageFileOptimalResize(
            imageId: $row['id'],
            requestedSize: max($row['image_attributes_width'], $row['image_attributes_height']),
            width: $row['image_attributes_width'],
            height: $row['image_attributes_height'],
            path: $this->nameGenerator->alternatePath($row['file_attributes_file_path'])->getRelativePath(),
        );
    }

    private function getCustomData(array $row): string
    {
        return json_encode(array_filter([
            'description' => trim($row['texts_description']),
            'author' => trim($row['custom_author'] ?? ''),
        ]));
    }

    private function insertAssetMetadata(array $row): void
    {
        $this->prepareBulkInsert('asset_metadata', [
            'id' => $row['id'],
            'keyword_suggestions' => '{}',
            'author_suggestions' => '{}',
            'custom_data' => $this->getCustomData($row),
            'created_at' => $row['created_at'],
            'modified_at' => $row['modified_at'],
            'created_by_id' => $this->getUserIdWithFallback($row['created_by_id']),
            'modified_by_id' => $this->getUserIdWithFallback($row['modified_by_id']),
        ]);
    }

    private function insertAssetFileMetadata(array $row): void
    {
        $this->prepareBulkInsert('asset_file_metadata', [
            'id' => $row['id'],
            'exif_data' => json_encode(array_filter([
                'Headline' => trim($row['tags_headline']),
                'Title' => trim($row['tags_title']),
                'Description' => trim($row['tags_description']),
                'Creator' => trim($row['tags_creator']),
                'Event' => trim($row['tags_tag_event']),
                'PersonInImage' => trim($row['tags_person_shown']),
                'Keywords' => implode(', ', array_filter(
                    array_map(
                        'trim',
                        json_decode($row['tags_keywords'], true)
                    )
                )),
                'Author' => trim($row['tags_author']),
                'Orientation' => trim($row['tags_orientation']),
                'Color Space' => trim($row['tags_color_space']),
            ])),
            'created_at' => $row['created_at'],
            'modified_at' => $row['modified_at'],
            'created_by_id' => $this->getUserIdWithFallback($row['created_by_id']),
            'modified_by_id' => $this->getUserIdWithFallback($row['modified_by_id']),
        ]);
    }

    private function insertAssetFile(array $row): void
    {
        $this->prepareBulkInsert('asset_file', [
            'id' => $row['id'],
            'metadata_id' => $row['id'],
            'licence_id' => (int) $row['licence_id'],
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
            'dtype' => 'imagefile',
            'created_at' => $row['created_at'],
            'modified_at' => $row['modified_at'],
            'created_by_id' => $this->getUserIdWithFallback($row['created_by_id']),
            'modified_by_id' => $this->getUserIdWithFallback($row['modified_by_id']),
        ]);
    }

    private function insertAssetSlot(array $row): void
    {
        $this->prepareBulkInsert('asset_slot', [
            'id' => $row['id'],
            'asset_id' => $row['id'],
            'image_id' => $row['id'],
            'audio_id' => null,
            'video_id' => null,
            'document_id' => null,
            'name' => self::SLOT_NAME,
            'flags_is_default' => 1,
            'flags_is_main' => 1,
            'created_at' => $row['created_at'],
            'modified_at' => $row['modified_at'],
            'created_by_id' => $this->getUserIdWithFallback($row['created_by_id']),
            'modified_by_id' => $this->getUserIdWithFallback($row['modified_by_id']),
        ]);
    }

    private function insertImageFile(array $row): void
    {
        $this->prepareBulkInsert('image_file', [
            'id' => $row['id'],
            'image_attributes_ratio_width' => $row['image_attributes_ratio_width'],
            'image_attributes_ratio_height' => $row['image_attributes_ratio_height'],
            'image_attributes_width' => $row['image_attributes_width'],
            'image_attributes_height' => $row['image_attributes_height'],
            'image_attributes_rotation' => $row['image_attributes_rotation'],
            'image_attributes_most_dominant_color' => '#000000',
            'asset_id' => $row['id'],
        ]);
    }

    private function insertRegionIfInterest(array $row): void
    {
        $this->prepareBulkInsert('region_of_interest', [
            'id' => $row['id'],
            'image_id' => $row['id'],
            'point_x' => $row['roi_point_x'],
            'point_y' => $row['roi_point_y'],
            'percentage_width' => $row['roi_percentage_width'],
            'percentage_height' => $row['roi_percentage_height'],
            'title' => 'Default',
            'position' => 0,
            'created_at' => $row['created_at'],
            'modified_at' => $row['modified_at'],
            'created_by_id' => $this->getUserIdWithFallback($row['created_by_id']),
            'modified_by_id' => $this->getUserIdWithFallback($row['modified_by_id']),
        ]);
    }

    private function insertImageFileOptimalResize(
        string $imageId,
        int $requestedSize,
        int $width,
        int $height,
        string $path,
    ): void {
        $id = u($imageId)->slice(0, -(strlen((string) $requestedSize)))->append((string) $requestedSize);

        $this->prepareBulkInsert('image_file_optimal_resize', [
            'id' => $id->toString(),
            'image_id' => $imageId,
            'requested_size' => $requestedSize,
            'width' => $width,
            'height' => $height,
            'file_path' => $path,
            'original' => 1, // TODO check
        ]);
    }
}
