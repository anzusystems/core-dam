<?php

declare(strict_types=1);

namespace App\JwVideoMigration;

use AnzuSystems\Contracts\Entity\Interfaces\TimeTrackingInterface;
use AnzuSystems\CoreDamBundle\Command\Traits\OutputUtilTrait;
use AnzuSystems\CoreDamBundle\Domain\Asset\AssetFactory;
use AnzuSystems\CoreDamBundle\Domain\AssetFile\AssetFileStatusFacadeProvider;
use AnzuSystems\CoreDamBundle\Domain\AssetFile\AssetFileStatusManager;
use AnzuSystems\CoreDamBundle\Domain\AssetFileMetadata\AssetFileMetadataManager;
use AnzuSystems\CoreDamBundle\Domain\JwDistribution\JwDistributionManager;
use AnzuSystems\CoreDamBundle\Domain\Video\VideoManager;
use AnzuSystems\CoreDamBundle\Domain\YoutubeDistribution\YoutubeAbstractDistributionManager;
use AnzuSystems\CoreDamBundle\Entity\AssetFileMetadata;
use AnzuSystems\CoreDamBundle\Entity\AssetLicence;
use AnzuSystems\CoreDamBundle\Entity\Embeds\JwTexts;
use AnzuSystems\CoreDamBundle\Entity\Embeds\YoutubeTexts;
use AnzuSystems\CoreDamBundle\Entity\JwDistribution;
use AnzuSystems\CoreDamBundle\Entity\VideoFile;
use AnzuSystems\CoreDamBundle\Entity\YoutubeDistribution;
use AnzuSystems\CoreDamBundle\Exception\DomainException;
use AnzuSystems\CoreDamBundle\Exception\RuntimeException;
use AnzuSystems\CoreDamBundle\FileSystem\FileSystemProvider;
use AnzuSystems\CoreDamBundle\Helper\StringHelper;
use AnzuSystems\CoreDamBundle\Messenger\Message\AssetRefreshPropertiesMessage;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetFileCreateStrategy;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetFileProcessStatus;
use AnzuSystems\CoreDamBundle\Model\Enum\DistributionProcessStatus;
use AnzuSystems\CoreDamBundle\Repository\AssetLicenceRepository;
use AnzuSystems\CoreDamBundle\Repository\JwDistributionRepository;
use AnzuSystems\CoreDamBundle\Repository\VideoFileRepository;
use AnzuSystems\CoreDamBundle\Repository\YoutubeDistributionRepository;
use AnzuSystems\CoreDamBundle\Traits\IndexManagerAwareTrait;
use AnzuSystems\CoreDamBundle\Traits\MessageBusAwareTrait;
use AnzuSystems\SerializerBundle\Serializer;
use App\App;
use App\Csv\CsvFactory;
use App\Distribution\Modules\ArtemisMediaDistributionCustomDataFactory;
use App\Domain\ArtemisVideoDistribution\ArtemisVideoDistributionManager;
use App\Entity\ArtemisVideoDistribution;
use App\Entity\Embeds\ArtemisVideoTexts;
use App\MediaApiMigrations\ConnectionDecorator;
use App\Model\Csv\JwCsvFile;
use App\Model\Dto\JwVideo\JwMediaObject;
use App\Model\Dto\JwVideo\JwMediaObjectPlaylist;
use App\Repository\ArtemisAudioDistributionRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

final class JwMigrator
{
    use OutputUtilTrait;
    use IndexManagerAwareTrait;
    use MessageBusAwareTrait;
    private const string FILE_URL_MIME = 'video/mp4';

    private const string ARTEMIS_DISTRIBUTION_SERVICE = 'artemis_video_cms';
    private const string YOUTUBE_DISTRIBUTION_SERVICE = 'youtube_cms_main';
    private const string JW_DISTRIBUTION_SERVICE = 'jw_cms';
    private const int LICENCE_ID = 100_000;

    private readonly ConnectionDecorator $damMediaApiMigConnectionDecorator;

