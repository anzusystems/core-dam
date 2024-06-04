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
use AnzuSystems\CoreDamBundle\Domain\Podcast\PodcastImportIterator;
use AnzuSystems\CoreDamBundle\Domain\PodcastEpisode\PodcastEpisodeManager;
use AnzuSystems\CoreDamBundle\Domain\Video\VideoManager;
use AnzuSystems\CoreDamBundle\Domain\YoutubeDistribution\YoutubeAbstractDistributionManager;
use AnzuSystems\CoreDamBundle\Entity\AssetFileMetadata;
use AnzuSystems\CoreDamBundle\Entity\AssetLicence;
use AnzuSystems\CoreDamBundle\Entity\AudioFile;
use AnzuSystems\CoreDamBundle\Entity\Embeds\PodcastAttributes;
use AnzuSystems\CoreDamBundle\Entity\Embeds\PodcastDates;
use AnzuSystems\CoreDamBundle\Entity\Podcast;
use AnzuSystems\CoreDamBundle\Entity\PodcastEpisode;
use AnzuSystems\CoreDamBundle\Exception\DomainException;
use AnzuSystems\CoreDamBundle\Exception\RuntimeException;
use AnzuSystems\CoreDamBundle\FileSystem\FileSystemProvider;
use AnzuSystems\CoreDamBundle\Helper\StringHelper;
use AnzuSystems\CoreDamBundle\Messenger\Message\AssetRefreshPropertiesMessage;
use AnzuSystems\CoreDamBundle\Model\Dto\Podcast\PodcastImportIteratorDto;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetFileCreateStrategy;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetFileProcessStatus;
use AnzuSystems\CoreDamBundle\Model\Enum\DistributionProcessStatus;
use AnzuSystems\CoreDamBundle\Model\ValueObject\PodcastSynchronizerPointer;
use AnzuSystems\CoreDamBundle\Repository\AssetLicenceRepository;
use AnzuSystems\CoreDamBundle\Repository\AudioFileRepository;
use AnzuSystems\CoreDamBundle\Repository\JwDistributionRepository;
use AnzuSystems\CoreDamBundle\Repository\PodcastEpisodeRepository;
use AnzuSystems\CoreDamBundle\Repository\PodcastRepository;
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
use App\Model\Csv\AudioCsvFile;
use App\Repository\ArtemisAudioDistributionRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

final class AudioPodcastMigrator
{
    use OutputUtilTrait;
    use IndexManagerAwareTrait;
    use MessageBusAwareTrait;
    private const int LICENCE_ID = 100_000;
    private const string ARTEMIS_DISTRIBUTION_SERVICE = 'artemis_podcast_cms';

    private readonly ConnectionDecorator $damMediaApiMigConnectionDecorator;

    public function __construct(
        private readonly FileSystemProvider $fileSystemProvider,
        private readonly CsvFactory $csvFactory,
        private readonly HttpClientInterface $httpClient,
        private readonly Serializer $serializer,
        private readonly AssetFileStatusManager $assetFileStatusManager,
        private readonly AssetFileStatusFacadeProvider $facadeProvider,
        private readonly AudioFileRepository $audioFileRepository,
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
        private readonly PodcastRepository $podcastRepository,
        private readonly PodcastEpisodeRepository $podcastEpisodeRepository,
        private readonly PodcastEpisodeManager $podcastEpisodeManager,
        private readonly PodcastImportIterator $podcastImportIterator,
    ) {
        $this->damMediaApiMigConnectionDecorator = new ConnectionDecorator($damMediaApiMigConnection);
    }

