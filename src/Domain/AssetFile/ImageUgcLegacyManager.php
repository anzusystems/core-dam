<?php

declare(strict_types=1);

namespace App\Domain\AssetFile;

use AnzuSystems\CoreDamBundle\Domain\AbstractManager;
use AnzuSystems\CoreDamBundle\Domain\Asset\AssetTextsWriter;
use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\CoreDamBundle\Model\Configuration\TextsWriter\TextsWriterConfiguration;
use App\Model\Ugc\Legacy\ImageUpdateDto;

final class ImageUgcLegacyManager extends AbstractManager
{

    //          title:
    //            source_property_path: 'title'
    //            destination_property_path: 'metadata.customData[title]'
    //            normalizers:
    //              - { type: string, options: { length: 64 } }
    //              - { type: html, options: { words_wrap: 0 } }
    //          description:
    //            source_property_path: 'description'
    //            destination_property_path: 'metadata.customData[description]'
    //            normalizers:
    //              - { type: string, options: { length: 5000 } }

    private const TEXTS_WRITER_MAPPING = [
        [
            'source_property_path' => 'texts.description',
            'destination_property_path' => 'asset.metadata.customData[description]',
        ],
        [
            'source_property_path' => 'title',
            'destination_property_path' => 'metadata.customData[title]',
        ],
    ];

    public function __construct(
        private readonly AssetTextsWriter $textsWriter,
    ) {
    }

    public function updateUgcImage(ImageFile $imageFile, ImageUpdateDto $newImageFile, bool $flush = true): ImageFile
    {
        $this->textsWriter->writeValues(
            from: $newImageFile,
            to: $imageFile,
            config: [TextsWriterConfiguration::getFromArrayConfiguration(self::TEXTS_WRITER_MAPPING)],
        );

        return $imageFile;
    }
}
