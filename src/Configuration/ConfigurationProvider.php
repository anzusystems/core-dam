<?php

declare(strict_types=1);

namespace App\Configuration;

use App\Model\Configuration\ArtemisAudioDistributionConfiguration;
use App\Model\Configuration\ArtemisVideoDistributionConfiguration;
use App\Model\Configuration\AssetPubConfiguration;
use App\Model\Configuration\MediaApiSyncConfiguration;
use App\Model\Configuration\RtmpConfiguration;

final class ConfigurationProvider
{
    private ?ArtemisAudioDistributionConfiguration $distributionConfiguration = null;
    private ?ArtemisVideoDistributionConfiguration $videoDistributionConfiguration = null;
    private ?RtmpConfiguration $rtmpConfiguration = null;
    private ?MediaApiSyncConfiguration $mediaApiSyncConfiguration = null;
    private ?AssetPubConfiguration $assetPubConfiguration = null;

    public function __construct(
        private readonly array $artemisAudioDistribution,
        private readonly array $artemisVideoDistribution,
        private readonly array $rtmp,
        private readonly array $mediaApiConfiguration,
        private readonly array $assetPubConfigurationData,
    ) {
    }

    public function getMediaApiSyncConfiguration(): MediaApiSyncConfiguration
    {
        if (null === $this->mediaApiSyncConfiguration) {
            $this->mediaApiSyncConfiguration = MediaApiSyncConfiguration::getFromArrayConfiguration($this->mediaApiConfiguration);
        }

        return $this->mediaApiSyncConfiguration;
    }

    public function getAssetPubConfiguration(): AssetPubConfiguration
    {
        if (null === $this->assetPubConfiguration) {
            $this->assetPubConfiguration = AssetPubConfiguration::getFromArrayConfiguration(
                $this->assetPubConfigurationData
            );
        }

        return $this->assetPubConfiguration;
    }

    public function getRtmpConfiguration(): RtmpConfiguration
    {
        if (null === $this->rtmpConfiguration) {
            $this->rtmpConfiguration = RtmpConfiguration::getFromArrayConfiguration($this->rtmp);
        }

        return $this->rtmpConfiguration;
    }

    public function getAudioDistribution(): ArtemisAudioDistributionConfiguration
    {
        if (null === $this->distributionConfiguration) {
            $this->distributionConfiguration = ArtemisAudioDistributionConfiguration::getFromArrayConfiguration($this->artemisAudioDistribution);
        }

        return $this->distributionConfiguration;
    }

    public function getVideoDistribution(): ArtemisVideoDistributionConfiguration
    {
        if (null === $this->videoDistributionConfiguration) {
            $this->videoDistributionConfiguration = ArtemisVideoDistributionConfiguration::getFromArrayConfiguration($this->artemisVideoDistribution);
        }

        return $this->videoDistributionConfiguration;
    }
}
