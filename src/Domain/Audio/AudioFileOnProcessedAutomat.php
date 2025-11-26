<?php

declare(strict_types=1);

namespace App\Domain\Audio;

use AnzuSystems\CoreDamBundle\Domain\AbstractManager;
use AnzuSystems\CoreDamBundle\Domain\AssetFileRoute\AssetFileRouteFacade;
use AnzuSystems\CoreDamBundle\Domain\PodcastEpisode\PodcastEpisodeManager;
use AnzuSystems\CoreDamBundle\Entity\AssetFile;
use AnzuSystems\CoreDamBundle\Entity\AssetSlot;
use AnzuSystems\CoreDamBundle\Entity\AudioFile;
use AnzuSystems\CoreDamBundle\Entity\PodcastEpisode;
use AnzuSystems\CoreDamBundle\Logger\DamLogger;
use AnzuSystems\CoreDamBundle\Model\Dto\AssetFileRoute\AssetFileRouteAdmCreateDto;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetFileProcessStatus;
use AnzuSystems\CoreDamBundle\Repository\AssetFileRouteRepository;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use App\Configuration\ConfigurationProvider;
use Doctrine\ORM\NonUniqueResultException;
use Throwable;

final class AudioFileOnProcessedAutomat extends AbstractManager
{
    public function __construct(
        private readonly ConfigurationProvider $configurationProvider,
        private readonly DamLogger $logger,
        private readonly AssetFileRouteFacade $assetFileRouteFacade,
        private readonly AssetFileRouteRepository $assetFileRouteRepository,
        private readonly PodcastEpisodeManager $podcastEpisodeManager,
    ) {
    }

    /**
     * @throws SerializerException
     */
    public function makeAudioPublicUrl(AudioFile $audioFile): void
    {
        // Public url only for processed
        if ($audioFile->getAssetAttributes()->getStatus()->isNot(AssetFileProcessStatus::Processed)) {
            return;
        }

        // todo condition?
        // Public url only for bonus/premium slot
        if (
            false === $this->isAtPremiumSlot($audioFile) &&
            false === $this->isAtBonusSlot($audioFile)
        ) {
            return;
        }

        $this->tryMakePublic($audioFile);
    }

    /**
     * @throws NonUniqueResultException
     * @throws SerializerException
     */
    public function activateForPublicExportAndPublishPremium(AudioFile $audioFile): void
    {
        if ($audioFile->getAssetAttributes()->getStatus()->isNot(AssetFileProcessStatus::Processed)) {
            return;
        }

        foreach ($audioFile->getAsset()->getEpisodes() as $episode) {
            $this->activateEpisodeForPublicExport($episode);
            $this->makePremiumEpisodePublic($episode, $audioFile);
        }
    }

    private function activateEpisodeForPublicExport(PodcastEpisode $episode): void
    {
        if ($episode->getFlags()->isMobilePublicExportEnabled() && $episode->getFlags()->isWebPublicExportEnabled()) {
            return;
        }
        $episode->getFlags()
            ->setMobilePublicExportEnabled(true)
            ->setWebPublicExportEnabled(true)
        ;

        $this->podcastEpisodeManager->updateExisting($episode);
    }

    /**
     * Automatically distribute if audio is synced from RSS to free slot
     *
     * @throws NonUniqueResultException
     * @throws SerializerException
     */
    private function makePremiumEpisodePublic(PodcastEpisode $episode, AudioFile $audioFile): void
    {
        if ($this->isAtFreeSlot($audioFile) && $episode->getFlags()->isFromRss()) {
            $premiumVersion = $audioFile->getAsset()->getSlots()->filter(
                fn (AssetSlot $slot): bool => $this->isAtPremiumSlot($slot->getAssetFile())
            )->first();

            if ($premiumVersion instanceof AssetSlot && $premiumVersion->getAudio()) {
                $this->tryMakePublic($premiumVersion->getAudio());
            }
        }
    }

    private function isAtBonusSlot(AssetFile $assetFile): bool
    {
        return $this->isAtSlot($assetFile, $this->configurationProvider->getAudioDistribution()->getAudioBonusSlotName());
    }

    private function isAtPremiumSlot(AssetFile $assetFile): bool
    {
        return $this->isAtSlot($assetFile, $this->configurationProvider->getAudioDistribution()->getAudioPremiumSlotName());
    }

    private function isAtFreeSlot(AssetFile $assetFile): bool
    {
        return $this->isAtSlot($assetFile, $this->configurationProvider->getAudioDistribution()->getAudioFreeSlotName());
    }

    private function isAtSlot(AssetFile $assetFile, string $slotName): bool
    {
        return (bool) $assetFile->getSlots()->filter(
            fn (AssetSlot $slot): bool => $slot->getName() === $slotName
        )->first();
    }

    /**
     * @throws SerializerException
     */
    private function tryMakePublic(AudioFile $audioFile): void
    {
        $mainRoute = $this->assetFileRouteRepository->findMainByAssetFile((string) $audioFile->getId());
        if (null === $mainRoute) {
            try {
                $this->assetFileRouteFacade->makePublicFromDto($audioFile, new AssetFileRouteAdmCreateDto());
            } catch (Throwable $exception) {
                $this->logger->error(self::class, 'Make public audio link failed', $exception);
            }
        }
    }
}