    public function migrate(): void
    {
        $this->dropTable();
        $this->createTable();

        $videoFileSystem = $this->fileSystemProvider->getFileSystemByStorageName('cms.audio');
        if (null === $videoFileSystem) {
            throw new DomainException('Filesystem not exist');
        }

        $csvFile = $this->fileSystemProvider->getTmpFileSystem()->writeTmpFileFromStream(
            $videoFileSystem->readStream('audio_video_media.csv')
        );

        $csvFile = $this->csvFactory->initCsv((string) $csvFile->getRealPath(), AudioCsvFile::class, true);

        $progress = $this->outputUtil->createProgressBar();
        $progress->setFormat('debug');
        $progress->start();

        /** @var AudioCsvFile $row */
        foreach ($csvFile->readObject() as $row) {
            $this->entityManager->clear();
            $this->fileSystemProvider->getTmpFileSystem()->clearPaths();

            $licence = $this->assetLicenceRepository->find(self::LICENCE_ID);
            if (null === $licence) {
                throw new DomainException('licence not found');
            }

            try {
                $this->videoManager->beginTransaction();
                $assetFile = $this->downloadSynchronous($licence, $row);
                if (null === $assetFile) {
                    continue;
                }

                $episode = $this->addToEpisode($row, $assetFile);
                $pubDate = App::getMinDate();

                if ($episode instanceof PodcastEpisode) {
                    $dto = $this->getPodcastImportIteratorDto($episode->getPodcast(), $row);
                    if ($dto) {
                        $pubDate = $dto->getItem()->getPubDate();
                    }
                    $episode->getDates()->setPublicationDate($pubDate);
                }

                $this->updateAudioTrackingFields($assetFile, $pubDate);
                $this->setupArtemisDistribution($row, $assetFile, $pubDate);

                $this->videoManager->flush();
                $this->videoManager->commit();
                $this->messageBus->dispatch(new AssetRefreshPropertiesMessage((string) $assetFile->getAsset()->getId()));

                $this->insertIntoTable($row, $assetFile, $episode);
                $this->damMediaApiMigConnectionDecorator->flush();
                $progress->advance();
            } catch (Throwable $e) {
                $this->videoManager->rollback();

                throw new RuntimeException('asset_file_create_from_url_failed', 0, $e);
            }
        }
    }

    public function downloadSynchronous(AssetLicence $assetLicence, AudioCsvFile $audioCsvFile): ?AudioFile
    {
        if (empty($audioCsvFile->getDirectSourceUrl())) {
            $this->outputUtil->error(sprintf('Empty url for mediaId (%s)', (string) $audioCsvFile->getId()));

            return null;
        }

        $audioFile = $this->audioFileRepository->findOneBy([
            'assetAttributes.originUrl' => $audioCsvFile->getDirectSourceUrl(),
            'licence' => $assetLicence,
            'assetAttributes.status' => AssetFileProcessStatus::Processed,
        ]);
        if ($audioFile) {
            return $audioFile;
        }

        $audioFile = $this->createAudio($assetLicence, $audioCsvFile->getDirectSourceUrl());

        $audioFile->getAsset()->getMetadata()->setCustomData([
            'title' => $audioCsvFile->getTitle(),
        ]);

        $this->assetFileStatusManager->toUploaded($audioFile, false);
        $this->facadeProvider->getStatusFacade($audioFile)->storeAndProcess($audioFile);

        if ($audioFile->getAssetAttributes()->getStatus()->is(AssetFileProcessStatus::Processed)) {
            return $audioFile;
        }

        if (
            $audioFile->getAssetAttributes()->getStatus()->is(AssetFileProcessStatus::Duplicate) &&
            false === empty($audioFile->getAssetAttributes()->getOriginAssetId())
        ) {
            $origin = $this->audioFileRepository->findOneBy([
                'id' => $audioFile->getAssetAttributes()->getOriginAssetId(),
                'licence' => $assetLicence,
                'assetAttributes.status' => AssetFileProcessStatus::Processed,
            ]);

            if ($origin instanceof AudioFile) {
                return $origin;
            }
        }

        $this->outputUtil->error(sprintf('Asset file failed (%s)', (string) $audioFile->getId()));

        return null;
    }

