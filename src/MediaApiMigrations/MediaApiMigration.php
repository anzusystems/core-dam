<?php

declare(strict_types=1);

namespace App\MediaApiMigrations;

use AnzuSystems\Contracts\Entity\Interfaces\TimeTrackingInterface;
use AnzuSystems\Contracts\Entity\Interfaces\UserTrackingInterface;
use AnzuSystems\CoreDamBundle\Command\Traits\OutputUtilTrait;
use AnzuSystems\CoreDamBundle\Domain\Asset\AssetFactory;
use AnzuSystems\CoreDamBundle\Domain\Asset\AssetTextsProcessor;
use AnzuSystems\CoreDamBundle\Domain\AssetFile\AssetFileStatusFacadeProvider;
use AnzuSystems\CoreDamBundle\Domain\AssetFile\FileProcessor\AssetFileStorageOperator;
use AnzuSystems\CoreDamBundle\Domain\AssetFile\FileProcessor\FileAttributesProcessor;
use AnzuSystems\CoreDamBundle\Domain\AssetFileMetadata\AssetFileMetadataManager;
use AnzuSystems\CoreDamBundle\Domain\Image\FileProcessor\OptimalCropsProcessor;
use AnzuSystems\CoreDamBundle\Domain\Image\ImageFactory;
use AnzuSystems\CoreDamBundle\Domain\Image\ImageManager;
use AnzuSystems\CoreDamBundle\Domain\RegionOfInterest\RegionOfInterestManager;
use AnzuSystems\CoreDamBundle\Entity\AssetFileMetadata;
use AnzuSystems\CoreDamBundle\Entity\AssetLicence;
use AnzuSystems\CoreDamBundle\Entity\Author;
use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\CoreDamBundle\Entity\Keyword;
use AnzuSystems\CoreDamBundle\Entity\RegionOfInterest;
use AnzuSystems\CoreDamBundle\Exception\AssetFileProcessFailed;
use AnzuSystems\CoreDamBundle\Exception\DuplicateAssetFileException;
use AnzuSystems\CoreDamBundle\Exiftool\Exiftool;
use AnzuSystems\CoreDamBundle\FileSystem\AbstractFilesystem;
use AnzuSystems\CoreDamBundle\FileSystem\FileSystemProvider;
use AnzuSystems\CoreDamBundle\FileSystem\MimeGuesser;
use AnzuSystems\CoreDamBundle\FileSystem\TmpLocalFilesystem;
use AnzuSystems\CoreDamBundle\Helper\StringHelper;
use AnzuSystems\CoreDamBundle\Model\Dto\File\AdapterFile;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetFileFailedType;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetFileProcessStatus;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetStatus;
use AnzuSystems\CoreDamBundle\Model\Enum\ImageMimeTypes;
use AnzuSystems\CoreDamBundle\Repository\AssetLicenceRepository;
use AnzuSystems\CoreDamBundle\Repository\ImageFileRepository;
use App\App;
use App\Entity\User;
use App\Model\Dto\Image\PoiDto;
use App\Model\MediaApiMigrateConfig;
use App\Model\MediaApiMigration\ImageMigrationDto;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NonUniqueResultException;
use League\Flysystem\FilesystemException;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\File;
use Throwable;

final class MediaApiMigration
{
    use OutputUtilTrait;

    public const string STATUS_WAITING = 'waiting';
    public const string STATUS_MIGRATED = 'migrated';
    public const string STATUS_FAILED = 'failed';

    public const string FAIL_REASON_FILE_NOT_EXISTS = 'file_not_exist';
    public const string FAIL_REASON_UNKNOWN = 'unknown';

    private AbstractFilesystem $sourceFileSystem;
    private TmpLocalFilesystem $tmpFileSystem;
    private readonly ConnectionDecorator $damMediaApiMigConnectionDecorator;

    public function __construct(
        private readonly array $exifImageMetadata,
        private readonly array $exifCommonMetadata,
        private readonly MigrationTableIterator $migrationIterator,
        private readonly FileSystemProvider $fileSystemProvider,
        private readonly ImageManager $imageManager,
        private readonly ImageFactory $imageFactory,
        private readonly AssetFileStatusFacadeProvider $facadeProvider,
        private readonly AssetLicenceRepository $licenceRepository,
        private readonly LicenceProvider $licenceProvider,
        private readonly Connection $damMediaApiMigConnection,
        private readonly RegionOfInterestManager $regionOfInterestManager,
        private readonly PoiToRoiTransformer $poiToRoiTransformer,
        private readonly EntityManagerInterface $entityManager,
        private readonly AssetTextsProcessor $assetTextsProcessor,
        private readonly MimeGuesser $mimeGuesser,
        private readonly AssetFactory $assetFactory,
        protected readonly AssetFileMetadataManager $assetFileMetadataManager,
        protected FileAttributesProcessor $fileAttributesPostProcessor,
        protected readonly ImageFileRepository $imageFileRepository,
        protected AssetFileStorageOperator $assetFileStorageOperator,
        //        private readonly MostDominantColorProcessor $mostDominantColorProcessor,
        private readonly OptimalCropsProcessor $optimalCropsProcessor,
        private readonly Exiftool $exiftool,
    ) {
        $this->damMediaApiMigConnectionDecorator = new ConnectionDecorator($damMediaApiMigConnection);
    }

