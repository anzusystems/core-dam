<?php

declare(strict_types=1);

namespace App\Configuration;

use App\Model\Configuration\ArtemisAudioDistributionConfiguration;
use App\Model\Configuration\ArtemisVideoDistributionConfiguration;

final class ConfigurationProvider
{
    private ?ArtemisAudioDistributionConfiguration $distributionConfiguration = null;
    private ?ArtemisVideoDistributionConfiguration $videoDistributionConfiguration = null;

    public function __construct(
        private readonly array $artemisAudioDistribution,
        private readonly array $artemisVideoDistribution,
    ) {
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
