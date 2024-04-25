<?php

declare(strict_types=1);

namespace App\Domain\ArtemisAudioDistribution;

use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\CoreDamBundle\Domain\Asset\AssetTextsWriter;
use AnzuSystems\CoreDamBundle\Entity\AssetFile;
use AnzuSystems\CoreDamBundle\Entity\AudioFile;
use AnzuSystems\CoreDamBundle\Entity\Distribution;
use AnzuSystems\CoreDamBundle\Model\Dto\CustomDistribution\CustomDistributionAdmDto;
use App\Configuration\ConfigurationProvider;
use App\Domain\Distribution\AbstractDistributionAdapter;
use App\Entity\ArtemisAudioDistribution;

final class ArtemisAudioDistributionAdapter extends AbstractDistributionAdapter
{
    public function __construct(
        private readonly ConfigurationProvider $configurationProvider,
        private readonly ArtemisAudioDistributionFactory $audioDistributionFactory,
        private readonly AssetTextsWriter $textsWriter,
    ) {
    }

    public function decorateDistribution(Distribution $distribution): CustomDistributionAdmDto
    {
        $decorator = CustomDistributionAdmDto::getFromDistribution($distribution);
        $this->textsWriter->writeValues(
            from: $distribution,
            to: $decorator,
            config: $this->configurationProvider->getAudioDistribution()->getCustomDataToDistributionMap(),
        );

        return $decorator;
    }

    /**
     * @param AudioFile $assetFile
     */
    public function preparePayload(AssetFile $assetFile, string $distributionService): Distribution
    {
        return $this->audioDistributionFactory->createFromAudioFile($assetFile, $distributionService);
    }

    /**
     * @throws ValidationException
     */
    public function createDistributionEntity(AssetFile $assetFile, CustomDistributionAdmDto $distributionDto): Distribution
    {
        $distribution = new ArtemisAudioDistribution();
        $this->setBaseDistributionFields($assetFile, $distributionDto, $distribution);
        $config = $this->configurationProvider->getAudioDistribution()->getCustomDataToDistributionMap();

        $this->textsWriter->writeValues(
            from: $distributionDto,
            to: $distribution,
            config: $config,
            reversedConfig: true,
        );

        $this->validate(
            distribution: $distribution,
            distributionDto: $distributionDto,
            config: $config,
        );

        return $distribution;
    }
}