    /**
     * @throws Exception
     * @throws FilesystemException
     */
    public function migrate(MediaApiMigrateConfig $config): void
    {
        if (false === $config->allowMigrateImagesStage()) {
            return;
        }

        $sourceFileSystem = $this->fileSystemProvider->getFileSystemByStorageName($config->getMediaApiSourceStorage());
        if (false === $sourceFileSystem instanceof AbstractFilesystem) {
            throw new RuntimeException(sprintf('Source filesystem must be instance of %s', AbstractFilesystem::class));
        }

        $this->sourceFileSystem = $sourceFileSystem;
        $this->tmpFileSystem = $this->fileSystemProvider->getTmpFileSystem();

        $progress = $this->outputUtil->createProgressBar($this->migrationIterator->getCount($config));
        $progress->setFormat('debug');
        $progress->start();

        $i = 0;
        foreach ($this->migrationIterator->iterate($config) as $row) {
            $progress->advance();
            $i++;

            $this->migrateImage($row);

            if (0 === ($i % $config->getBatchSize())) {
                $this->flushAndClear();
            }

            if ($config->getLimit() && 0 === ($i % $config->getLimit())) {
                break;
            }
        }

        $this->flushAndClear();

        $progress->finish();
        $this->outputUtil->writeln('');
    }

    private function flushAndClear(): void
    {
        // todo if fail, remove saved files!
        $this->imageManager->flush();
        $this->imageManager->clear();
        $this->tmpFileSystem->clearPaths();
        $this->damMediaApiMigConnectionDecorator->flush();
    }

    /**
     * @throws FilesystemException
     */
    private function migrateImage(ImageMigrationDto $row): void
    {
        if (false === $this->sourceFileSystem->fileExists($row->getFilePath())) {
            $this->outputUtil->writeln(sprintf('File not exists (%s)', $row->getFilePath()));

            $this->updateRow(
                mediaApiId: $row->getMediaApiId(),
                status: self::STATUS_FAILED,
                failReason: self::FAIL_REASON_FILE_NOT_EXISTS,
            );

            return;
        }

        try {
            $image = $this->createImage($row);
        } catch (DuplicateAssetFileException $e) {
            $this->updateRow(
                mediaApiId: $row->getMediaApiId(),
                status: self::STATUS_MIGRATED,
                mainFileId: (string) $e->getOldAsset()->getId()
            );

            return;
        } catch (Throwable $e) {
            $this->updateRow(
                mediaApiId: $row->getMediaApiId(),
                status: self::STATUS_FAILED,
                failReason: self::FAIL_REASON_UNKNOWN,
                outputLog: $e->getMessage()
            );

            return;
        }

        if ($image->getAssetAttributes()->getStatus()->is(AssetFileProcessStatus::Processed)) {
            $this->updateRow(
                mediaApiId: $row->getMediaApiId(),
                status: self::STATUS_MIGRATED,
                mainFileId: (string) $image->getId()
            );

            return;
        }

        if ($image->getAssetAttributes()->getStatus()->is(AssetFileProcessStatus::Duplicate)) {
            $this->updateRow(
                mediaApiId: $row->getMediaApiId(),
                status: self::STATUS_MIGRATED,
                mainFileId: $image->getAssetAttributes()->getOriginAssetId()
            );

            return;
        }

        if ($image->getAssetAttributes()->getStatus()->is(AssetFileProcessStatus::Failed)) {
            $this->updateRow(
                mediaApiId: $row->getMediaApiId(),
                status: self::STATUS_FAILED,
                failReason: $image->getAssetAttributes()->getFailReason()->toString()
            );
        }
    }

