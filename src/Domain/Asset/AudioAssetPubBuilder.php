<?php

declare(strict_types=1);

namespace App\Domain\Asset;

use AnzuSystems\CoreDamBundle\Cache\AssetFileRouteGenerator;
use AnzuSystems\CoreDamBundle\Entity\Asset;
use AnzuSystems\CoreDamBundle\Entity\AssetSlot;
use AnzuSystems\CoreDamBundle\Entity\AudioFile;
use AnzuSystems\CoreDamBundle\Entity\PodcastEpisode;
use AnzuSystems\CoreDamBundle\Entity\PublicExport;
use AnzuSystems\CoreDamBundle\Helper\StringHelper;
use AnzuSystems\CoreDamBundle\Repository\AssetFileRouteRepository;
use App\Configuration\ConfigurationProvider;
use App\Model\Configuration\ArtemisAudioDistributionConfiguration;
use App\Model\Domain\Asset\AudioAssetMediaPubDecorator;
use App\Model\Domain\Asset\AudioAssetPubDecorator;
use App\Model\Enum\GateStatus;
use App\Util\GateStatusResolver;

final readonly class AudioAssetPubBuilder
{
    public function __construct(
        private ConfigurationProvider $configurationProvider,
        private AssetFileRouteGenerator $assetFileRouteGenerator,
        private AssetFileRouteRepository $assetFileRouteRepository,
        private GateStatusResolver $gateStatusResolver,
    ) {
    }

    public function getAudioDecorator(Asset $asset, PublicExport $publicExport): ?AudioAssetPubDecorator
    {
        $configuration = $this->configurationProvider->getAudioDistribution();
        $assetPubConfiguration = $this->configurationProvider->getAssetPubConfiguration();

        $podcastEpisode = $asset->getEpisodes()->first();
        $podcastEpisode = $podcastEpisode instanceof PodcastEpisode ? $podcastEpisode : null;

        if (
            $podcastEpisode instanceof PodcastEpisode && (
                false === $publicExport->getType()->isEnabled($podcastEpisode) ||
                false === $publicExport->getType()->isEnabled($podcastEpisode->getPodcast())
            )
        ) {
            $podcastEpisode = null;
        }

        $media = [];
        foreach ($asset->getSlots() as $slot) {
            $audio = $this->getAudioSlotDecorator($slot, $configuration, $podcastEpisode);
            if ($audio) {
                $media[$slot->getName()] = $audio;
            }
        }

        if (
            false === isset($media[$configuration->getAudioBonusSlotName()]) &&
            $podcastEpisode instanceof PodcastEpisode
        ) {
            $audio = $this->getBonusAudioMedia($configuration, $podcastEpisode);
            if ($audio) {
                $media[$configuration->getAudioBonusSlotName()] = $audio;
            }
        }

        if (empty($media)) {
            return null;
        }

        /** @var AudioFile $audioFile */
        $audioFile = $asset->getMainFile();

        return AudioAssetPubDecorator::getInstance(
            $asset,
            $audioFile,
            array_values($media),
            $assetPubConfiguration->getMetadataTitle(),
            $podcastEpisode
        );
    }

    private function getAudioSlotDecorator(
        AssetSlot $slot,
        ArtemisAudioDistributionConfiguration $configuration,
        ?PodcastEpisode $podcastEpisode = null,
    ): ?AudioAssetMediaPubDecorator {
        if ($slot->getName() === $configuration->getAudioPremiumSlotName()) {
            return $this->getPremiumAudioMedia($slot, $configuration);
        }
        if ($slot->getName() === $configuration->getAudioBonusSlotName()) {
            if ($podcastEpisode instanceof PodcastEpisode) {
                return $this->getBonusAudioMedia($configuration, $podcastEpisode);
            }

            return null;
        }
        if ($slot->getName() === $configuration->getAudioFreeSlotName()) {
            return $this->getFreeAudioMedia($slot, $configuration, $podcastEpisode);
        }

        return null;
    }

    private function getPremiumAudioMedia(
        AssetSlot $slot,
        ArtemisAudioDistributionConfiguration $configuration,
    ): ?AudioAssetMediaPubDecorator {
        if ($this->gateStatusResolver->getLockStatus()->isNot(GateStatus::Unlocked)) {
            return null;
        }

        $premiumSlotAssetFile = $slot->getAudio();
        if (null === $premiumSlotAssetFile) {
            return null;
        }

        $mainUrl = $this->assetFileRouteRepository->findMainByAssetFile((string) $premiumSlotAssetFile->getId());
        if ($mainUrl) {
            return AudioAssetMediaPubDecorator::getInstance(
                type: $configuration->getAudioPremiumSlotName(),
                audioFile: $premiumSlotAssetFile,
                mediaUrl: $this->assetFileRouteGenerator->getFullUrl($mainUrl)
            );
        }

        return null;
    }

    private function getBonusAudioMedia(
        ArtemisAudioDistributionConfiguration $configuration,
        PodcastEpisode $podcastEpisode
    ): ?AudioAssetMediaPubDecorator {
        if (StringHelper::isNotEmpty($podcastEpisode->getAttributes()->getExtUrl())) {
            return AudioAssetMediaPubDecorator::getInstance(
                type: $configuration->getAudioBonusSlotName(),
                mediaUrl: $podcastEpisode->getAttributes()->getExtUrl()
            );
        }

        return null;
    }

    private function getFreeAudioMedia(
        AssetSlot $slot,
        ArtemisAudioDistributionConfiguration $configuration,
        ?PodcastEpisode $podcastEpisode = null
    ): ?AudioAssetMediaPubDecorator {
        $freeSlotAssetFile = $slot->getAudio();
        if (null === $freeSlotAssetFile) {
            return null;
        }

        if (null === $podcastEpisode) {
            $mainUrl = $this->assetFileRouteRepository->findMainByAssetFile((string) $freeSlotAssetFile->getId());
            if ($mainUrl) {
                return AudioAssetMediaPubDecorator::getInstance(
                    type: $configuration->getAudioFreeSlotName(),
                    audioFile: $freeSlotAssetFile,
                    mediaUrl: $this->assetFileRouteGenerator->getFullUrl($mainUrl)
                );
            }

            return null;
        }

        if (StringHelper::isNotEmpty($podcastEpisode->getAttributes()->getRssUrl())) {
            return AudioAssetMediaPubDecorator::getInstance(
                type: $configuration->getAudioFreeSlotName(),
                audioFile: $freeSlotAssetFile,
                linkUrl: $podcastEpisode->getAttributes()->getRssUrl()
            );
        }

        return null;
    }
}
