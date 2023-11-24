<?php

declare(strict_types=1);

namespace App\Fs1Migration;

use AnzuSystems\Contracts\Entity\Interfaces\TimeTrackingInterface;
use AnzuSystems\Contracts\Entity\Interfaces\UserTrackingInterface;
use AnzuSystems\CoreDamBundle\Command\Traits\OutputUtilTrait;
use AnzuSystems\CoreDamBundle\Domain\Asset\AssetFactory;
use AnzuSystems\CoreDamBundle\Domain\Asset\AssetPropertiesRefresher;
use AnzuSystems\CoreDamBundle\Domain\Asset\AssetTextsProcessor;
use AnzuSystems\CoreDamBundle\Domain\AssetFile\FileProcessor\AssetFileStorageOperator;
use AnzuSystems\CoreDamBundle\Domain\AssetFileMetadata\AssetFileMetadataManager;
use AnzuSystems\CoreDamBundle\Domain\Image\ImageFactory;
use AnzuSystems\CoreDamBundle\Domain\Image\ImageManager;
use AnzuSystems\CoreDamBundle\Domain\ImagePreview\ImagePreviewFactory;
use AnzuSystems\CoreDamBundle\Domain\Video\VideoManager;
use AnzuSystems\CoreDamBundle\Domain\Video\VideoStatusFacade;
use AnzuSystems\CoreDamBundle\Domain\YoutubeDistribution\YoutubeAbstractDistributionManager;
use AnzuSystems\CoreDamBundle\Entity\Asset;
use AnzuSystems\CoreDamBundle\Entity\AssetFileMetadata;
use AnzuSystems\CoreDamBundle\Entity\AssetLicence;
use AnzuSystems\CoreDamBundle\Entity\Author;
use AnzuSystems\CoreDamBundle\Entity\Embeds\YoutubeTexts;
use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\CoreDamBundle\Entity\Keyword;
use AnzuSystems\CoreDamBundle\Entity\VideoFile;
use AnzuSystems\CoreDamBundle\Entity\YoutubeDistribution;
use AnzuSystems\CoreDamBundle\Exception\AssetFileProcessFailed;
use AnzuSystems\CoreDamBundle\Exception\DomainException;
use AnzuSystems\CoreDamBundle\Exception\DuplicateAssetFileException;
use AnzuSystems\CoreDamBundle\Exiftool\Exiftool;
use AnzuSystems\CoreDamBundle\Ffmpeg\FfmpegService;
use AnzuSystems\CoreDamBundle\FileSystem\AbstractFilesystem;
use AnzuSystems\CoreDamBundle\FileSystem\FileSystemProvider;
use AnzuSystems\CoreDamBundle\FileSystem\MimeGuesser;
use AnzuSystems\CoreDamBundle\FileSystem\TmpLocalFilesystem;
use AnzuSystems\CoreDamBundle\Helper\StringHelper;
use AnzuSystems\CoreDamBundle\Model\Dto\File\AdapterFile;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetFileFailedType;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetFileProcessStatus;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetStatus;
use AnzuSystems\CoreDamBundle\Model\Enum\DistributionProcessStatus;
use AnzuSystems\CoreDamBundle\Model\Enum\ImageMimeTypes;
use AnzuSystems\CoreDamBundle\Model\Enum\VideoMimeTypes;
use AnzuSystems\CoreDamBundle\Repository\AssetLicenceRepository;
use AnzuSystems\CoreDamBundle\Repository\AuthorRepository;
use AnzuSystems\CoreDamBundle\Repository\DistributionCategoryRepository;
use AnzuSystems\CoreDamBundle\Repository\ImageFileRepository;
use AnzuSystems\CoreDamBundle\Repository\VideoFileRepository;
use AnzuSystems\CoreDamBundle\Repository\VideoShowEpisodeRepository;
use AnzuSystems\CoreDamBundle\Traits\IndexManagerAwareTrait;
use App\App;
use App\Distribution\Modules\ArtemisMediaDistributionCustomDataFactory;
use App\Domain\ArtemisVideoDistribution\ArtemisVideoDistributionManager;
use App\Entity\ArtemisVideoDistribution;
use App\Entity\Embeds\ArtemisVideoTexts;
use App\Entity\User;
use App\MediaApiMigrations\ConnectionDecorator;
use App\Model\Fs1ApiMigrateConfig;
use App\Model\Fs1Migration\VideoMigrationDto;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NonUniqueResultException;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemException;
use Throwable;