    private function setupArtemisDistribution(AudioCsvFile $audioCsvFile, AudioFile $audioFile, DateTimeImmutable $pubDate): ArtemisVideoDistribution
    {
        $distribution = $this->artemisDistributionRepository->findByAssetFileAndDistributionService(
            (string) $audioFile->getId(),
            self::ARTEMIS_DISTRIBUTION_SERVICE
        );

        if ($distribution instanceof ArtemisVideoDistribution) {
            return $distribution;
        }

        $distribution = (new ArtemisVideoDistribution())
            ->setDistributionService(self::ARTEMIS_DISTRIBUTION_SERVICE)
            ->setAssetId((string) $audioFile->getAsset()->getId())
            ->setAssetFileId((string) $audioFile->getId())
            ->setTexts(
                (new ArtemisVideoTexts())
                    ->setTitle(StringHelper::parseString($audioCsvFile->getTitle(), 100))
            )
        ;
        $distribution->setStatus(DistributionProcessStatus::Distributed);
        $distribution->setDistributionData([
            ArtemisMediaDistributionCustomDataFactory::MEDIA_ADMIN_URL => [
                'type' => 'url',
                'value' => sprintf('https://artemis.sme.sk/admin/media/%d/edit/section/117', $audioCsvFile->getId()),
            ],
        ]);
        $distribution->setExtId((string) $audioCsvFile->getId());
        $distribution->setPublishAt($pubDate);
        $this->updateTrackableFields($distribution, $pubDate);

        $this->distributionManager->create($distribution);

        return $distribution;
    }

    private function getPodcastImportIteratorDto(Podcast $podcast, AudioCsvFile $audioCsvFile): ?PodcastImportIteratorDto
    {
        $podcastAlt = (new Podcast())
            ->setAttributes((new PodcastAttributes())->setRssUrl($podcast->getAttributes()->getRssUrl()))
            ->setDates((new PodcastDates())->setImportFrom(App::getMinDate()))
        ;

        foreach ($this->podcastImportIterator->iteratePodcast(new PodcastSynchronizerPointer(), $podcastAlt) as $dto) {
            if ($dto->getItem()->getTitle() === $audioCsvFile->getTitle()) {
                return $dto;
            }
        }

        return null;
    }

    private function addToEpisode(AudioCsvFile $audioCsvFile, AudioFile $audioFile): ?PodcastEpisode
    {
        $oldEpisodes = $audioFile->getAsset()->getEpisodes();

        $oldEpisode = $oldEpisodes->findFirst(
            fn (int $id, PodcastEpisode $episode): bool => $episode->getTexts()->getTitle() === $audioCsvFile->getTitle()
        );

        if ($oldEpisode instanceof PodcastEpisode) {
            return $oldEpisode;
        }

        $podcast = $this->podcastRepository->findOneBy(['texts.title' => $audioCsvFile->getTitleChannel()]);
        $oldEpisode = $this->podcastEpisodeRepository->findOneBy(['texts.title' => $audioCsvFile->getTitle(), 'podcast' => $podcast]);

        // episode exists but asset is not assigned
        if ($oldEpisode instanceof PodcastEpisode && null === $oldEpisode->getAsset()) {
            $this->outputUtil->info(sprintf('Episode exists without asset, assigning (%s)', $oldEpisode->getTexts()->getTitle()));
            $oldEpisode->setAsset($audioFile->getAsset());

            return $oldEpisode;
        }

        // Episode exists but another asset is assigned
        if ($oldEpisode instanceof PodcastEpisode && false === ($oldEpisode->getAsset()?->getId() === $audioFile->getAsset()?->getId())) {
            $this->outputUtil->error(sprintf(
                'Episode exists but is taken by eixsting podcast (%s) media id (%s) (%s !== %s)',
                $oldEpisode->getTexts()->getTitle(),
                $audioCsvFile->getId(),
                $oldEpisode->getAsset()?->getId(),
                $audioFile->getAsset()?->getId()
            ));

            return null;
        }

        // Episode exists and same episode assigned
        if ($oldEpisode instanceof PodcastEpisode && $oldEpisode->getAsset()?->getId() === $audioFile->getAsset()?->getId()) {
            return $oldEpisode;
        }

        if (false === ($podcast instanceof Podcast)) {
            $this->outputUtil->error(sprintf('Podcast not found (%s)', $audioCsvFile->getTitleChannel()));

            return null;
        }

        $episode = new PodcastEpisode();
        $episode->getTexts()->setTitle($audioCsvFile->getTitle());
        $episode->getAttributes()->setRssUrl($audioCsvFile->getDirectSourceUrl());
        $episode->setPodcast($podcast);
        $podcast->getEpisodes()->add($episode);
        $episode->setAsset($audioFile->getAsset());
        $audioFile->getAsset()->getEpisodes()->add($episode);
        $this->podcastEpisodeManager->create($episode);

        return $episode;
    }

