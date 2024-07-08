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
    private const array TEXTS_DESCRIPTION_WRITER_MAPPING = [
        TextsWriterConfiguration::SOURCE_PROPERTY_PATH_KEY => 'texts.description',
        TextsWriterConfiguration::DESTINATION_PROPERTY_PATH_KEY => 'asset.metadata.customData[description]',
    ];

    private const array AUTHOR_WRITER_MAPPING = [
        TextsWriterConfiguration::SOURCE_PROPERTY_PATH_KEY => 'author.customAuthor',
        TextsWriterConfiguration::DESTINATION_PROPERTY_PATH_KEY => 'asset.metadata.customData[author]',
    ];

    public function __construct(
        private readonly AssetTextsWriter $textsWriter,
    ) {
    }

    public function updateUgcImage(ImageFile $imageFile, ImageUpdateDto $updateImageFile, bool $flush = true): ImageFile
    {
        $this->textsWriter->writeValues(
            from: $updateImageFile,
            to: $imageFile,
            config: [
                TextsWriterConfiguration::getFromArrayConfiguration(self::TEXTS_DESCRIPTION_WRITER_MAPPING),
                TextsWriterConfiguration::getFromArrayConfiguration(self::AUTHOR_WRITER_MAPPING),
            ],
        );
        $imageFile->getAsset()->getAssetFlags()->setDescribed(true);
        $this->trackModification($imageFile);
        $this->trackModification($imageFile->getAsset());
        $this->flush($flush);

        return $imageFile;
    }
}