final class VideoMigrator
{
    use OutputUtilTrait;
    use IndexManagerAwareTrait;

    public const STATUS_WAITING = 'waiting';
    public const STATUS_MIGRATED = 'migrated';
    public const STATUS_FAILED = 'failed';

    public const FAIL_REASON_FILE_NOT_EXISTS = 'file_not_exist';
    public const FAIL_REASON_UNKNOWN = 'unknown';

    private const LICENCE_ID = 100_000;
    private const ARTEMIS_DISTRIBUTION_SERVICE = 'artemis_video_cms';
    private const YOUTUBE_DISTRIBUTION_SERVICE = 'youtube_cms_main';

    private AbstractFilesystem $sourceFileSystem;
    private TmpLocalFilesystem $tmpFileSystem;
    private readonly ConnectionDecorator $damMediaApiMigConnectionDecorator;

    public function __construct(
        private readonly array $exifImageMetadata,
        private readonly array $exifCommonMetadata,
        private readonly MigrationTableIterator $migrationTableIterator,
        private readonly FileSystemProvider $fileSystemProvider,
        private readonly MimeGuesser $mimeGuesser,
        private readonly VideoFileRepository $videoFileRepository,
        private readonly AssetFileMetadataManager $assetFileMetadataManager,
        private readonly AssetFactory $assetFactory,
        private readonly VideoManager $videoManager,
        private readonly AssetLicenceRepository $licenceRepository,
        private readonly AuthorRepository $authorRepository,
        private readonly Connection $damMediaApiMigConnection,
        private AssetFileStorageOperator $assetFileStorageOperator,
        private readonly EntityManagerInterface $entityManager,
        private readonly AssetTextsProcessor $assetTextsProcessor,
        private readonly FfmpegService $ffmpegService,
        private readonly Exiftool $exiftool,
        private readonly ArtemisVideoDistributionManager $distributionManager,
        private readonly YoutubeAbstractDistributionManager $youtubeDistributionManager,
        private readonly ImagePreviewFactory $imagePreviewFactory,
        private readonly VideoStatusFacade $videoStatusFacade,
        private readonly ImageFileRepository $imageFileRepository,
        private readonly ImageFactory $imageFactory,
        private readonly AssetPropertiesRefresher $propertiesRefresher,
        private readonly VideoShowEpisodeRepository $videoShowEpisodeRepository,
        private readonly DistributionCategoryRepository $distributionCategoryRepository,
        private readonly ImageManager $imageManager,
    ) {
        $this->damMediaApiMigConnectionDecorator = new ConnectionDecorator($damMediaApiMigConnection);
    }

    /**
     * @throws Exception
     * @throws FilesystemException
     */
    public function migrate(Fs1ApiMigrateConfig $config): void
    {
        $sourceFilesystem = $this->fileSystemProvider->getFileSystemByStorageName('cms.media_api.fs1_storage');
        if (null === $sourceFilesystem) {
            throw new DomainException('Filesystem not exist');
        }
        $this->sourceFileSystem = $sourceFilesystem;
        $this->tmpFileSystem = $this->fileSystemProvider->getTmpFileSystem();

        $progress = $this->outputUtil->createProgressBar($this->migrationTableIterator->getCount($config));
        $progress->setFormat('debug');
        $progress->start();

        $i = 0;
        foreach ($this->migrationTableIterator->iterate($config) as $row) {
            $i++;
            $progress->advance();
            $video = $this->migrateVideo($row);
            $this->flushAndClear();
            if ($video) {
                $this->reindex($video);
                $this->entityManager->clear();
            }

            if ($config->getLimit() && $i >= $config->getLimit()) {
                break;
            }
        }

        $progress->finish();
        $this->outputUtil->writeln('');
    }

