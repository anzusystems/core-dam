<?php

declare(strict_types=1);

namespace App\DocumentMigration;

use AnzuSystems\Contracts\Entity\Interfaces\TimeTrackingInterface;
use AnzuSystems\Contracts\Entity\Interfaces\UserTrackingInterface;
use AnzuSystems\CoreDamBundle\Command\Traits\OutputUtilTrait;
use AnzuSystems\CoreDamBundle\Domain\AssetFile\AssetFileFactory;
use AnzuSystems\CoreDamBundle\Domain\AssetFile\AssetFileStatusFacadeProvider;
use AnzuSystems\CoreDamBundle\Domain\AssetFileRoute\AssetFileRouteFactory;
use AnzuSystems\CoreDamBundle\Domain\AssetFileRoute\AssetFileRouteManager;
use AnzuSystems\CoreDamBundle\Domain\AssetFileRoute\AssetFileRouteStorageManager;
use AnzuSystems\CoreDamBundle\Entity\Asset;
use AnzuSystems\CoreDamBundle\Entity\AssetFile;
use AnzuSystems\CoreDamBundle\Entity\AssetFileRoute;
use AnzuSystems\CoreDamBundle\Entity\AssetLicence;
use AnzuSystems\CoreDamBundle\Entity\Author;
use AnzuSystems\CoreDamBundle\Entity\Embeds\RouteUri;
use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\CoreDamBundle\Entity\Keyword;
use AnzuSystems\CoreDamBundle\Entity\VideoFile;
use AnzuSystems\CoreDamBundle\Exception\AssetFileProcessFailed;
use AnzuSystems\CoreDamBundle\Exception\DomainException;
use AnzuSystems\CoreDamBundle\Exception\DuplicateAssetFileException;
use AnzuSystems\CoreDamBundle\FileSystem\AbstractFilesystem;
use AnzuSystems\CoreDamBundle\FileSystem\FileSystemProvider;
use AnzuSystems\CoreDamBundle\FileSystem\TmpLocalFilesystem;
use AnzuSystems\CoreDamBundle\Helper\StringHelper;
use AnzuSystems\CoreDamBundle\Model\Dto\AssetFileRoute\AssetFileRouteAdmCreateDto;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetFileProcessStatus;
use AnzuSystems\CoreDamBundle\Model\Enum\RouteMode;
use AnzuSystems\CoreDamBundle\Model\Enum\RouteStatus;
use AnzuSystems\CoreDamBundle\Repository\AssetLicenceRepository;
use AnzuSystems\CoreDamBundle\Traits\IndexManagerAwareTrait;
use App\App;
use App\Domain\Image\MediaApi\UserProvider;
use App\MediaApiMigrations\AuthorProvider;
use App\MediaApiMigrations\ConnectionDecorator;
use App\MediaApiMigrations\KeywordProvider;
use App\Model\Fs1Migration\DocumentMigrationDto;
use App\Model\StaticFilesMigrateConfig;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use League\Flysystem\FilesystemException;

final class DocumentMigrator
{
    use OutputUtilTrait;
    use IndexManagerAwareTrait;

    public const string STATUS_WAITING = 'waiting';
    public const string STATUS_MIGRATED = 'migrated';
    public const string STATUS_FAILED = 'failed';

    public const string FAIL_REASON_FILE_NOT_EXISTS = 'file_not_exist';
    public const string FAIL_REASON_UNKNOWN = 'unknown';

    private const int CMS_LICENCE_ID = 100_000;
    private const int SPECTATOR_LICENCE_ID = 100_001;

    private const string ARTEMIS_DISTRIBUTION_SERVICE = 'artemis_video_cms';
    private const string YOUTUBE_DISTRIBUTION_SERVICE = 'youtube_cms_main';

    private AbstractFilesystem $sourceFileSystem;
    private TmpLocalFilesystem $tmpFileSystem;
    private readonly ConnectionDecorator $damMediaApiMigConnectionDecorator;
    private ?AssetLicence $licence = null;
    private ?AssetLicence $spectatorLicence = null;

    public function __construct(
        private readonly MigrationTableIterator $migrationTableIterator,
        private readonly AssetFileFactory $assetFileFactory,
        private readonly AssetFileStatusFacadeProvider $facadeProvider,
        private readonly UserProvider $userProvider,
        private readonly FileSystemProvider $fileSystemProvider,
        private readonly AssetLicenceRepository $licenceRepository,
        private readonly Connection $damMediaApiMigConnection,
        private readonly EntityManagerInterface $entityManager,
        private readonly KeywordProvider $keywordProvider,
        private readonly AuthorProvider $authorProvider,
        private readonly AssetFileRouteFactory $routeFactory,
        private readonly AssetFileRouteStorageManager $assetFileRouteStorageManager,
        private readonly AssetFileRouteManager $assetFileRouteManager,
    ) {
        $this->damMediaApiMigConnectionDecorator = new ConnectionDecorator($damMediaApiMigConnection);
    }