    private function createAudio(AssetLicence $assetLicence, string $url): AudioFile
    {
        $metadata = new AssetFileMetadata();
        $this->assetFileMetadataManager->create($metadata, false);

        $audioFile = (new AudioFile())
            ->setMetadata($metadata)
            ->setLicence($assetLicence)
        ;

        $audioFile->getAssetAttributes()
            ->setOriginUrl($url)
            ->setCreateStrategy(AssetFileCreateStrategy::Download);

        $this->assetFactory->createForAssetFile($audioFile, $audioFile->getLicence());
        $this->videoManager->create($audioFile, false);

        return $audioFile;
    }

    private function dropTable(): void
    {
        $this->damMediaApiMigConnection->executeQuery('DROP TABLE IF EXISTS dam_podcast_audio_mig');
        $this->outputUtil->info('Table dropped');
    }

    private function createTable(): void
    {
        $sql = '
        CREATE TABLE IF NOT EXISTS dam_podcast_audio_mig(
            media_id int unsigned auto_increment primary key,
            asset_id char(36) not null default \'\',
            asset_file_id varchar(500) not null default \'\',
            image_preview_id varchar(500) not null default \'\'
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;';

        $this->damMediaApiMigConnection->executeQuery($sql);

        $this->damMediaApiMigConnection->executeQuery(
            'create index IDX_ASSET_ID on dam_podcast_audio_mig(asset_id);'
        );

        $this->outputUtil->info('Table created');
    }

    private function insertIntoTable(AudioCsvFile $video, AudioFile $videoFile, ?PodcastEpisode $episode = null): void
    {
        $imageFile = $episode?->getImagePreview()?->getImageFile() ?? $episode?->getPodcast()?->getImagePreview()?->getImageFile();

        $this->damMediaApiMigConnectionDecorator->prepareBulkInsert(
            'dam_podcast_audio_mig',
            [
                'media_id' => $video->getId(),
                'asset_id' => (string) $videoFile->getAsset()->getId(),
                'asset_file_id' => (string) $videoFile->getId(),
                'image_preview_id' => (string) $imageFile?->getId(),
            ],
            ['media_id = new_row.media_id']
        );
    }

    private function updateAudioTrackingFields(AudioFile $videoFile, DateTimeImmutable $pubDate): void
    {
        $this->updateTrackableFields($videoFile, $pubDate);
        $this->updateTrackableFields($videoFile->getMetadata(), $pubDate);
        $this->updateTrackableFields($videoFile->getAsset(), $pubDate);
        $this->updateTrackableFields($videoFile->getAsset()->getMetadata(), $pubDate);
    }

    private function updateTrackableFields(TimeTrackingInterface $entity, DateTimeImmutable $pubDate): void
    {
        $entity->setCreatedAt($pubDate);
        $entity->setModifiedAt($pubDate);
    }
}