    private function reindex(VideoFile $video): void
    {
        $this->indexManager->index($video->getAsset());
        if ($video->getImagePreview()) {
            $this->indexManager->index($video->getImagePreview()->getImageFile()->getAsset());
        }
    }

    private function migrateVideo(VideoMigrationDto $dto): ?VideoFile
    {
        $path = $this->getPath($dto);

        if (empty($path)) {
            $this->outputUtil->writeln(sprintf('File not exists (%s)', $dto->getFilePath()));
            $this->updateRow(
                mediaApiId: $dto->getMediaId(),
                status: self::STATUS_FAILED,
                failReason: self::FAIL_REASON_FILE_NOT_EXISTS,
            );

            return null;
        }

        try {
            $video = $this->createVideo($dto, $path);
        } catch (DuplicateAssetFileException $e) {
            $this->updateRow(
                mediaApiId: $dto->getMediaId(),
                status: self::STATUS_MIGRATED,
                asset_id: (string) $e->getOldAsset()->getId()
            );

            return null;
        } catch (AssetFileProcessFailed $e) {
            $this->outputUtil->error(sprintf(
                'Failed to migrate (%s) with message (%s) failed type (%s)',
                $dto->getMediaId(),
                $e->getMessage(),
                $e->getAssetFileFailedType()->toString()
            ));
            $this->updateRow(
                mediaApiId: $dto->getMediaId(),
                status: self::STATUS_FAILED,
                failReason: self::FAIL_REASON_UNKNOWN,
                outputLog: sprintf('%s_%s', $e->getMessage(), $e->getAssetFileFailedType()->toString())
            );

            return null;
        } catch (Throwable $e) {
            $this->outputUtil->error(sprintf(
                'Failed to migrate (%s) with message (%s)',
                $dto->getMediaId(),
                $e->getMessage(),
            ));
            $this->updateRow(
                mediaApiId: $dto->getMediaId(),
                status: self::STATUS_FAILED,
                failReason: self::FAIL_REASON_UNKNOWN,
                outputLog: $e->getMessage()
            );

            return null;
        }

        if ($video->getAssetAttributes()->getStatus()->is(AssetFileProcessStatus::Processed)) {
            $this->updateRow(
                mediaApiId: $dto->getMediaId(),
                status: self::STATUS_MIGRATED,
                asset_id: (string) $video->getId()
            );

            return $video;
        }

        if ($video->getAssetAttributes()->getStatus()->is(AssetFileProcessStatus::Duplicate)) {
            $this->updateRow(
                mediaApiId: $dto->getMediaId(),
                status: self::STATUS_MIGRATED,
                asset_id: $video->getAssetAttributes()->getOriginAssetId()
            );

            return null;
        }

        if ($video->getAssetAttributes()->getStatus()->is(AssetFileProcessStatus::Failed)) {
            $this->updateRow(
                mediaApiId: $dto->getMediaId(),
                status: self::STATUS_FAILED,
                failReason: $video->getAssetAttributes()->getFailReason()->toString()
            );
        }

        return null;
    }

