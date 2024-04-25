<?php

declare(strict_types=1);

namespace App\Domain\Image\MediaApi;

use AnzuSystems\CoreDamBundle\Domain\RegionOfInterest\RegionOfInterestManager;
use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\CoreDamBundle\Entity\RegionOfInterest;
use AnzuSystems\CoreDamBundle\Exception\DomainException;
use AnzuSystems\CoreDamBundle\FileSystem\FileSystemProvider;
use App\App;
use App\Configuration\ConfigurationProvider;
use App\MediaApiMigrations\PoiToRoiTransformer;
use App\Model\Domain\Asset\AssetFileMediaApiDecorator;
use App\Model\Dto\Image\PoiDto;
use League\Flysystem\FilesystemException;

final readonly class RoiFactory
{
    public function __construct(
        private FileSystemProvider $fileSystemProvider,
        private PoiToRoiTransformer $poiToRoiTransformer,
        private RegionOfInterestManager $regionOfInterestManager,
        private ConfigurationProvider $configurationProvider
    ) {
    }

    public function setupDefaultRoi(ImageFile $assetFile, AssetFileMediaApiDecorator $dto): RegionOfInterest
    {
        [$width, $height] = $this->getImageSize($assetFile, $dto);

        $roiDto = $this->poiToRoiTransformer->transformPoi(
            (new PoiDto(
                pointX: $dto->getFocusX(),
                pointY: $dto->getFocusY(),
                imageWidth: $width,
                imageHeight: $height
            ))
        );

        return $this->getDefaultRoi($assetFile)
            ->setImage($assetFile)
            ->setPercentageWidth($roiDto->getPercentageWidth())
            ->setPercentageHeight($roiDto->getPercentageHeight())
            ->setPointX($roiDto->getPointX())
            ->setPointY($roiDto->getPointY())
            ->setTitle('Default')
            ->setPosition(App::ZERO)
        ;
    }

    /**
     * @return list{int, int}
     * @throws FilesystemException
     */
    private function getImageSize(ImageFile $assetFile, AssetFileMediaApiDecorator $dto): array
    {
        $imageAttributes = $assetFile->getImageAttributes();
        if ($imageAttributes->getWidth() > 0 && $imageAttributes->getHeight() > 0) {
            return [$imageAttributes->getWidth(), $imageAttributes->getHeight()];
        }

        $fileSystem = $this->fileSystemProvider->getFileSystemByStorageName(
            $this->configurationProvider->getMediaApiSyncConfiguration()->getStorageName()
        );
        if (null === $fileSystem) {
            throw new DomainException('Filesystem not found');
        }

        $file = $this->fileSystemProvider->getTmpFileSystem()
            ->writeTmpFileFromStream(
                resource: $fileSystem->readStream($dto->getFullPath())
            );

        $imageSize = getimagesize($file->getRealPath());

        return [$imageSize[0], $imageSize[1]];
    }

    private function getDefaultRoi(ImageFile $imageFile): RegionOfInterest
    {
        $defaultRoi = $imageFile->getRegionsOfInterest()->filter(
            fn (RegionOfInterest $roi): bool => App::ZERO === $roi->getPosition()
        )->first();

        if ($defaultRoi instanceof RegionOfInterest) {
            return $defaultRoi;
        }

        $roi = (new RegionOfInterest());
        $roi->setImage($imageFile);
        $imageFile->getRegionsOfInterest()->add($roi);

        return $this->regionOfInterestManager->create($roi, false);
    }
}
