<?php

declare(strict_types=1);

namespace App\Domain\ArtemisVideoDistribution;

use AnzuSystems\CoreDamBundle\Domain\Distribution\AbstractDistributionManager;
use AnzuSystems\CoreDamBundle\Entity\Distribution;
use App\Entity\ArtemisVideoDistribution;

final class ArtemisVideoDistributionManager extends AbstractDistributionManager
{
    /**
     * @param ArtemisVideoDistribution $distribution
     * @param ArtemisVideoDistribution $newDistribution
     */
    public function update(Distribution $distribution, Distribution $newDistribution, bool $flush = true): Distribution
    {
        $this->trackModification($distribution);

        $distribution->getTexts()
            ->setTitle($newDistribution->getTexts()->getTitle())
            ->setDescription($newDistribution->getTexts()->getDescription())
            ->setAuthors($newDistribution->getTexts()->getAuthors())
            ->setKeywords($newDistribution->getTexts()->getKeywords())
            ->setRubricId($newDistribution->getTexts()->getRubricId());
        $distribution->getFlags()
            ->setCreateArticle($newDistribution->getFlags()->isCreateArticle());

        $this->flush($flush);

        return $distribution;
    }

    public static function getDefaultKeyName(): string
    {
        return ArtemisVideoDistribution::class;
    }
}