    private function getPath(VideoMigrationDto $dto): string
    {
        $path = $this->sourceFileSystem->fileExists($dto->getFilePathHd())
            ? $dto->getFilePathHd()
            : ($this->sourceFileSystem->fileExists($dto->getFilePath()) ? $dto->getFilePath() : '')
        ;

        if (false === empty($path)) {
            return $path;
        }

        if ($this->sourceFileSystem->directoryExists($dto->getDir())) {
            /** @var FileAttributes $item */
            foreach ($this->sourceFileSystem->listContents($dto->getDir()) as $item) {
                if (in_array($item->mimeType(), VideoMimeTypes::CHOICES, true)) {
                    return $item->path();
                }

                if ('application/octet-stream' === $item->mimeType()) {
                    return $item->path();
                }
            }
        }

        return '';
    }

    private function createVideo(VideoMigrationDto $dto, string $path): VideoFile
    {
        $file = $this->tmpFileSystem->writeTmpFileFromFilesystem($this->sourceFileSystem, $path);
        $adapterFile = AdapterFile::createFromBaseFile(
            file: $file,
            filesystem: $this->tmpFileSystem
        );
        $licence = $this->licenceRepository->find(self::LICENCE_ID);
        if (null === $licence) {
            throw new DomainException('Licence not found');
        }

        $videoFile = $this->createVideoFile($adapterFile, $licence);
        $asset = $videoFile->getAsset();

        $this->setupAuthors($asset, $dto);
        $this->setupKeywords($asset, $dto);
        $this->setupAssetAttributes($videoFile, $path);

        $this->assetFileStorageOperator->save($videoFile, $adapterFile);

        $this->ffmpegService->populateVideoParams($videoFile, $file);
        $this->processMetadata($videoFile, $adapterFile, $dto);
        $this->createImagePreview($videoFile, $dto, $adapterFile);

        $youtubeDistribution = $this->setupYoutubeDistribution($videoFile, $dto);
        $this->setupArtemisDistribution($videoFile, $dto, $youtubeDistribution);
        $this->setEpisode($videoFile, $dto);
        $this->setCategory($videoFile, $dto);

        $videoFile->getAssetAttributes()->setStatus(AssetFileProcessStatus::Processed);
        $asset->getAttributes()->setStatus(AssetStatus::WithFile);

        $distributedInServices = [self::ARTEMIS_DISTRIBUTION_SERVICE];
        if ($youtubeDistribution) {
            $distributedInServices[] = 'youtube_cms_main';
        }

        $asset->getAssetFileProperties()
            ->setHeight($videoFile->getAttributes()->getHeight())
            ->setWidth($videoFile->getAttributes()->getWidth())
            ->setSlotNames(['default'])
            ->setDistributesInServices($distributedInServices)
        ;

        $this->updateTrackableFields($videoFile, $dto);
        $this->updateTrackableFields($videoFile->getMetadata(), $dto);
        $this->updateTrackableFields($asset, $dto);
        $this->updateTrackableFields($asset->getMetadata(), $dto);

        $imageFile = $videoFile->getImagePreview()?->getImageFile();
        if ($imageFile) {
            $this->updateTrackableFields($imageFile, $dto);
            $this->updateTrackableFields($imageFile->getMetadata(), $dto);
            $this->updateTrackableFields($imageFile->getAsset(), $dto);
            $this->updateTrackableFields($imageFile->getAsset()->getMetadata(), $dto);
        }

        return $videoFile;
    }

    private function setCategory(VideoFile $videoFile, VideoMigrationDto $dto): void
    {
        if (empty($dto->getCategoryUuid())) {
            return;
        }

        $category = $this->distributionCategoryRepository->find($dto->getCategoryUuid());
        if (null === $category) {
            return;
        }

        $videoFile->getAsset()->setDistributionCategory($category);
    }

    private function setEpisode(VideoFile $videoFile, VideoMigrationDto $dto): void
    {
        if (empty($dto->getEpisodeUuid())) {
            return;
        }

        $episode = $this->videoShowEpisodeRepository->find($dto->getEpisodeUuid());
        if (null === $episode) {
            return;
        }

        /** @psalm-suppress InvalidArgument */
        $videoFile->getAsset()->setVideoEpisodes(new ArrayCollection([$episode]));
        $episode->setAsset($videoFile->getAsset());
    }

