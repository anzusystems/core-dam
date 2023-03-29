<?php

declare(strict_types=1);

namespace App\MediaApiMigrations;

use AnzuSystems\Contracts\Entity\Interfaces\TimeTrackingInterface;
use AnzuSystems\Contracts\Entity\Interfaces\UserTrackingInterface;
use AnzuSystems\CoreDamBundle\Command\Traits\OutputUtilTrait;
use AnzuSystems\CoreDamBundle\Domain\Asset\AssetTextsProcessor;
use AnzuSystems\CoreDamBundle\Domain\AssetFile\AssetFileStatusFacadeProvider;
use AnzuSystems\CoreDamBundle\Domain\Image\ImageFactory;
use AnzuSystems\CoreDamBundle\Domain\Image\ImageManager;
use AnzuSystems\CoreDamBundle\Domain\RegionOfInterest\RegionOfInterestManager;
use AnzuSystems\CoreDamBundle\Entity\Author;
use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\CoreDamBundle\Entity\Keyword;
use AnzuSystems\CoreDamBundle\Entity\RegionOfInterest;
use AnzuSystems\CoreDamBundle\FileSystem\AbstractFilesystem;
use AnzuSystems\CoreDamBundle\FileSystem\FileSystemProvider;
use AnzuSystems\CoreDamBundle\FileSystem\TmpLocalFilesystem;
use AnzuSystems\CoreDamBundle\Helper\StringHelper;
use AnzuSystems\CoreDamBundle\Model\Dto\File\AdapterFile;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetFileProcessStatus;
use AnzuSystems\CoreDamBundle\Repository\AssetLicenceRepository;
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
use League\Flysystem\FilesystemException;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\File;
use Throwable;

final class MediaApiMigration
{
    use OutputUtilTrait;

    public const STATUS_WAITING = 'waiting';
    public const STATUS_MIGRATED = 'migrated';
    public const STATUS_FAILED = 'failed';

    public const FAIL_REASON_FILE_NOT_EXISTS = 'file_not_exist';
    public const FAIL_REASON_UNKNOWN = 'unknown';

    private AbstractFilesystem $sourceFileSystem;
    private TmpLocalFilesystem $tmpFileSystem;
    private readonly ConnectionDecorator $damMediaApiMigConnectionDecorator;

    public function __construct(
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

        $image = $this->imageFactory->createFromFile(file: $adapterFile, assetLicence: $licence);
        $image->getAsset()->getAssetFlags()->setDescribed(true);
        $image->getAssetAttributes()->setStatus(AssetFileProcessStatus::Uploaded);
        $this->prepareRoi($row, $file, $image);

        $author = $this->getAuthor($row);
        if ($author) {
            $image->getAsset()->getAuthors()->add($author);
        }
        $image->getAsset()->setKeywords($this->getKeywords($row));
        $image->getAssetAttributes()->setOriginFileName(pathinfo($row->getFilePath())['basename'] ?? '');
        $image->getAssetAttributes()->setOriginUrl($this->getSourceUrl($row));

        $this->facadeProvider->getStatusFacade($image)->storeAndProcess($image, $adapterFile);

        $image->getAsset()->getMetadata()->setCustomData([
            'title' => StringHelper::parseString(
                input: $this->getValueOrExifAlt(
                    image: $image,
                    value: $row->getDescription(),
                    keys: ['Title', 'Subject', 'Headline']
                ),
                length: 256
            ),
            'description' => StringHelper::parseString(
                input: $this->getValueOrExifAlt(
                    image: $image,
                    value: $row->getDescription(),
                    keys: ['Description']
                ),
                length: 2_000
            ),
        ]);

        $image->getAsset()->getTexts()->setDisplayTitle(
            $this->assetTextsProcessor->getAssetDisplayTitle($image->getAsset())
        );

        if ($image->getAssetAttributes()->getStatus()->isNot(AssetFileProcessStatus::Processed)) {
            $image->getAsset()->getAssetFlags()->setDescribed(false);
        }

        $this->updateTrackableFields($image, $row);
        $this->updateTrackableFields($image->getAsset(), $row);

        return $image;
    }

    private function getValueOrExifAlt(ImageFile $image, string $value, array $keys): string
    {
        $exifData = $image->getMetadata()->getExifData();
        if (false === empty($value)) {
            return $value;
        }

        foreach ($keys as $key) {
            if (isset($exifData[$key]) && false === empty($exifData[$key])) {
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
}
