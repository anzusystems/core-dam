<?php

declare(strict_types=1);

namespace App\Fs1Migration;

use AnzuSystems\CoreDamBundle\Command\Traits\OutputUtilTrait;
use AnzuSystems\CoreDamBundle\Distribution\Modules\JwPlayerDistributionModule;
use AnzuSystems\CoreDamBundle\Domain\Distribution\DistributionStatusManager;
use AnzuSystems\CoreDamBundle\Domain\JwDistribution\JwDistributionManager;
use AnzuSystems\CoreDamBundle\Entity\JwDistribution;
use AnzuSystems\CoreDamBundle\FileSystem\FileSystemProvider;
use AnzuSystems\CoreDamBundle\FileSystem\TmpLocalFilesystem;
use AnzuSystems\CoreDamBundle\Helper\StringHelper;
use AnzuSystems\CoreDamBundle\Messenger\Message\DistributionRemoteProcessingCheckMessage;
use AnzuSystems\CoreDamBundle\Repository\DistributionRepository;
use AnzuSystems\CoreDamBundle\Repository\VideoFileRepository;
use AnzuSystems\CoreDamBundle\Traits\MessageBusAwareTrait;
use App\MediaApiMigrations\ConnectionDecorator;
use App\Model\Fs1ApiMigrateConfig;
use App\Model\Fs1Migration\VideoMigrationDto;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Throwable;

final class JwVideoDistribution
{
    use MessageBusAwareTrait;

    use OutputUtilTrait;

    public const STATUS_DISTRIBUTED = 'distributed';
    public const STATUS_DISTRIBUTION_FAILED = 'distribution_failed';

    private const JW_CMS_SERVICE = 'jw_cms';
    private const ARTEMIS_VIDEO = 'artemis_video_cms';

    private readonly ConnectionDecorator $damMediaApiMigConnectionDecorator;
    private TmpLocalFilesystem $tmpLocalFilesystem;

    public function __construct(
        private readonly MigrationTableIterator $migrationTableIterator,
        private readonly VideoFileRepository $videoFileRepository,
        private readonly JwPlayerDistributionModule $jwPlayerDistributionModule,
        private readonly JwDistributionManager $distributionManager,
        private readonly DistributionRepository $distributionRepository,
        private readonly DistributionStatusManager $distributionStatusManager,
        private readonly Connection $damMediaApiMigConnection,
        private readonly FileSystemProvider $fileSystemProvider,
        private readonly EntityManagerInterface $entityManager,
    ) {
        $this->damMediaApiMigConnectionDecorator = new ConnectionDecorator($damMediaApiMigConnection);
    }

    public function distribute(Fs1ApiMigrateConfig $config): void
    {
        if (empty($config->getShowId())) {
            $this->outputUtil->error('Show ID for distribution should be provided');

            return;
        }

        $this->tmpLocalFilesystem = $this->fileSystemProvider->getTmpFileSystem();

        $progres = $this->outputUtil->createProgressBar(
            $this->migrationTableIterator->getCount($config)
        );
        $progres->start();

        $i = 0;
        foreach ($this->migrationTableIterator->iterate($config) as $value) {
            if ($this->shouldDistribute($value)) {
                $i++;
                $this->distributeVideo($value);
                $this->flushAndClear();

                if ($config->getLimit() && $i >= $config->getLimit()) {
                    break;
                }
            }

            $progres->advance();
        }

        $progres->finish();
        $this->outputUtil->writeln('');
    }

    private function distributeVideo(VideoMigrationDto $dto): void
    {
        if (empty($dto->getAssetId())) {
            return;
        }

        $videoFile = $this->videoFileRepository->find($dto->getAssetId());

        if (null === $videoFile) {
            return;
        }

        try {
            $existingJwDistribution = $this->distributionRepository->findByAssetFileAndDistributionService(
                assetFileId: $dto->getAssetId(),
                distributionService: self::JW_CMS_SERVICE
            );

            if ($existingJwDistribution) {
                $this->outputUtil->writeln('Already distributed to JW');
                $this->updateRow(
                    mediaApiId: $dto->getMediaId(),
                    status: self::STATUS_DISTRIBUTION_FAILED,
                    failReason: 'already_distributed',
                );
                return;
            }

            $jwDistribution = (new JwDistribution())
                ->setDistributionService(self::JW_CMS_SERVICE)
                ->setAssetId((string) $videoFile->getAsset()->getId())
                ->setAssetFileId((string) $videoFile->getId())
            ;

            $this->distributionManager->create($jwDistribution);
            $this->jwPlayerDistributionModule->distribute($jwDistribution);
            $this->distributionStatusManager->toRemoteProcessing($jwDistribution);

            $this->messageBus->dispatch(new DistributionRemoteProcessingCheckMessage($jwDistribution));

            $this->updateRow(
                mediaApiId: $dto->getMediaId(),
                status: self::STATUS_DISTRIBUTED,
                jwId: $jwDistribution->getExtId(),
            );
        } catch (Throwable $e) {
            $this->updateRow(
                mediaApiId: $dto->getMediaId(),
                status: self::STATUS_DISTRIBUTION_FAILED,
                failReason: 'unknown',
                outputLog: $e->getMessage()
            );
        }
    }

    private function shouldDistribute(VideoMigrationDto $dto): bool
    {
        return empty($dto->getYoutubeCode())
            && false === empty($dto->getShowUuid())
            && false === empty($dto->getAssetId())
        ;
    }

    private function updateRow(
        int $mediaApiId,
        string $status,
        string $jwId = '',
        string $failReason = '',
        string $outputLog = '',
    ): void {
        $updateData = [
            'migration_status' => $status,
            'fail_reason' => $failReason,
            'output_log' => StringHelper::parseString($outputLog, 256),
        ];
        if (false === empty($jwId)) {
            $updateData['jw_id'] = $jwId;
        }

        $this->damMediaApiMigConnectionDecorator->prepareUpdate(
            'dam_fs1_mig',
            $updateData,
            [
                'media_id' => $mediaApiId,
            ]
        );
    }

    private function flushAndClear(): void
    {
        $this->distributionManager->flush();
        $this->distributionManager->clear();
        $this->tmpLocalFilesystem->clearPaths();
        $this->damMediaApiMigConnectionDecorator->flush();
    }
}
