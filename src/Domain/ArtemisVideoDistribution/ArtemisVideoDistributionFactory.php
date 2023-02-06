<?php

declare(strict_types=1);

namespace App\Domain\ArtemisVideoDistribution;

use AnzuSystems\CoreDamBundle\Distribution\AbstractDistributionDtoFactory;
use AnzuSystems\CoreDamBundle\Entity\VideoFile;
use App\Configuration\ConfigurationProvider;
use App\Entity\ArtemisVideoDistribution;

final class ArtemisVideoDistributionFactory extends AbstractDistributionDtoFactory
{
    public function __construct(
        private readonly ConfigurationProvider $configurationProvider,
    ) {
    }

    public function createFromVideoFile(VideoFile $videoFile, string $service): ArtemisVideoDistribution
    {
        $videoDistribution = (new ArtemisVideoDistribution())
            ->setDistributionService($service);

        $this->setRubricId($videoDistribution, $videoFile);

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

        $artemisVideoDistribution->getTexts()->setRubricId($this->configurationProvider->getAudioDistribution()->getDefaultRubricId());
    }
}
