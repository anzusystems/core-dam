<?php

declare(strict_types=1);

namespace App\DataFixtures;

use AnzuSystems\CommonBundle\DataFixtures\Fixtures\AbstractFixtures;
use AnzuSystems\CoreDamBundle\DataFixtures\VideoFixtures;
use AnzuSystems\CoreDamBundle\Domain\Distribution\DistributionManagerProvider;
use AnzuSystems\CoreDamBundle\Domain\Distribution\DistributionStatusFacade;
use AnzuSystems\CoreDamBundle\Domain\JwDistribution\JwDistributionFacade;
use AnzuSystems\CoreDamBundle\Domain\YoutubeDistribution\YoutubeDistributionFacade;
use AnzuSystems\CoreDamBundle\Entity\AssetFile;
use AnzuSystems\CoreDamBundle\Entity\Distribution;
use AnzuSystems\CoreDamBundle\Messenger\Message\AssetRefreshPropertiesMessage;
use AnzuSystems\CoreDamBundle\Repository\AssetFileRepository;
use AnzuSystems\CoreDamBundle\Traits\MessageBusAwareTrait;
use RuntimeException;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * @extends AbstractFixtures<Distribution>
 */
final class DistributionFixtures extends AbstractFixtures
{
    use MessageBusAwareTrait;

    public function __construct(
        private readonly YoutubeDistributionFacade $youtubeDistributionFacade,
        private readonly JwDistributionFacade $jwDistributionFacade,
        private readonly DistributionStatusFacade $distributionStatusFacade,
        private readonly AssetFileRepository $repository,
        private readonly DistributionManagerProvider $distributionManagerProvider,
    ) {
    }

    public function getEnvironments(): array
    {
        return ['dev', 'test'];
    }

    public static function getDependencies(): array
    {
        return [VideoFixtures::class];
    }

    public static function getIndexKey(): string
    {
        return Distribution::class;
    }

    public function load(ProgressBar $progressBar): void
    {
        $video = $this->repository->find(VideoFixtures::VIDEO_ID_1);
        if (null === $video) {
            return;
        }

        if (false === ($video instanceof AssetFile)) {
            throw new RuntimeException('Invalid video file type');
        }
        $distribution = $this->youtubeDistributionFacade->preparePayload($video, 'youtube_cms_main');
        $this->distributionManagerProvider->get($distribution::class)->create($distribution);
        $distribution->setExtId('8ZMm3wUrZSY');
        $this->distributionStatusFacade->toDistributed($distribution);

        $distribution = $this->jwDistributionFacade->preparePayload($video, 'jw_cms');
        $this->distributionManagerProvider->get($distribution::class)->create($distribution);
        $distribution->setExtId('1HssSBsu');
        $this->distributionStatusFacade->toDistributed($distribution);

        $this->messageBus->dispatch(new AssetRefreshPropertiesMessage((string) $video->getAsset()->getId()));
    }
}