    public function __construct(
        private readonly FileSystemProvider $fileSystemProvider,
        private readonly CsvFactory $csvFactory,
        private readonly HttpClientInterface $httpClient,
        private readonly Serializer $serializer,
        private readonly AssetFileStatusManager $assetFileStatusManager,
        private readonly AssetFileStatusFacadeProvider $facadeProvider,
        private readonly VideoFileRepository $videoFileRepository,
        private readonly VideoManager $videoManager,
        private readonly AssetFileMetadataManager $assetFileMetadataManager,
        private readonly AssetFactory $assetFactory,
        private readonly AssetLicenceRepository $assetLicenceRepository,
        private readonly ArtemisVideoDistributionManager $distributionManager,
        private readonly YoutubeAbstractDistributionManager $youtubeDistributionManager,
        private readonly JwDistributionManager $jwDistributionManager,
        private readonly YoutubeDistributionRepository $youtubeDistributionRepository,
        private readonly ArtemisAudioDistributionRepository $artemisDistributionRepository,
        private readonly JwDistributionRepository $jwDistributionRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly Connection $damMediaApiMigConnection,
    ) {
        $this->damMediaApiMigConnectionDecorator = new ConnectionDecorator($damMediaApiMigConnection);
    }

    public function buildTable(): void
    {
        $this->dropTable();
        $this->createTable();
    }

    public function migrate(): void
    {
        $videoFileSystem = $this->fileSystemProvider->getFileSystemByStorageName('cms.video');
        if (null === $videoFileSystem) {
            throw new DomainException('Filesystem not exist');
        }

        $csvFile = $this->fileSystemProvider->getTmpFileSystem()->writeTmpFileFromStream(
            $videoFileSystem->readStream('jw_video_media.csv')
        );

        $csvFile = $this->csvFactory->initCsv((string) $csvFile->getRealPath(), JwCsvFile::class, true);

        $progress = $this->outputUtil->createProgressBar();
        $progress->setFormat('debug');
        $progress->start();

        foreach ($csvFile->readObject() as $row) {
            $this->entityManager->clear();
            $this->fileSystemProvider->getTmpFileSystem()->clearPaths();

            $licence = $this->assetLicenceRepository->find(self::LICENCE_ID);
            if (null === $licence) {
                throw new DomainException('licence not found');
            }
            try {
                $this->videoManager->beginTransaction();
                $firstPlaylist = $this->getPlaylist($row);
                if (null === $firstPlaylist) {
                    continue;
                }
                $url = $this->getJwPlayerMediaUrl($firstPlaylist, $row);
                if (null === $url) {
                    continue;
                }
                $assetFile = $this->downloadSynchronous($licence, $row, $url);
                if (null === $assetFile) {
                    continue;
                }

                $dateTime = App::getAppDate()->setTimestamp($firstPlaylist->getPubDate());
                $this->setupDistributions($row, $assetFile, $dateTime);

                $this->updateVideoTrackingFields($assetFile, $dateTime);

                $this->videoManager->commit();
                $this->messageBus->dispatch(new AssetRefreshPropertiesMessage((string) $assetFile->getAsset()->getId()));

                $this->insertIntoTable($row, $assetFile);
                $this->damMediaApiMigConnectionDecorator->flush();
                $progress->advance();
            } catch (Throwable $e) {
                $this->videoManager->rollback();

                throw new RuntimeException('asset_file_create_from_url_failed', 0, $e);
            }
        }

        $progress->finish();
    }

    public function downloadSynchronous(AssetLicence $assetLicence, JwCsvFile $video, string $url): ?VideoFile
    {
        $videoFile = $this->videoFileRepository->findOneBy([
            'assetAttributes.originUrl' => $url,
            'licence' => $assetLicence,
            'assetAttributes.status' => AssetFileProcessStatus::Processed,
        ]);
        if ($videoFile) {
            return $videoFile;
        }

        $videoFile = $this->createVideo($assetLicence, $url);

        $videoFile->getAsset()->getMetadata()->setCustomData([
            'title' => $video->getTitle(),
        ]);

        $this->assetFileStatusManager->toUploaded($videoFile, false);
        $this->facadeProvider->getStatusFacade($videoFile)->storeAndProcess($videoFile);

        if ($videoFile->getAssetAttributes()->getStatus()->is(AssetFileProcessStatus::Processed)) {
            return $videoFile;
        }

        if (
            $videoFile->getAssetAttributes()->getStatus()->is(AssetFileProcessStatus::Duplicate) &&
            false === empty($videoFile->getAssetAttributes()->getOriginAssetId())
        ) {
            $origin = $this->videoFileRepository->findOneBy([
                'id' => $videoFile->getAssetAttributes()->getOriginAssetId(),
                'licence' => $assetLicence,
                'assetAttributes.status' => AssetFileProcessStatus::Processed,
            ]);

            if ($origin instanceof VideoFile) {
                return $origin;
            }
        }

        $this->outputUtil->error(sprintf('Asset file failed (%s)', (string) $videoFile->getId()));

        return null;
    }

