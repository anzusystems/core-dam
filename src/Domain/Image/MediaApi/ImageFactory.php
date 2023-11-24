<?php

declare(strict_types=1);

namespace App\Domain\Image\MediaApi;

use AnzuSystems\CoreDamBundle\Entity\AssetFile;
use AnzuSystems\CoreDamBundle\Exception\InvalidMimeTypeException;
use App\Domain\AssetMetadata\AssetMetadataManager;
use App\Model\Domain\Asset\AssetFileMediaApiCreateDecorator;
use App\Model\Domain\Asset\AssetFileMediaApiDecorator;
use App\Model\Domain\AssetMetadata\MediaApiMetadata;
use Doctrine\ORM\NonUniqueResultException;
use League\Flysystem\FilesystemException;

final readonly class ImageFactory
{
    public function __construct(
        private RoiFactory $roiFactory,
        private AuthorProvider $authorProvider,
        private KeywordProvider $keywordProvider,
        private UserProvider $userProvider,
        private AssetMetadataFactory $assetMetadataFactory,
        private AssetMetadataManager $assetMetadataManager,
        private ImageFileFactory $imageFileFactory,
    ) {
    }

    /**
     * @throws FilesystemException
     * @throws InvalidMimeTypeException
     * @throws NonUniqueResultException
     */
    public function createFromMediaApi(AssetFileMediaApiCreateDecorator $dto): AssetFile
    {
        $assetFile = $this->imageFileFactory->createAssetFileForStorageFromMediaApi($dto);
        $this->roiFactory->setupDefaultRoi($assetFile, $dto);
        $this->setupFlags($assetFile, $dto);
        $this->setupMetadata($assetFile, $dto);
        $this->setupKeywords($assetFile, $dto);
        $this->setupAuthor($assetFile, $dto);
        $this->trackUsers($assetFile, $dto);
        $this->trackDates($assetFile, $dto);

        return $assetFile;
    }

    private function setupFlags(AssetFile $assetFile, AssetFileMediaApiDecorator $dto): void
    {
        $assetFile->getAsset()->getAssetFlags()
            ->setDescribed(true)
        ;
        $assetFile->getFlags()
            ->setPublic($dto->isPublic())
            ->setSingleUse($dto->isSingleUse())
        ;
    }

    private function setupMetadata(AssetFile $assetFile, AssetFileMediaApiDecorator $dto): void
    {
        $metadata = $this->assetMetadataFactory->updateFromMediaApi(
            oldMetadata: (new MediaApiMetadata()),
            dto: $dto
        );
        $this->assetMetadataManager->updateMetadataFromObject(
            assetMetadata: $assetFile->getAsset()->getMetadata(),
            object: $metadata,
            flush: false
        );
    }

    private function trackDates(AssetFile $assetFile, AssetFileMediaApiDecorator $dto): void
    {
        $asset = $assetFile->getAsset();

        $assetFile->setModifiedAt($dto->getUpdatedAt());
        $asset->setModifiedAt($dto->getUpdatedAt());

        if ($dto instanceof AssetFileMediaApiCreateDecorator) {
            $assetFile->setCreatedAt($dto->getCreatedAt());
            $asset->setCreatedAt($dto->getCreatedAt());
        }
    }

    private function trackUsers(AssetFile $assetFile, AssetFileMediaApiDecorator $dto): void
    {
        $modifiedBy = $this->userProvider->getUser($dto->getIdCentralUserUpdated());
        $asset = $assetFile->getAsset();

        $assetFile->setModifiedBy($modifiedBy);
        $asset->setModifiedBy($modifiedBy);

        if ($dto instanceof AssetFileMediaApiCreateDecorator) {
            $createdBy = $this->userProvider->getUser($dto->getIdCentralUserCreated());

            $assetFile->setCreatedBy($createdBy);
            $asset->setCreatedBy($createdBy);
        }
    }

    private function setupKeywords(AssetFile $assetFile, AssetFileMediaApiCreateDecorator $dto): void
    {
        $asset = $assetFile->getAsset();
        $asset->setKeywords($this->keywordProvider->getKeywordsFromCreateDto($dto));
    }

    private function setupAuthor(AssetFile $assetFile, AssetFileMediaApiCreateDecorator $dto): void
    {
        $asset = $assetFile->getAsset();
        $author = $this->authorProvider->getAuthorFromDto($dto);

        if ($author) {
            $asset->getAuthors()->add($author);
        }
    }
}
