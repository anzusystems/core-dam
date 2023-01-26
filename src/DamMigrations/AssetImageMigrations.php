<?php

declare(strict_types=1);


namespace App\DamMigrations;

use AnzuSystems\CoreDamBundle\FileSystem\NameGenerator\NameGenerator;
use App\Model\MigrateConfig;

final class AssetImageMigrations extends AbstractAssetMigrations
{
    public function __construct(
        private readonly NameGenerator $nameGenerator,
    ) {
    }

    public const ASSET_TYPE_DISC = 'imagefile';

    protected function prepareAssetTypeSpecific(array $row, ?string $assetId = null): void
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
            path: $this->nameGenerator->alternatePath($row['file_attributes_file_path'], 'original')->getRelativePath(),
        );
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
            'created_by_id' => $this->getUserIdBySsoId($row['created_by_id']),
            'modified_by_id' => $this->getUserIdBySsoId($row['modified_by_id'])
        ]);
    }

    private function insertImageFileOptimalResize(
        string $imageId,
        int $requestedSize,
        int $width,
        int $height,
        string $path,
    ): void {
        $this->prepareBulkInsert('image_file_optimal_resize', [
            'id' => uuid_create(),
            'image_id' => $imageId,
            'requested_size' => $requestedSize,
            'width' => $width,
            'height' => $height,
            'file_path' => $path,
            'original' => 1, // TODO check
        ]);
    }

    protected function getSelectConditions(MigrateConfig $migrateConfig): array
    {
        if ($migrateConfig->isUgc()) {
            return [
                'i.image_type = "ugc"'
            ];
        }

        return [
            'i.image_type != "ugc"'
        ];
    }

    protected function getSlotName(): string
    {
       return self::SLOT_NAME;
    }

    protected function getCustomData(array $row): string
    {
        return json_encode(array_filter([
            'description' => trim($row['texts_description']),
            'author' => trim($row['custom_author'] ?? ''),
        ]));
    }
}