    private function setupDistributions(JwCsvFile $video, VideoFile $videoFile, DateTimeImmutable $pubDate): void
    {
        $jwDistribution = $this->setupJwDistribution($video, $videoFile, $pubDate);
        $ytDistribution = $this->setupYoutubeDistribution($video, $videoFile, $pubDate);
        $artemisDistribution = $this->setupArtemisDistribution($video, $videoFile, $pubDate);

        $jwDistribution->setBlocks(new ArrayCollection([$artemisDistribution]));
        $blockedBy = [$jwDistribution];

        if ($ytDistribution instanceof YoutubeDistribution) {
            $blockedBy[] = $ytDistribution;
            $ytDistribution->setBlocks(new ArrayCollection([$artemisDistribution]));
        }

        $artemisDistribution->setBlockedBy(new ArrayCollection($blockedBy));
    }

    private function setupArtemisDistribution(JwCsvFile $video, VideoFile $videoFile, DateTimeImmutable $pubDate): ArtemisVideoDistribution
    {
        $distribution = $this->artemisDistributionRepository->findByAssetFileAndDistributionService(
            (string) $videoFile->getId(),
            self::ARTEMIS_DISTRIBUTION_SERVICE
        );

        if ($distribution instanceof ArtemisVideoDistribution) {
            return $distribution;
        }

        $distribution = (new ArtemisVideoDistribution())
            ->setDistributionService(self::ARTEMIS_DISTRIBUTION_SERVICE)
            ->setAssetId((string) $videoFile->getAsset()->getId())
            ->setAssetFileId((string) $videoFile->getId())
            ->setTexts(
                (new ArtemisVideoTexts())
                    ->setTitle(StringHelper::parseString($video->getTitle(), 100))
            )
        ;
        $distribution->setStatus(DistributionProcessStatus::Distributed);
        $distribution->setDistributionData([
            ArtemisMediaDistributionCustomDataFactory::MEDIA_ADMIN_URL => [
                'type' => 'url',
                'value' => sprintf('https://artemis.sme.sk/admin/media/%d/edit/section/119', $video->getId()),
            ],
        ]);
        $distribution->setExtId((string) $video->getId());
        $distribution->setPublishAt($pubDate);
        $this->updateTrackableFields($distribution, $pubDate);

        $this->distributionManager->create($distribution);

        return $distribution;
    }

    private function setupJwDistribution(JwCsvFile $video, VideoFile $videoFile, DateTimeImmutable $pubDate): JwDistribution
    {
        $distribution = $this->jwDistributionRepository->findByAssetFileAndDistributionService(
            (string) $videoFile->getId(),
            self::JW_DISTRIBUTION_SERVICE
        );

        if ($distribution instanceof JwDistribution) {
            return $distribution;
        }

        $distribution = (new JwDistribution())
            ->setDistributionService(self::JW_DISTRIBUTION_SERVICE)
            ->setAssetId((string) $videoFile->getAsset()->getId())
            ->setAssetFileId((string) $videoFile->getId())
            ->setTexts(
                (new JwTexts())
                    ->setTitle(StringHelper::parseString($video->getTitle(), 100))
            )
            ->setExtId($video->getSourceId())
        ;

        $distribution->setStatus(DistributionProcessStatus::Distributed);
        $this->updateTrackableFields($distribution, $pubDate);
        $this->jwDistributionManager->create($distribution);

        return $distribution;
    }

    private function setupYoutubeDistribution(JwCsvFile $video, VideoFile $videoFile, DateTimeImmutable $pubDate): ?YoutubeDistribution
    {
        $distribution = $this->youtubeDistributionRepository->findByAssetFileAndDistributionService(
            (string) $videoFile->getId(),
            self::YOUTUBE_DISTRIBUTION_SERVICE
        );

        if ($distribution instanceof YoutubeDistribution) {
            return $distribution;
        }

        if (empty($video->getSourceIdYt())) {
            return null;
        }

        $distribution = (new YoutubeDistribution())
            ->setDistributionService(self::YOUTUBE_DISTRIBUTION_SERVICE)
            ->setAssetId((string) $videoFile->getAsset()->getId())
            ->setAssetFileId((string) $videoFile->getId())
            ->setTexts(
                (new YoutubeTexts())
                    ->setTitle(StringHelper::parseString($video->getTitle(), 100))
            )
            ->setExtId($video->getSourceIdYt())
        ;

        $distribution->setStatus(DistributionProcessStatus::Distributed);
        $this->updateTrackableFields($distribution, $pubDate);
        $this->youtubeDistributionManager->create($distribution);

        return $distribution;
    }

