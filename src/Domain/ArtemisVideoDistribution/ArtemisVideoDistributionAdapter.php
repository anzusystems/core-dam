<?php

declare(strict_types=1);

namespace App\Domain\ArtemisVideoDistribution;

use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\CoreDamBundle\Domain\Asset\AssetTextsWriter;
use AnzuSystems\CoreDamBundle\Entity\AssetFile;
use AnzuSystems\CoreDamBundle\Entity\Distribution;
use AnzuSystems\CoreDamBundle\Entity\VideoFile;
use AnzuSystems\CoreDamBundle\Model\Dto\CustomDistribution\CustomDistributionAdmDto;
use App\Configuration\ConfigurationProvider;
use App\Domain\Distribution\AbstractDistributionAdapter;
use App\Entity\ArtemisVideoDistribution;

final class ArtemisVideoDistributionAdapter extends AbstractDistributionAdapter
{
    public function __construct(
        private readonly ConfigurationProvider $configurationProvider,
        private readonly ArtemisVideoDistributionFactory $artemisVideoDistributionFactory,
        private readonly AssetTextsWriter $textsWriter,
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

    /**
     * @throws ValidationException
     */
    public function createDistributionEntity(AssetFile $assetFile, CustomDistributionAdmDto $distributionDto): Distribution
    {
        $distribution = new ArtemisVideoDistribution();
        $this->setBaseDistributionFields($assetFile, $distributionDto, $distribution);
        $config = $this->configurationProvider->getVideoDistribution()->getCustomDataToDistributionMap();

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
