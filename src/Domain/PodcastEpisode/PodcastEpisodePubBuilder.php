<?php

declare(strict_types=1);

namespace App\Domain\PodcastEpisode;

use AnzuSystems\CoreDamBundle\Entity\PodcastEpisode;
use App\Configuration\ConfigurationProvider;
use App\Domain\Asset\AudioAssetPubBuilder;
use App\Model\Domain\PodcastEpisode\PodcastEpisodeLatestPubDecorator;

final readonly class PodcastEpisodePubBuilder
{
    public function __construct(
        private ConfigurationProvider $configurationProvider,
        private AudioAssetPubBuilder $audioAssetPubBuilder,
    ) {
    }

    public function buildPodcastEpisodePubDecorator(PodcastEpisode $podcastEpisode): PodcastEpisodeLatestPubDecorator
    {
        $configuration = $this->configurationProvider->getAudioDistribution();

        return PodcastEpisodeLatestPubDecorator::getInstance(
            podcastEpisode: $podcastEpisode,
            assetBonusAudioMedia: $this->audioAssetPubBuilder->getBonusAudioMedia($configuration, $podcastEpisode)
        );
    }
}
