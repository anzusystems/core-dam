<?php

declare(strict_types=1);

namespace App\Domain\ArtemisVideoDistribution;

use AnzuSystems\CoreDamBundle\Distribution\DistributionAdapterInterface;
use AnzuSystems\CoreDamBundle\Domain\Asset\AssetTextsWriter;
use AnzuSystems\CoreDamBundle\Entity\AssetFile;
use AnzuSystems\CoreDamBundle\Entity\Distribution;
use AnzuSystems\CoreDamBundle\Entity\VideoFile;
use AnzuSystems\CoreDamBundle\Model\Dto\CustomDistribution\CustomDistributionAdmDto;
use App\Configuration\ConfigurationProvider;
use App\Entity\ArtemisVideoDistribution;

final class ArtemisVideoDistributionAdapter implements DistributionAdapterInterface
{
    public function __construct(
        private readonly AssetTextsWriter $textsWriter,
        private readonly ConfigurationProvider $configurationProvider,
        private readonly ArtemisVideoDistributionFactory $artemisVideoDistributionFactory,
    ) {
    }

    public function decorateDistribution(Distribution $distribution): CustomDistributionAdmDto
    {
        $decorator = CustomDistributionAdmDto::getFromDistribution($distribution);
        $this->textsWriter->writeValues(
            from: $distribution,
            to: $decorator,
            config: $this->configurationProvider->getVideoDistribution()->getCustomDataToDistributionMap(),
        );

        return $decorator;
    }

    /**
     * @param VideoFile $assetFile
     */
    public function preparePayload(AssetFile $assetFile, string $distributionService): Distribution
    {
        return $this->artemisVideoDistributionFactory->createFromVideoFile($assetFile, $distributionService);
    }

    public function createDistributionEntity(AssetFile $assetFile, CustomDistributionAdmDto $distributionDto): Distribution
    {
        $distribution = (new ArtemisVideoDistribution());
        $distribution->setDistributionService($distributionDto->getDistributionService());

        $this->textsWriter->writeValues(
            from: $distributionDto,
            to: $distribution,
            config: $this->configurationProvider->getVideoDistribution()->getCustomDataToDistributionMap(),
            reversedConfig: true,
        );

        return $distribution;
    }
}
