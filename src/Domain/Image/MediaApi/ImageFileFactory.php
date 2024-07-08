<?php

declare(strict_types=1);

namespace App\Domain\Image\MediaApi;

use AnzuSystems\CoreDamBundle\Domain\AssetFile\AbstractAssetFileFactory;
use AnzuSystems\CoreDamBundle\Entity\AssetFile;
use AnzuSystems\CoreDamBundle\Entity\AssetLicence;
use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\CoreDamBundle\Exception\DomainException;
use AnzuSystems\CoreDamBundle\Exception\InvalidMimeTypeException;
use AnzuSystems\CoreDamBundle\Model\Dto\AssetFile\AssetFileAdmCreateDto;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetFileCreateStrategy;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetFileProcessStatus;
use AnzuSystems\CoreDamBundle\Model\Enum\ImageMimeTypes;
use AnzuSystems\CoreDamBundle\Model\ValueObject\OriginStorage;
use App\Configuration\ConfigurationProvider;
use App\Model\Domain\Asset\AssetFileMediaApiCreateDecorator;
use Doctrine\ORM\NonUniqueResultException;
use League\Flysystem\FilesystemException;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @extends AbstractAssetFileFactory<ImageFile>
 */
final class ImageFileFactory extends AbstractAssetFileFactory
{
    private const array CONVERT_MAP = [
        'image/bmp' => ImageMimeTypes::MimeJpeg->value,
    ];

    private LicenceProvider $licenceProvider;
    private ConfigurationProvider $damConfigurationProvider;

    #[Required]
    public function setLicenceProvider(LicenceProvider $licenceProvider): void
    {
        $this->licenceProvider = $licenceProvider;
    }

    #[Required]
    public function setDamConfigurationProvider(ConfigurationProvider $damConfigurationProvider): void
    {
        $this->damConfigurationProvider = $damConfigurationProvider;
    }

    public function createFromAdmDto(AssetLicence $licence, AssetFileAdmCreateDto $createDto): AssetFile
    {
        throw new DomainException('not implemented');
    }

    /**
     * @throws NonUniqueResultException
     * @throws FilesystemException
     * @throws InvalidMimeTypeException
     */
    public function createAssetFileForStorageFromMediaApi(
        AssetFileMediaApiCreateDecorator $dto
    ): ImageFile {
        $storageName = $this->damConfigurationProvider->getMediaApiSyncConfiguration()->getStorageName();
        $licence = $this->licenceProvider->getLicence($dto);

        try {
            return $this->createAssetFileForStorage(
                $storageName,
                $dto->getFullPath(),
                $licence
            );
        } catch (InvalidMimeTypeException $e) {
            if (false === isset(self::CONVERT_MAP[$e->getMimeType()])) {
                throw $e;
            }

            $mime = self::CONVERT_MAP[$e->getMimeType()];
        }

        $assetType = $this->getTypeFromMime($mime);
        /** @var ImageFile $assetFile */
        $assetFile = $this->createBlankAssetType($assetType, $licence);

        $assetFile->getAssetAttributes()
            ->setStatus(AssetFileProcessStatus::Uploaded)
            ->setCreateStrategy(AssetFileCreateStrategy::Storage)
            ->setConvertToMime($mime)
            ->setOriginStorage(new OriginStorage($storageName, $dto->getFullPath()))
        ;
        $asset = $this->assetFactory->createForAssetFile($assetFile, $licence);
        $this->assetManager->updateExisting($asset, false);

        return $this->assetFileManager->create($assetFile, false);
    }
}