    private function getPlaylist(JwCsvFile $video): ?JwMediaObjectPlaylist
    {
        $response = $this->httpClient->request(
            method: Request::METHOD_GET,
            url: sprintf('https://cdn.jwplayer.com/v2/media/%s', $video->getSourceId())
        );

        try {
            $data = $this->serializer->deserialize($response->getContent(), JwMediaObject::class);
        } catch (Throwable $e) {
            $this->outputUtil->error(sprintf('Failed do get media id (%d) with message (%s)', $video->getId(), $e->getMessage()));

            return null;
        }

        $firstPlaylist = $data->getPlaylist()->first();

        if (false === ($firstPlaylist instanceof JwMediaObjectPlaylist)) {
            $this->outputUtil->error(sprintf('Playlist not found for media (%d)', $video->getId()));

            return null;
        }

        return $firstPlaylist;
    }

    private function getJwPlayerMediaUrl(JwMediaObjectPlaylist $firstPlaylist, JwCsvFile $video): ?string
    {
        $lastUrl = null;
        $lastSize = 0;

        foreach ($firstPlaylist->getSources() as $source) {
            if ($source->getFileSize() && $source->getFileSize() > $lastSize && self::FILE_URL_MIME === $source->getType()) {
                $lastUrl = $source->getFile();
                $lastSize = $source->getFileSize();
            }
        }

        if (null === $lastUrl) {
            $this->outputUtil->error(sprintf('Not found mp4url (%d)', $video->getId()));

            return null;
        }

        return $lastUrl;
    }

    private function createVideo(AssetLicence $assetLicence, string $url): VideoFile
    {
        $metadata = new AssetFileMetadata();
        $this->assetFileMetadataManager->create($metadata, false);

        $videoFile = (new VideoFile())
            ->setMetadata($metadata)
            ->setLicence($assetLicence)
        ;

        $videoFile->getAssetAttributes()
            ->setOriginUrl($url)
            ->setCreateStrategy(AssetFileCreateStrategy::Download);

        $this->assetFactory->createForAssetFile($videoFile, $videoFile->getLicence());
        $this->videoManager->create($videoFile, false);

        return $videoFile;
    }

    private function updateVideoTrackingFields(VideoFile $videoFile, DateTimeImmutable $pubDate): void
    {
        $this->updateTrackableFields($videoFile, $pubDate);
        $this->updateTrackableFields($videoFile->getMetadata(), $pubDate);
        $this->updateTrackableFields($videoFile->getAsset(), $pubDate);
        $this->updateTrackableFields($videoFile->getAsset()->getMetadata(), $pubDate);

        $imageFile = $videoFile->getImagePreview()?->getImageFile();
        if ($imageFile) {
            $this->updateTrackableFields($imageFile, $pubDate);
            $this->updateTrackableFields($imageFile->getMetadata(), $pubDate);
            $this->updateTrackableFields($imageFile->getAsset(), $pubDate);
            $this->updateTrackableFields($imageFile->getAsset()->getMetadata(), $pubDate);
        }

        $this->entityManager->flush();
    }

    private function updateTrackableFields(TimeTrackingInterface $entity, DateTimeImmutable $pubDate): void
    {
        $entity->setCreatedAt($pubDate);
        $entity->setModifiedAt($pubDate);
    }

    private function dropTable(): void
    {
        $this->damMediaApiMigConnection->executeQuery('DROP TABLE IF EXISTS dam_jw_video_mig');
        $this->outputUtil->info('Table dropped');
    }

    private function createTable(): void
    {
        $sql = '
        CREATE TABLE IF NOT EXISTS dam_jw_video_mig(
            media_id int unsigned auto_increment primary key,
            asset_id char(36) not null default \'\',
            asset_file_id varchar(500) not null default \'\',
            image_preview_id varchar(500) not null default \'\'
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;';

        $this->damMediaApiMigConnection->executeQuery($sql);

        $this->damMediaApiMigConnection->executeQuery(
            'create index IDX_ASSET_ID on dam_jw_video_mig(asset_id);'
        );

        $this->outputUtil->info('Table created');
    }

    private function insertIntoTable(JwCsvFile $video, VideoFile $videoFile): void
    {
        $this->damMediaApiMigConnectionDecorator->prepareBulkInsert(
            'dam_jw_video_mig',
            [
                'media_id' => $video->getId(),
                'asset_id' => (string) $videoFile->getAsset()->getId(),
                'asset_file_id' => (string) $videoFile->getId(),
                'image_preview_id' => (string) $videoFile->getImagePreview()?->getImageFile()?->getId(),
            ],
            ['media_id = new_row.media_id']
        );
    }
}
