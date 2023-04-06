<?php

declare(strict_types=1);

namespace App\Domain\ArtemisVideoDistribution;

use AnzuSystems\CoreDamBundle\Distribution\AbstractDistributionDtoFactory;
use AnzuSystems\CoreDamBundle\Entity\Distribution;
use AnzuSystems\CoreDamBundle\Entity\VideoFile;
use AnzuSystems\CoreDamBundle\Repository\DistributionRepository;
use App\Configuration\ConfigurationProvider;
use App\Entity\ArtemisVideoDistribution;
use Doctrine\Common\Collections\ArrayCollection;

final class ArtemisVideoDistributionFactory extends AbstractDistributionDtoFactory
{
    public function __construct(
        private readonly ConfigurationProvider $configurationProvider,
        private readonly DistributionRepository $distributionRepository,
    ) {
    }

    public function createFromVideoFile(VideoFile $videoFile, string $service): ArtemisVideoDistribution
    {
        $videoDistribution = (new ArtemisVideoDistribution())
            ->setDistributionService($service);

        $this->setRubricId($videoDistribution, $videoFile);

        $blockedByDistributions = $this->distributionRepository->findByAssetFile((string) $videoFile->getId())->filter(
            fn (Distribution $distribution): bool => false === ($distribution->getDistributionService() === $service)
        );
        $videoDistribution->setBlockedBy(new ArrayCollection($blockedByDistributions->toArray()));

        return $videoDistribution;
    }

    /**
     * Sets rubricId based on distribution category. If option is missing, uses configuration value
     */
    private function setRubricId(
        ArtemisVideoDistribution $artemisVideoDistribution,
        VideoFile $audioFile,
    ): void {
        $option = $this->getSelectedOption($audioFile, $artemisVideoDistribution);
        if ($option) {
            $artemisVideoDistribution->getTexts()->setRubricId((int) $option->getValue());

            return;
        }

        $artemisVideoDistribution->getTexts()->setRubricId($this->configurationProvider->getVideoDistribution()->getDefaultRubricId());
    }
}