    /**
     * @throws Exception
     * @throws FilesystemException
     */
    public function migrate(StaticFilesMigrateConfig $config): void
    {
        $sourceFilesystem = $this->fileSystemProvider->getFileSystemByStorageName('general.documents');
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

            $assetFile = $this->handleMigrateAsset($row);
            $this->flushAndClear();

            if ($assetFile) {
                $this->reindex($assetFile);
                $this->entityManager->clear();
            }

            if ($config->getLimit() && $i >= $config->getLimit()) {
                break;
            }
        }

        $progress->finish();
        $this->outputUtil->writeln('');
    }

    private function handleMigrateAsset(DocumentMigrationDto $dto): ?AssetFile
    {
        if (false === $this->sourceFileSystem->fileExists($dto->getFilePath())) {
            $this->outputUtil->writeln(sprintf('File not exists (%s)', $dto->getFilePath()));
            $this->updateRow(
                mediaApiId: $dto->getId(),
                status: self::STATUS_FAILED,
                failReason: self::FAIL_REASON_FILE_NOT_EXISTS,
            );

            return null;
        }

        try {
            $assetFile = $this->migrateAsset($dto);
        } catch (DuplicateAssetFileException $e) {
            $this->updateRow(
                mediaApiId: $dto->getId(),
                status: self::STATUS_MIGRATED,
                asset_id: (string) $e->getOldAsset()->getId()
            );

            return null;
        } catch (AssetFileProcessFailed $e) {
            $this->outputUtil->error(sprintf(
                'Failed to migrate (%s) with message (%s) failed type (%s)',
                $dto->getId(),
                $e->getMessage(),
                $e->getAssetFileFailedType()->toString()
            ));
            $this->updateRow(
                mediaApiId: $dto->getId(),
                status: self::STATUS_FAILED,
                failReason: self::FAIL_REASON_UNKNOWN,
                outputLog: sprintf('%s_%s', $e->getMessage(), $e->getAssetFileFailedType()->toString())
            );

            return null;
        } catch (\Throwable $e) {
            $this->outputUtil->error(sprintf(
                'Failed to migrate (%s) with message (%s)',
                $dto->getId(),
                $e->getMessage(),
            ));
            $this->updateRow(
                mediaApiId: $dto->getId(),
                status: self::STATUS_FAILED,
                failReason: self::FAIL_REASON_UNKNOWN,
                outputLog: $e->getMessage()
            );

            return null;
        }

        if ($assetFile->getAssetAttributes()->getStatus()->is(AssetFileProcessStatus::Processed)) {
            $this->updateRow(
                mediaApiId: $dto->getId(),
                status: self::STATUS_MIGRATED,
                asset_id: (string) $assetFile->getId()
            );

            return $assetFile;
        }

        if ($assetFile->getAssetAttributes()->getStatus()->is(AssetFileProcessStatus::Duplicate)) {
            $this->updateRow(
                mediaApiId: $dto->getId(),
                status: self::STATUS_MIGRATED,
                asset_id: $assetFile->getAssetAttributes()->getOriginAssetId()
            );

            return null;
        }

        if ($assetFile->getAssetAttributes()->getStatus()->is(AssetFileProcessStatus::Failed)) {
            $this->updateRow(
                mediaApiId: $dto->getId(),
                status: self::STATUS_FAILED,
                failReason: $assetFile->getAssetAttributes()->getFailReason()->toString()
            );
        }

        return null;
    }

    private function migrateAsset(DocumentMigrationDto $dto): AssetFile
    {
        $licence = $this->getLicence($dto);
        $assetFile = $this->assetFileFactory->createAssetFileForStorage(
            storageName: 'general.documents',
            filePath: $dto->getFilePath(),
            licence: $licence
        );

        $assetFile->getAsset()->getMetadata()->setCustomData([
            'title' => $dto->getFileName(),
            'description' => 'https://artemis.smedata.sk/usmedata' . $dto->getFilePath(),
        ]);

        $this->setupKeywords($assetFile->getAsset(), $dto);
        $this->setupAuthors($assetFile->getAsset(), $dto);

        $this->updateTrackableFields($assetFile, $dto);
        $this->updateTrackableFields($assetFile->getMetadata(), $dto);
        $this->updateTrackableFields($assetFile, $dto);
        $this->updateTrackableFields($assetFile->getMetadata(), $dto);

        $this->facadeProvider->getStatusFacade($assetFile)->storeAndProcess($assetFile);
        $this->setupRouting($assetFile, $dto);

        return $assetFile;
    }

    private function setupRouting(AssetFile $assetFile, DocumentMigrationDto $dto): void
    {
        if ($assetFile instanceof VideoFile) {
            return;
        }

        $route = $assetFile instanceof ImageFile
            ? $this->routeFactory->createForImage($assetFile)
            : $this->routeFactory->createFromDto($assetFile, (new AssetFileRouteAdmCreateDto()));

        if ($route->getMode()->is(RouteMode::StorageCopy)) {
            $this->assetFileRouteStorageManager->writeRouteFile($assetFile, $route);
        }

        $this->setupLegacyRoute($assetFile, $dto);
    }

    private function setupLegacyRoute(AssetFile $file, DocumentMigrationDto $dto): void
    {
        $route = (new AssetFileRoute())
            ->setUri(
                (new RouteUri())
                    ->setSlug('')
                    ->setMain(false)
                    ->setPath('artemis/usmedata' . $dto->getFilePath())
            )
            ->setStatus(RouteStatus::Active)
            ->setMode(RouteMode::Direct)
        ;
        $this->assetFileRouteManager->create($route, false);

        $route->setTargetAssetFile($file);
        $file->getRoutes()->add($route);
    }

    private function setupAuthors(Asset $asset, DocumentMigrationDto $dto): void
    {
        if (empty($dto->getAuthor())) {
            return;
        }

        $authorId = $this->authorProvider->getAuthor(
            title: $dto->getAuthor(),
            extSystemId: (int) $this->getLicence($dto)->getExtSystem()->getId()
        );

        $author = $this->entityManager->find(Author::class, $authorId);
        if ($author) {
            $asset->getAuthors()->add($author);
        }
    }

    private function setupKeywords(Asset $asset, DocumentMigrationDto $dto): void
    {
        foreach (['migrdoc', $dto->getCmsDomain()] as $keywordTitle) {
            $keywordId = $this->keywordProvider->getKeyword(
                title: $keywordTitle,
                extSystemId: (int) $this->getLicence($dto)->getExtSystem()->getId()
            );

            $keyword = $this->entityManager->find(Keyword::class, $keywordId);
            if ($keyword) {
                $asset->getKeywords()->add($keyword);
            }
        }
    }

    private function getLicence(DocumentMigrationDto $dto): AssetLicence
    {
        if (null === $this->spectatorLicence) {
            $this->spectatorLicence = $this->licenceRepository->find(self::SPECTATOR_LICENCE_ID);
        }
        if (null === $this->licence) {
            $this->licence = $this->licenceRepository->find(self::CMS_LICENCE_ID);
        }

        if (2 === $dto->getCmsSection()) {
            return $this->spectatorLicence ?? throw new DomainException('Spectator licence not found');
        }

        return $this->licence ?? throw new DomainException('CMS licence not found');
    }

    private function updateRow(
        int $mediaApiId,
        string $status,
        string $asset_id = '',
        string $failReason = '',
        string $outputLog = '',
    ): void {
        $this->damMediaApiMigConnectionDecorator->prepareUpdate(
            'dam_document_mig',
            [
                'asset_file_id' => $asset_id,
                'migration_status' => $status,
                'fail_reason' => $failReason,
                'output_log' => StringHelper::parseString($outputLog, 256),
            ],
            [
                'id' => $mediaApiId,
            ]
        );
    }

    private function flushAndClear(): void
    {
        $this->entityManager->flush();
        $this->entityManager->clear();
        $this->tmpFileSystem->clearPaths();
        $this->damMediaApiMigConnectionDecorator->flush();
        $this->licence = null;
        $this->spectatorLicence = null;
        $this->authorProvider->clearCache();
        $this->keywordProvider->clearCache();
    }

    private function reindex(AssetFile $assetFile): void
    {
        $this->indexManager->index($assetFile->getAsset());
        if ($assetFile instanceof VideoFile && $assetFile->getImagePreview()) {
            $this->indexManager->index($assetFile->getImagePreview()->getImageFile()->getAsset());
        }
    }

    /**
     * @throws ORMException
     */
    private function updateTrackableFields(UserTrackingInterface&TimeTrackingInterface $entity, DocumentMigrationDto $dto): void
    {
        $userId = $dto->getCreatedById() > 0 ? $dto->getCreatedById() : App::getUserIdAnonymous();

        $createdBy = $this->userProvider->getUser($userId);
        $entity->setCreatedBy($createdBy);
        $entity->setModifiedBy($createdBy);

        $entity->setCreatedAt($dto->getCreatedAt());
        $entity->setModifiedAt($dto->getCreatedAt());
    }
}
