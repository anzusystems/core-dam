<?php

declare(strict_types=1);

namespace App\Domain\Asset;

use AnzuSystems\CoreDamBundle\Entity\Asset;
use AnzuSystems\CoreDamBundle\Entity\JwDistribution;
use AnzuSystems\CoreDamBundle\Entity\VideoFile;
use AnzuSystems\CoreDamBundle\Entity\VideoShowEpisode;
use AnzuSystems\CoreDamBundle\Entity\YoutubeDistribution;
use AnzuSystems\CoreDamBundle\Model\Enum\DistributionProcessStatus;
use AnzuSystems\CoreDamBundle\Repository\DistributionRepository;
use App\Configuration\ConfigurationProvider;
use App\Model\Domain\Asset\VideoAssetPubDecorator;
use App\Model\Domain\Distribution\JwDistributionPubDecorator;
use App\Model\Domain\Distribution\YoutubeDistributionPubDecorator;

final readonly class VideoAssetPubBuilder
{
    public function __construct(
        private ConfigurationProvider $configurationProvider,
        private DistributionRepository $distributionRepository,
    ) {
    }

    public function getVideoDecorator(Asset $asset): ?VideoAssetPubDecorator
    {
        $configuration = $this->configurationProvider->getAssetPubConfiguration();

        $videoShowEpisode = $asset->getVideoEpisodes()->first();
        $videoShowEpisode = $videoShowEpisode instanceof VideoShowEpisode ? $videoShowEpisode : null;

        $videoFile = $asset->getMainFile();
        if (false === ($videoFile instanceof VideoFile)) {
            return null;
        }

        $distributions = [];
        foreach ($configuration->getVideoAllowedDistributions() as $allowedDistributionService) {
            $distribution = $this->distributionRepository->findByAssetFileAndDistributionService(
                (string) $videoFile->getId(),
                $allowedDistributionService
            );
            if (null === $distribution) {
                continue;
            }
            if ($distribution->getStatus()->isNot(DistributionProcessStatus::Distributed)) {
                continue;
            }
            if ($distribution instanceof YoutubeDistribution) {
                $distributions[] = YoutubeDistributionPubDecorator::getInstance($distribution);
            }
            if ($distribution instanceof JwDistribution) {
                $distributions[] = JwDistributionPubDecorator::getInstance($distribution);
            }
        }

        if (empty($distributions)) {
            return null;
        }

        return VideoAssetPubDecorator::getInstance(
            $asset,
            $videoFile,
            $distributions,
            $configuration->getMetadataTitle(),
            $videoShowEpisode
        );
    }
}