    private function createImagePreview(VideoFile $videoFile, VideoMigrationDto $dto, AdapterFile $adapterFile): void
    {
        $imageFile = null;
        if ($this->sourceFileSystem->fileExists($dto->getFilePathImage())) {
            try {
                $imageFile = $this->createImagePreviewFromImage($dto);
            } catch (Throwable $e) {
                $this->outputUtil->error(sprintf(
                    'Invalid preview image (%s)',
                    $e->getMessage()
                ));
                $imageFile = $this->createImagePreviewFromVideo($videoFile, $adapterFile);
            }
        }

        if (null === $imageFile) {
            $imageFile = $this->createImagePreviewFromVideo($videoFile, $adapterFile);
        }

        $imageFile->getAsset()->getAssetFileProperties()
            ->setHeight($imageFile->getImageAttributes()->getHeight())
            ->setWidth($imageFile->getImageAttributes()->getWidth())
            ->setSlotNames(['default'])
        ;

        $imageFile->getAsset()->getAssetFlags()->setGeneratedBySystem(true);

        $imagePreview = $this->imagePreviewFactory->createFromImageFile(
            imageFile: $imageFile,
            flush: false
        );

        $videoFile->setImagePreview($imagePreview);
    }

    /**
     * @throws DuplicateAssetFileException
     * @throws ORMException
     * @throws FilesystemException
     * @throws NonUniqueResultException
     * @throws AssetFileProcessFailed
     */
    private function createImagePreviewFromImage(VideoMigrationDto $dto): ImageFile
    {
        $file = $this->tmpFileSystem->writeTmpFileFromFilesystem($this->sourceFileSystem, $dto->getFilePathImage());

        $adapterFile = AdapterFile::createFromBaseFile(
            file: $file,
            filesystem: $this->tmpFileSystem
        );
        $licence = $this->licenceRepository->find(self::LICENCE_ID);
        if (null === $licence) {
            throw new DomainException('Licence not found');
        }

        return $this->createImageFile($adapterFile, $licence);
    }

    private function createImagePreviewFromVideo(VideoFile $videoFile, AdapterFile $file): ImageFile
    {
        return $this->videoStatusFacade->getPreviewImageFile(
            videoFile: $videoFile,
            file: $file
        );
    }

    private function setupYoutubeDistribution(VideoFile $videoFile, VideoMigrationDto $dto): ?YoutubeDistribution
    {
        $asset = $videoFile->getAsset();

        if (empty($dto->getYoutubeCode())) {
            return null;
        }

        $distribution = (new YoutubeDistribution())
            ->setDistributionService(self::YOUTUBE_DISTRIBUTION_SERVICE)
            ->setAssetId((string) $videoFile->getAsset()->getId())
            ->setAssetFileId((string) $videoFile->getId())
            ->setTexts(
                (new YoutubeTexts())
                    ->setKeywords(
                        $asset->getKeywords()->map(
                            fn (Keyword $keyword): string => $keyword->getName()
                        )->toArray()
                    )
                    ->setTitle(StringHelper::parseString($dto->getTitle(), 100))
                    ->setDescription(StringHelper::parseString($dto->getTitle(), 5_000))
            )
            ->setExtId($dto->getYoutubeCode())
        ;

        $this->youtubeDistributionManager->create($distribution);
        $distribution->setStatus(DistributionProcessStatus::Distributed);
        // create thumbnail url

        return $distribution;
    }

