<?php

declare(strict_types=1);

namespace App\Event\Listener;

use AnzuSystems\CoreDamBundle\Domain\VideoShowEpisode\VideoShowEpisodeManager;
use AnzuSystems\CoreDamBundle\Event\DistributionStatusEvent;
use AnzuSystems\CoreDamBundle\Model\Enum\DistributionProcessStatus;
use AnzuSystems\CoreDamBundle\Repository\AssetRepository;
use AnzuSystems\CoreDamBundle\Traits\MessageBusAwareTrait;
use App\Configuration\ConfigurationProvider;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: DistributionStatusEvent::class)]
final class DistributionStatusEventListener
{
    use MessageBusAwareTrait;

    public function __construct(
        private readonly AssetRepository $assetRepository,
        private readonly VideoShowEpisodeManager $videoShowEpisodeManager,
        private readonly ConfigurationProvider $configurationProvider,
    ) {
    }

    public function __invoke(DistributionStatusEvent $event): void
    {
        if ($event->getDistribution()->getStatus()->isNot(DistributionProcessStatus::Distributed)) {
            return;
        }

        $asset = $this->assetRepository->find($event->getDistribution()->getAssetId());
        if (null === $asset) {
            return;
        }

        $config = $this->configurationProvider->getAssetPubConfiguration();
        if (false === in_array($event->getDistribution()->getDistributionService(), $config->getVideoAllowedDistributions(), true)) {
            return;
        }

        $changed = false;
        foreach ($asset->getVideoEpisodes() as $videoEpisode) {
            if (
                false === $videoEpisode->getFlags()->isMobilePublicExportEnabled() ||
                false === $videoEpisode->getFlags()->isWebPublicExportEnabled()
            ) {
                $videoEpisode->getFlags()->setMobilePublicExportEnabled(true);
                $videoEpisode->getFlags()->setWebPublicExportEnabled(true);
                $changed = true;

                $this->videoShowEpisodeManager->updateExisting($videoEpisode, false);
            }
        }

        if ($changed) {
            $this->videoShowEpisodeManager->flush();
        }
    }
}