    /**
     * @throws FilesystemException
     * @throws ORMException
     * @throws AssetFileProcessFailed
     * @throws DuplicateAssetFileException
     */
    private function createImage(ImageMigrationDto $row): ImageFile
    {
        $file = $this->tmpFileSystem->writeTmpFileFromFilesystem($this->sourceFileSystem, $row->getFilePath());
        $adapterFile = AdapterFile::createFromBaseFile(
            file: $file,
            filesystem: $this->tmpFileSystem
        );
        $licence = $this->licenceProvider->getLicence(
            title: 'Migration Licence',
            extSystemId: MigrationTableBuilder::CMS_EXT_ID
        );
        $image = $this->createImageFile($adapterFile, $licence);

        $this->prepareRoi($row, $file, $image);

        $author = $this->getAuthor($row);
        if ($author) {
            $image->getAsset()->getAuthors()->add($author);
        }
        $image->getAsset()->setKeywords($this->getKeywords($row));
        $image->getAssetAttributes()->setOriginFileName(pathinfo($row->getFilePath())['basename'] ?? '');
        $image->getAssetAttributes()->setOriginUrl($this->getSourceUrl($row));

        $this->storeAndProcess($image, $adapterFile);

        $this->processMetadata($image, $adapterFile, $row);
        $this->updateTrackableFields($image, $row);
        $this->updateTrackableFields($image->getAsset(), $row);

        $image->getAssetAttributes()->setStatus(AssetFileProcessStatus::Processed);

        $asset = $image->getAsset();

        $asset->getAssetFileProperties()->setWidth(
            $image->getImageAttributes()->getWidth()
        );
        $asset->getAssetFileProperties()->setHeight(
            $image->getImageAttributes()->getHeight()
        );
        $asset->getAttributes()->setStatus(AssetStatus::WithFile);

        return $image;
    }

    private function storeAndProcess(ImageFile $imageFile, AdapterFile $file): void
    {
        $this->assetFileStorageOperator->save($imageFile, $file);
        //        $this->mostDominantColorProcessor->process($imageFile, $file);
        $this->optimalCropsProcessor->process($imageFile, $file);
    }

    private function processMetadata(ImageFile $imageFile, AdapterFile $file, ImageMigrationDto $row): void
    {
        try {
            $rawMetadata = $this->exiftool->getTags($file->getRealPath());
            $metadata = $this->provideCommonMetadata($rawMetadata, $this->exifCommonMetadata);
            $metadata = array_merge(
                $metadata,
                $this->provideCommonMetadata($rawMetadata, $this->exifImageMetadata)
            );
            $imageFile->getMetadata()->setExifData($metadata);
            $imageFile->getFlags()->setProcessedMetadata(true);
        } catch (Throwable $e) {
            $this->outputUtil->writeln('Exiftool failed ' . $e->getMessage());
        }

        $imageFile->getAsset()->getMetadata()->setCustomData([
            'title' => StringHelper::parseString(
                input: $this->getValueOrExifAlt(
                    image: $imageFile,
                    value: $row->getDescription(),
                    keys: ['Title', 'Subject', 'Headline']
                ),
                length: 255
            ),
            'description' => StringHelper::parseString(
                input: $this->getValueOrExifAlt(
                    image: $imageFile,
                    value: $row->getDescription(),
                    keys: ['Description']
                ),
                length: 2_000
            ),
        ]);

        $imageFile->getAsset()->getTexts()->setDisplayTitle(
            $this->assetTextsProcessor->getAssetDisplayTitle($imageFile->getAsset())
        );
        $imageFile->getAsset()->getAssetFlags()->setDescribed(true);
    }

    /**
     * @throws AssetFileProcessFailed
     * @throws NonUniqueResultException
     * @throws DuplicateAssetFileException
     */
    private function createImageFile(AdapterFile $file, AssetLicence $licence): ImageFile
    {
        $mimeType = $this->mimeGuesser->guessMime((string) $file->getRealPath());

        if (false === in_array($mimeType, ImageMimeTypes::values(), true)) {
            throw new AssetFileProcessFailed(AssetFileFailedType::InvalidMimeType);
        }
        $checksum = MimeGuesser::checksumFromPath($file->getRealPath());
        $originAsset = $this->imageFileRepository->findProcessedByChecksumAndLicence(
            checksum: $checksum,
            licence: $licence,
        );
        if ($originAsset) {
            throw new DuplicateAssetFileException($originAsset, (new ImageFile()));
        }

        $metadata = new AssetFileMetadata();
        $this->assetFileMetadataManager->create($metadata, false);

        $assetFile = (new ImageFile());
        $assetFile
            ->setMetadata($metadata)
            ->setLicence($licence);

        $assetFile->getAssetAttributes()
            ->setMimeType($mimeType)
            ->setSize($file->getSize());

        $assetFile->getAssetAttributes()
            ->setChecksum($checksum);

        $this->assetFactory->createForAssetFile($assetFile, $licence);
        $this->imageManager->create($assetFile, false);

        return $assetFile;
    }