    private function setupArtemisDistribution(
        VideoFile $videoFile,
        VideoMigrationDto $dto,
        ?YoutubeDistribution $youtubeDistribution = null
    ): void {
        $asset = $videoFile->getAsset();

        $distribution = (new ArtemisVideoDistribution())
            ->setDistributionService(self::ARTEMIS_DISTRIBUTION_SERVICE)
            ->setAssetId((string) $videoFile->getAsset()->getId())
            ->setAssetFileId((string) $videoFile->getId())
            ->setTexts(
                (new ArtemisVideoTexts())
                    ->setKeywords(
                        $asset->getKeywords()->map(
                            fn (Keyword $keyword): string => $keyword->getName()
                        )->toArray()
                    )
                    ->setAuthors(
                        $asset->getAuthors()->map(
                            fn (Author $keyword): string => $keyword->getName()
                        )->toArray()
                    )
                    ->setTitle(StringHelper::parseString($dto->getTitle(), 100))
                    ->setDescription(StringHelper::parseString($dto->getTitle(), 5_000))
            )
        ;
        $distribution->setStatus(DistributionProcessStatus::Distributed);
        $distribution->setDistributionData([
            ArtemisMediaDistributionCustomDataFactory::MEDIA_ADMIN_URL => [
                'type' => 'url',
                'value' => $dto->getMediaUrl(),
            ],
            ArtemisMediaDistributionCustomDataFactory::ARTICLE_WEB_URL => [
                'type' => 'url',
                'value' => $dto->getArticleUrl(),
            ],
        ]);
        $distribution->setPublishAt($dto->getPubDate());
        if ($youtubeDistribution) {
            /** @psalm-suppress InvalidArgument */
            $distribution->setBlockedBy(new ArrayCollection([$youtubeDistribution]));
            /** @psalm-suppress InvalidArgument */
            $youtubeDistribution->setBlocks(new ArrayCollection([$distribution]));
        }
        $distribution->setExtId((string) $dto->getMediaId());
        $this->distributionManager->create($distribution, false);
    }

    private function processMetadata(VideoFile $videoFile, AdapterFile $file, VideoMigrationDto $dto): void
    {
        try {
            $rawMetadata = $this->exiftool->getTags($file->getRealPath());
            $metadata = $this->provideCommonMetadata($rawMetadata, $this->exifCommonMetadata);
            $videoFile->getMetadata()->setExifData($metadata);
            $videoFile->getFlags()->setProcessedMetadata(true);
        } catch (Throwable $e) {
            $this->outputUtil->writeln('Exiftool failed ' . $e->getMessage());
        }

        $videoFile->getAsset()->getMetadata()->setCustomData([
            'title' => $dto->getTitle(),
        ]);

        $videoFile->getAsset()->getTexts()->setDisplayTitle(
            $this->assetTextsProcessor->getAssetDisplayTitle($videoFile->getAsset())
        );
        $videoFile->getAsset()->getAssetFlags()->setDescribed(true);
    }

    private function setupAssetAttributes(VideoFile $videoFile, string $path): void
    {
        $videoFile->getAssetAttributes()->setOriginFileName(pathinfo($path)['basename'] ?? '');
    }

    private function setupAuthors(Asset $asset, VideoMigrationDto $dto): void
    {
        $asset->setAuthors(
            new ArrayCollection(
                array_values(
                    array_filter(
                        array_map(
                            fn (string $authorId): ?Author => $this->entityManager->find(Author::class, $authorId),
                            $dto->getAuthorIds()
                        ),
                        fn (?Author $author): bool => $author instanceof Author
                    )
                )
            )
        );
    }

    private function setupKeywords(Asset $asset, VideoMigrationDto $dto): void
    {
        $asset->setKeywords(
            new ArrayCollection(
                array_values(
                    array_filter(
                        array_map(
                            fn (string $keywordId): ?Keyword => $this->entityManager->find(Keyword::class, $keywordId),
                            $dto->getKeywordIds()
                        ),
                        fn (?Keyword $author): bool => $author instanceof Keyword
                    )
                )
            )
        );
    }

