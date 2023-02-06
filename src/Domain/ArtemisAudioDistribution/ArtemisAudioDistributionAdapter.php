<?php

declare(strict_types=1);

namespace App\Domain\ArtemisAudioDistribution;

use AnzuSystems\CoreDamBundle\Distribution\DistributionAdapterInterface;
use AnzuSystems\CoreDamBundle\Domain\Asset\AssetTextsWriter;
use AnzuSystems\CoreDamBundle\Entity\AssetFile;
use AnzuSystems\CoreDamBundle\Entity\AudioFile;
use AnzuSystems\CoreDamBundle\Entity\Distribution;
use AnzuSystems\CoreDamBundle\Model\Dto\CustomDistribution\CustomDistributionAdmDto;
use App\Configuration\ConfigurationProvider;
use App\Entity\ArtemisAudioDistribution;

final class ArtemisAudioDistributionAdapter implements DistributionAdapterInterface
{
    public function __construct(
        private readonly AssetTextsWriter $textsWriter,
        private readonly ConfigurationProvider $configurationProvider,
        private readonly ArtemisAudioDistributionFactory $audioDistributionFactory,
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

    public function createDistributionEntity(AssetFile $assetFile, CustomDistributionAdmDto $distributionDto): Distribution
    {
        $distribution = (new ArtemisAudioDistribution());
        $distribution->setDistributionService($distributionDto->getDistributionService());

        $this->textsWriter->writeValues(
            from: $distributionDto,
            to: $distribution,
            config: $this->configurationProvider->getAudioDistribution()->getCustomDataToDistributionMap(),
            reversedConfig: true,
        );

        return $distribution;
    }
}
