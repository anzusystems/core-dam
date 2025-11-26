<?php

declare(strict_types=1);

namespace App\Configuration;

use App\Model\Configuration\AssetPubConfiguration;
use App\Model\Configuration\CmsAudioProcessedAutomatConfiguration;
use App\Model\Configuration\MediaApiSyncConfiguration;
use App\Model\Configuration\RtmpConfiguration;

final class ConfigurationProvider
{
    private ?CmsAudioProcessedAutomatConfiguration $cmsAudioProcessedAutomatConfiguration = null;
    private ?RtmpConfiguration $rtmpConfiguration = null;
    private ?MediaApiSyncConfiguration $mediaApiSyncConfiguration = null;
    private ?AssetPubConfiguration $assetPubConfiguration = null;

    public function __construct(
        private readonly array $cmsAudioProcessedAutomat,
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

    public function getAudioDistribution(): CmsAudioProcessedAutomatConfiguration
    {
        if (null === $this->cmsAudioProcessedAutomatConfiguration) {
            $this->cmsAudioProcessedAutomatConfiguration = CmsAudioProcessedAutomatConfiguration::getFromArrayConfiguration($this->cmsAudioProcessedAutomat);
        }

        return $this->cmsAudioProcessedAutomatConfiguration;
    }
}