    private function getValueOrExifAlt(ImageFile $image, string $value, array $keys): string
    {
        $exifData = $image->getMetadata()->getExifData();
        if (false === empty($value)) {
            return $value;
        }

        foreach ($keys as $key) {
            if (false === empty($exifData[$key])) {
                return $exifData[$key];
            }
        }

        return '';
    }

    /**
     * @throws ORMException
     */
    private function updateTrackableFields(UserTrackingInterface&TimeTrackingInterface $entity, ImageMigrationDto $row): void
    {
        $createdBy = $this->entityManager->getReference(User::class, $row->getCreatedById());
        if ($createdBy) {
            $entity->setCreatedBy($createdBy);
        }
        $modifiedBy = $this->entityManager->getReference(User::class, $row->getUpdatedById());
        if ($modifiedBy) {
            $entity->setModifiedBy($modifiedBy);
        }

        if ($row->getCreatedAt()) {
            $entity->setCreatedAt($row->getCreatedAt());
        }
        if ($row->getUpdatedAt()) {
            $entity->setModifiedAt($row->getUpdatedAt());
        }
    }

    private function getSourceUrl(ImageMigrationDto $migrationDto): string
    {
        $url = $migrationDto->getSourceUrl();
        if (empty($url) || false === filter_var($url, FILTER_VALIDATE_URL)) {
            return '';
        }

        return $url;
    }

    /**
     * @throws ORMException
     */
    private function getAuthor(ImageMigrationDto $migrationDto): ?Author
    {
        if ($migrationDto->getAuthorId()) {
            return $this->entityManager->getReference(Author::class, $migrationDto->getAuthorId());
        }

        return null;
    }

    /**
     * @throws ORMException
     */
    private function getKeywords(ImageMigrationDto $migrationDto): Collection
    {
        if (empty($migrationDto->getKeywords())) {
            return new ArrayCollection();
        }

        return new ArrayCollection(
            array_map(
                fn (string $keywordId): ?Keyword => $this->entityManager->getReference(Keyword::class, $keywordId),
                $migrationDto->getKeywords()
            )
        );
    }

    private function prepareRoi(ImageMigrationDto $migrationDto, File $file, ImageFile $image): ?RegionOfInterest
    {
        if (null === $migrationDto->getFocusX() || null === $migrationDto->getFocusY()) {
            return null;
        }

        $imageSize = getimagesize((string) $file->getRealPath());

        $width = $imageSize[0] ?? App::ZERO;
        $height = $imageSize[1] ?? App::ZERO;

        if (App::ZERO === $width || App::ZERO === $height) {
            return null;
        }

        $roiDto = $this->poiToRoiTransformer->transformPoi(
            (new PoiDto(
                pointX: $migrationDto->getFocusX(),
                pointY: $migrationDto->getFocusY(),
                imageWidth: $width,
                imageHeight: $height,
            ))
        );

        $roi = (new RegionOfInterest())
            ->setPercentageHeight($roiDto->getPercentageHeight())
            ->setPercentageWidth($roiDto->getPercentageWidth())
            ->setPointX($roiDto->getPointX())
            ->setPointY($roiDto->getPointY())
            ->setTitle('Default');

        $roi->setImage($image);
        $image->getRegionsOfInterest()->add($roi);

        return $this->regionOfInterestManager->create($roi, false);
    }

    private function updateRow(
        int $mediaApiId,
        string $status,
        string $assetId = '',
        string $mainFileId = '',
        string $failReason = '',
        string $outputLog = '',
    ): void {
        $this->damMediaApiMigConnectionDecorator->prepareUpdate(
            'dam_media_api_mig',
            [
                'asset_id' => $assetId,
                'main_file_id' => $mainFileId,
                'migration_status' => $status,
                'fail_reason' => $failReason,
                'output_log' => StringHelper::parseString($outputLog, 256),
            ],
            [
                'media_api_id' => $mediaApiId,
            ]
        );
    }

    private function provideCommonMetadata(array $rawMetadata, array $allowedMetadataList): array
    {
        $metadata = [];
        foreach ($allowedMetadataList as $metadataName => $value) {
            if (isset($rawMetadata[$metadataName])) {
                $metadata[$metadataName] = $this->parseValue($rawMetadata[$metadataName]);
            }
        }

        return $metadata;
    }

    private function parseValue(string $value): string
    {
        return htmlspecialchars(strip_tags($value));
    }
}
