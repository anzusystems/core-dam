<?php

declare(strict_types=1);

namespace App\Domain\Image\MediaApi;

use AnzuSystems\CommonBundle\Domain\AbstractManager;
use AnzuSystems\CommonBundle\Traits\SerializerAwareTrait;
use AnzuSystems\CoreDamBundle\Domain\AssetFile\AssetFileManager;
use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use App\Domain\AssetMetadata\AssetMetadataManager;
use App\Model\Domain\Asset\AssetFileMediaApiDecorator;
use App\Model\Domain\Asset\AssetFileMediaApiResponseDecorator;
use App\Model\Domain\AssetMetadata\ExifMetadata;
use App\Model\Domain\AssetMetadata\MediaApiMetadata;
use Doctrine\Common\Collections\ArrayCollection;

final class ImageManager extends AbstractManager
{
    use SerializerAwareTrait;

    public function __construct(
        private readonly KeywordProvider $keywordProvider,
        private readonly AuthorProvider $authorProvider,
        private readonly AssetMetadataManager $assetMetadataManager,
        private readonly AssetMetadataFactory $assetMetadataFactory,
        private readonly RoiFactory $roiFactory,
    ) {
    }

    /**
     * @throws SerializerException
     */
    public function updateFromDto(ImageFile $image, AssetFileMediaApiDecorator $dto, bool $flush = true): void
    {
        $asset = $image->getAsset();
        $this->colUpdate(
            oldCollection: $asset->getKeywords(),
            newCollection: $this->keywordProvider->getKeywordsFromUpdateDto($dto, $image->getExtSystem()),
        );
        $this->colUpdate(
            oldCollection: $asset->getAuthors(),
            newCollection: $this->authorProvider->getAuthorCollFromDto($dto, $image->getExtSystem()),
        );

        $oldMetadata = $this->assetMetadataManager->getObjectFromMetadata(
            assetMetadata: $asset->getMetadata(),
            className: MediaApiMetadata::class
        );

        if (false === (
            $oldMetadata->getFocusX() === $dto->getFocusX() &&
            $oldMetadata->getFocusY() === $dto->getFocusY()
        )
        ) {
            $this->roiFactory->setupDefaultRoi($image, $dto);
        }

        $this->assetMetadataManager->updateMetadataFromObject(
            assetMetadata: $asset->getMetadata(),
            object: $this->assetMetadataFactory->updateFromMediaApi($oldMetadata, $dto)
        );

        if ($flush) {
            $this->flush();
        }
    }

    /**
     * @throws SerializerException
     */
    public function updateFromExif(ImageFile $image, bool $flush = true): void
    {
        $asset = $image->getAsset();

        $assetMetadata = $this->assetMetadataManager->getObjectFromMetadata(
            $asset->getMetadata(),
            MediaApiMetadata::class
        );

        /** @var ExifMetadata $exifMetadata */
        $exifMetadata = $this->serializer->fromArray($image->getMetadata()->getExifData(), ExifMetadata::class);

        // set authors from exif only if origin authors not set
        if ($asset->getAuthors()->isEmpty()) {
            $this->colUpdate(
                oldCollection: $asset->getAuthors(),
                newCollection: $this->authorProvider->getAuthorCollFromArray(
                    authors: $exifMetadata->getAssetAuthors(),
                    extSystem: $asset->getExtSystem()
                ),
            );
        }

        $assetMetadata->setTitle(
            empty($assetMetadata->getTitle())
                ? $exifMetadata->getAssetTitle()
                : $assetMetadata->getTitle()
        );
        $assetMetadata->setDescription(
            empty($assetMetadata->getDescription())
                ? $exifMetadata->getAssetDescription()
                : $assetMetadata->getDescription()
        );

        $this->assetMetadataManager->updateMetadataFromObject(
            assetMetadata: $asset->getMetadata(),
            object: $assetMetadata
        );

        if ($flush) {
            $this->flush();
        }
    }

    /**
     * @throws SerializerException
     */
    public function updateFromDuplicate(ImageFile $originImage, ImageFile $duplicate, bool $flush = true): void
    {
        $originAsset = $originImage->getAsset();
        $duplicateAsset = $duplicate->getAsset();

        // merge keywords
        $this->colUpdate(
            oldCollection: $originAsset->getKeywords(),
            newCollection: $duplicateAsset->getKeywords(),
        );

        // replace authors
        $this->colUpdate(
            oldCollection: $originAsset->getAuthors(),
            newCollection: $duplicateAsset->getAuthors(),
        );

        $this->assetMetadataManager->updateMetadataFromObject(
            assetMetadata: $originAsset->getMetadata(),
            object: $this->assetMetadataFactory->updateFromMediaApiMetadata(
                oldMetadata: $this->assetMetadataManager->getObjectFromMetadata(
                    $originAsset->getMetadata(),
                    MediaApiMetadata::class
                ),
                newMetadata: $this->assetMetadataManager->getObjectFromMetadata(
                    $duplicateAsset->getMetadata(),
                    MediaApiMetadata::class
                )
            )
        );

        if ($flush) {
            $this->flush();
        }
    }
}