    /**
     * @throws AssetFileProcessFailed
     * @throws NonUniqueResultException
     * @throws DuplicateAssetFileException
     */
    private function createVideoFile(AdapterFile $file, AssetLicence $licence): VideoFile
    {
        $mimeType = $this->mimeGuesser->guessMime((string) $file->getRealPath());

        if (false === in_array($mimeType, VideoMimeTypes::values(), true)) {
            throw new AssetFileProcessFailed(AssetFileFailedType::InvalidMimeType);
        }
        $checksum = MimeGuesser::checksumFromPath($file->getRealPath());
        $originAsset = $this->videoFileRepository->findProcessedByChecksumAndLicence(
            checksum: $checksum,
            licence: $licence,
        );

        if ($originAsset) {
            throw new DuplicateAssetFileException($originAsset, (new VideoFile()));
        }

        $metadata = new AssetFileMetadata();
        $this->assetFileMetadataManager->create($metadata, false);

        $assetFile = (new VideoFile());
        $assetFile
            ->setMetadata($metadata)
            ->setLicence($licence);

        $assetFile->getAssetAttributes()
            ->setMimeType($mimeType)
            ->setSize($file->getSize());

        $assetFile->getAssetAttributes()
            ->setChecksum($checksum);

        $this->assetFactory->createForAssetFile($assetFile, $licence);
        $this->videoManager->create($assetFile, false);

        return $assetFile;
    }

    private function flushAndClear(): void
    {
        $this->entityManager->flush();
        $this->entityManager->clear();
        $this->tmpFileSystem->clearPaths();
        $this->damMediaApiMigConnectionDecorator->flush();
    }

    private function flush(): void
    {
        $this->videoManager->flush();
        $this->damMediaApiMigConnectionDecorator->flush();
    }

    private function updateRow(
        int $mediaApiId,
        string $status,
        string $asset_id = '',
        string $failReason = '',
        string $outputLog = '',
    ): void {
        $this->damMediaApiMigConnectionDecorator->prepareUpdate(
            'dam_fs1_mig',
            [
                'asset_id' => $asset_id,
                'migration_status' => $status,
                'fail_reason' => $failReason,
                'output_log' => StringHelper::parseString($outputLog, 256),
            ],
            [
                'media_id' => $mediaApiId,
            ]
        );
    }

    /**
     * @throws ORMException
     */
    private function updateTrackableFields(UserTrackingInterface&TimeTrackingInterface $entity, VideoMigrationDto $row): void
    {
        $userId = $row->getCreatedById() > 0 ? $row->getCreatedById() : App::getUserIdAnonymous();

        $createdBy = $this->entityManager->getReference(User::class, $userId);
        if ($createdBy) {
            $entity->setCreatedBy($createdBy);
        }
        $modifiedBy = $this->entityManager->getReference(User::class, $userId);
        if ($modifiedBy) {
            $entity->setModifiedBy($modifiedBy);
        }

        $entity->setCreatedAt($row->getCreatedAt());
        $entity->setModifiedAt($row->getModifiedAt());
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

    /**
     * @throws AssetFileProcessFailed
     * @throws NonUniqueResultException
     * @throws DuplicateAssetFileException
     */
    private function createImageFile(AdapterFile $file, AssetLicence $licence): ImageFile
    {
        $checksum = MimeGuesser::checksumFromPath($file->getRealPath());
        /** @var ImageFile|null $originAsset */
        $originAsset = $this->imageFileRepository->findProcessedByChecksumAndLicence(
            checksum: $checksum,
            licence: $licence,
        );
        if ($originAsset) {
            return $originAsset;
        }

        $mimeType = $this->mimeGuesser->guessMime((string) $file->getRealPath());
        if (false === in_array($mimeType, ImageMimeTypes::values(), true)) {
            throw new AssetFileProcessFailed(AssetFileFailedType::InvalidMimeType);
        }

        /** @var ImageFile $originAsset */
        $originAsset = $this->imageFactory->createAndProcessFromFile($file, $licence, true);

        return $originAsset;
    }
}
